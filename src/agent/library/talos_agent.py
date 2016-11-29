#!/usr/bin/python

# collect a bunch of useful info from a Mode machine

BASE = '/home/prod'
USER_AGENT = "talos-agent/0.1"
PID_FILE = 'talos-agent.pid'
STDOUT_LOG = 'talos-agent.log'
STDERR_LOG = 'talos-agent.err'

import glob, os, socket, traceback, yum, optparse, urllib2, json, sys, signal, time, atexit

def try_file(fn):
    if os.path.exists(fn): return open(fn).read().strip()

def daemonize(stdin='/dev/null', stdout='/dev/null', stderr='/dev/null', pidfile=None, sigterm_handler=None):
    # This function comes from: http://www.jejik.com/articles/2007/02/a_simple_unix_linux_daemon_in_python/
    try:
        pid = os.fork()
        if pid > 0:
            # exit first parent
            sys.exit(0)
    except OSError, e:
        sys.stderr.write("fork #1 failed: %d (%s)\n" % (e.errno, e.strerror))
        sys.exit(1)

    # decouple from parent environment
    os.chdir("/")
    os.setsid()
    os.umask(0)

    # do second fork
    try:
        pid = os.fork()
        if pid > 0:
            # exit from second parent
            sys.exit(0)
    except OSError, e:
        sys.stderr.write("fork #2 failed: %d (%s)\n" % (e.errno, e.strerror))
        sys.exit(1)

    # redirect standard file descriptors
    sys.stdout.flush()
    sys.stderr.flush()
    si = file(stdin, 'r')
    so = file(stdout, 'a+')
    se = file(stderr, 'a+', 0)
    os.dup2(si.fileno(), sys.stdin.fileno())
    os.dup2(so.fileno(), sys.stdout.fileno())
    os.dup2(se.fileno(), sys.stderr.fileno())

    if pidfile:
        def delpid():
            if not os.path.exists(pidfile): return
            os.remove(pidfile)
        atexit.register(delpid)
        pid = str(os.getpid())
        file(pidfile, 'w+').write("%s\n" % pid)

    if sigterm_handler:
        signal.signal(signal.SIGTERM, sigterm_handler)

class Collector:
    def collect(self):
        data = {'errors': {}}
        for fname in sorted(dir(self)):
            if not fname.startswith("collect_"): continue
            k = fname[8:]
            try:
                data[k] = getattr(self, fname)()
            except:
                data['errors'][k] = traceback.format_exc()
        return data

    def collect_hostname(self):
        return socket.gethostname()

    def collect_httpd_talos_pid(self):
        return try_file('/var/run/httpd-talos.pid')

    def collect_caviar_httpd_pid(self):
        return try_file('/var/run/caviar-httpd.pid')

    def collect_mock_server_pid(self):
        return try_file('/var/run/mock-server.pid')

    def collect_in_rotation(self):
        if os.path.exists("%s/system/in_rotation.html" % BASE): return 1
        if os.path.exists("%s/system/out_rotation.html" % BASE): return 0

    def collect_mode_env(self):
        return try_file("%s/system/mode-env" % BASE)

    def collect_mode_product(self):
        return try_file("%s/system/mode-product" % BASE)

    def collect_mode_build(self):
        return try_file("%s/system/mode-build" % BASE)

    def collect_product_symlink(self):
        fn = '%s/system/mode-product' % BASE
        if not os.path.exists(fn): return None
        product = open(fn).read().strip()
        if not product: return None
        fn = '%s/%s/releases/live' % (BASE, product)
        if not os.path.exists(fn): return None
        return os.readlink(fn)

    def _collect_rpms(self):
        yb = yum.YumBase()
        yb.setCacheDir()
        pkgs = yb.rpmdb.returnPackages()
        help(pkgs[0])
        return [[pkg.name, pkg.arch, pkg.epoch, pkg.version, pkg.release, pkg.printVer()] for pkg in pkgs]

class Daemon:
    def __init__(self, status_url, log_root=None, verbose=False):
        self.status_url = status_url
        self.log_root = log_root
        self.running = False
        self.verbose = verbose

    def collect(self):
        status = Collector().collect()

        if self.verbose:
            import pprint
            pprint.pprint(status)

        return status

    def collect_and_send(self):
        status = self.collect()

        req = urllib2.Request(self.status_url,
            headers = {
                #"Authorization": basic_authorization(settings.username, settings.password),
                "Content-Type": "application/json",
                "User-Agent": USER_AGENT, 
                }, data = json.dumps(status))
        f = urllib2.urlopen(req)
        if self.verbose: print "response:",f.read()

    def run(self):
        def handle_sigterm(signum, frame):
            print "talos-agent with pid %d caught SIGTERM" % os.getpid()
            self.running = False
        stdout = os.path.join(self.log_root, STDOUT_LOG) if self.log_root else '/dev/null'
        stderr = os.path.join(self.log_root, STDERR_LOG) if self.log_root else '/dev/null'
        pidfile = os.path.join(self.log_root, PID_FILE) if self.log_root else None
        daemonize(stdout=stdout, stderr=stderr, pidfile=pidfile, sigterm_handler=handle_sigterm)
        print "talos-agent started with pid %d, reporting to %s" % (os.getpid(), self.status_url)
        self.running = True
        self.start_time = time.time()
        self.report_count = 0
        while self.running:
            self.collect_and_send()
            self.report_count += 1
            time.sleep(10)
        print "talos-agent with pid %d stopped.  Ran for %d s; %d reports sent." % (os.getpid(), time.time() - self.start_time, self.report_count)

class SyntaxException(Exception): pass

def main():
    parser = optparse.OptionParser()
    parser.add_option('-u', '--status-url', dest='status_url', help='Status URL from central Talos server')
    parser.add_option('-l', '--log-root', dest='log_root', help='Folder for logs and pid files')
    opts, args = parser.parse_args()

    def fail(msg):
        print>>sys.stderr, msg
        return

    valid_commands = ['start', 'stop', 'once', 'dump']
    if len(args) == 1:
        opts.command, = args
    else:
        raise SyntaxException("Command required; valid commands are: %s" % ', '.join(valid_commands))
    if opts.command not in valid_commands and os.path.exists(opts.command):
        # read opts from file (we're probably being run by ansible as a module)
        opts.command = open(opts.command).read().strip()
    if opts.command not in valid_commands: raise SyntaxException("invalid command; valid commands are: %s" % ', '.join(valid_commands))
    
    daemon = Daemon(status_url=opts.status_url, log_root=opts.log_root)

    if opts.command in ('start', 'stop'):
        # stop any existing agents
        agent_pids = []
        for line in os.popen("ps ax", "r").readlines():
            if line.find(sys.argv[0]) == -1: continue
            agent_pids.append(int(line.strip().split()[0]))
        if os.getpid() not in agent_pids:
            raise Exception("Couldn't find self in ps ax output; this is weird")
        agent_pids.remove(os.getpid())
        for pid in agent_pids:
            print "Killing agent with pid %d" % pid
            os.kill(pid, signal.SIGTERM)

    if opts.command == 'start':
        if not opts.status_url: raise SyntaxException("--status-url required")
        daemon.run()
    elif opts.command == 'once':
        if not opts.status_url: raise SyntaxException("--status-url required")
        daemon.collect_and_send()
    elif opts.command == 'dump':
        print json.dumps(daemon.collect())

if __name__ == '__main__':
    try:
        sys.exit(main())
    except SyntaxException, e:
        print e
        sys.exit(1)
