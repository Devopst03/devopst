import MySQLdb, yaml, os.path, sys, subprocess
HERE = os.path.abspath(os.path.split(sys.argv[0])[0])
sys.path.insert(0, os.path.join(HERE, '..', '..', 'lib', 'talos'))
import talos_data

class Main:
	def __init__(self, env):
		self.db = talos_data.connect(env, fallback_to_fake_db=True)

	def update_row(self, table, row_id, vars):
		#FIXME: sql injection protection
		vars = [v.split("=", 1) for v in vars]
		self.db.q("UPDATE %s SET %s WHERE id=%%s" % (table, ", ".join("%s=%%s" % v[0] for v in vars)), [v[1] for v in vars] + [row_id])

	# --- commands ---

	def handle_test(self):
		print self.db.q1("select max(id) from build")

	def handle_update_build(self, build_id, *vars):
		self.update_row('build', build_id, vars)

	def handle_build_done(self, build_id):
		self.db.q("UPDATE build SET finished_date=NOW() where id=%s", (build_id,))

	def handle_monitor(self, table, row_id, col_name, cmd):
		print "RUNNING %s" % cmd
		print "LOGGING TO %s id=%s saving to col %s" % (table, row_id, col_name)
		self.db.q("UPDATE %s SET %s='' WHERE id=%%s" % (table, col_name), (row_id,))

		log_f = None
		if os.environ.has_key('ANSIBLE_LOG_PATH'):
			log_f = open(os.environ['ANSIBLE_LOG_PATH'].replace('.log', '-build.log'), 'w')
		maxlen = 1000000 # max_allowed_packet on mysql defaults to ~1M
		bytes_so_far = 0

		p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, shell=True)
		while 1:
			line = p.stdout.readline()
			if not line: break
			print "mvn: %s" % line.rstrip()
			if log_f:
				log_f.write(line)
				log_f.flush()
			if bytes_so_far > maxlen:
				continue

			bytes_so_far += len(line)
			if bytes_so_far > maxlen:
				# we just went over the limit
				line += "\n\n(Log truncated due to MySQL max_allowed_packet restriction.)\n"
			self.db.q("UPDATE %s SET %s=CONCAT(%s, %%s) WHERE id=%%s" % (table, col_name, col_name), (line, row_id))

		# preserve return code for ansible
		sys.exit(p.wait())

	def main(self, command, args):
		getattr(self, 'handle_%s' % command)(*args)

if __name__ == '__main__':
	Main(sys.argv[1]).main(sys.argv[2], sys.argv[3:])
