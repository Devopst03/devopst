# createrepo_talos.py

# Syntax: python createrepo_talos.py <path>

# This will run createrepo -v --update <path>, but will serialize itself so it doesn't clobber other instances.

import os, sys, glob, time

def main(root):
    print "%s: Updating Yum metadata in %s" % (sys.argv[0], root)

    pid_pattern = "%s/.createrepo_talos." % root
    
    # Figure out what else is running
    pids = []
    for pid_fn in glob.glob("%s*" % pid_pattern):
        try:
            pid = int(open(pid_fn).read().strip())
            os.kill(pid, 0)
            pids.append(pid)
        except OSError:
            print "Process %d not running; removing its semaphore file" % pid
            os.unlink(pid_fn)
        except ValueError:
            print "Malformed semaphore file %s; removing" % pid_fn
            os.unlink(pid_fn)

    # Write our own semaphore if we're going to run createrepo
    if len(pids) < 2:
        my_pid_fn = "%s%d" % (pid_pattern, os.getpid())
        open(my_pid_fn, 'w').write(str(os.getpid()))
    else:
        my_pid_fn = None

    print "Other processes:",pids

    try:
        supervise_createrepo(root, pids)
    finally:
        if my_pid_fn and os.path.exists(my_pid_fn):
            os.unlink(my_pid_fn)

def createrepo(root):
    os.system("createrepo -v --update %s" % root)

def supervise_createrepo(root, pids):
    # NO EXISTING CREATEREPO -- start it up, run, done!
    if not len(pids):
        return createrepo(root)

    # ONE EXISTING CREATEREPO -- wait until it's done, then run
    if len(pids) == 1:
        print "Waiting for createrepo process %d to finish" % pids[0]
        while 1:
            try:
                os.kill(pids[0], 0)
                time.sleep(1)
            except OSError:
                print "Process %d finished; our turn!" % pids[0]
                break
        return createrepo(root)

    # TWO OR MORE EXISTING CREATEREPOS -- wait until the second one is done, then exit
    while 1:
        for pid in pids[:]:
            try:
                os.kill(pid, 0)
            except OSError:
                print "Process %d finished" % pid
                pids.remove(pid)
        if not len(pids):
            print "All other createrepo processes finished; we should have updated repo metadata by now"
            break
        time.sleep(1)

if __name__ == '__main__':
    main(sys.argv[1])
