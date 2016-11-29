#!/usr/bin/env python

# Description: This script is used to extract the latest build number
#              from the current branch by reading the latest build generated git tag.
#              Talos generates a tag at the end of a successful build that is named after the rpm name.
#              The tag name format is product-branch_name-buildnumber-0. Ex: yoko-feature_hotfix2.45-32-0
#
# params:      product_branchname: product-branch_name, i.e. yoko-feature_hotfix2.45
#              buildnum_file: path for the global build number file
#              buildnum_local: path to save a copy of the build number file (so it'll be included in the build)
#
# This must be run from inside the product git checkout.

import sys, os, os.path, re

product, branch, buildnum_file = sys.argv[1:]

if os.path.exists(buildnum_file):
	latest_build = None
	for line in open(buildnum_file):
		m = re.search("^buildNumber0\=(\d+)", line)
		if not m: continue
		latest_build = int(m.group(1))
else:
	latest_build = 0
	# If a maven generated build num file is missing then its either a new branch
	# or build directory was removed (along with the file)
	for line in os.popen('git tag -l', 'r'):
		m = re.search(r"^([^\-]+)\-([^\-]+)\-(\d+)(?:\-0)\s*?$", line)
		if not m: continue
		if product == m.group(1) and branch == m.group(2) and int(m.group(3)) > latest_build:
#			print line.strip()
			latest_build = int(m.group(3))
#		print line, m.groups()
		# | grep -o "\-[0-9]*\-0$" | cut -d'-' -f2`''', 'r').read().strip())

latest_build += 1
print>>open(buildnum_file, 'w'), "buildNumber0=%d" % latest_build

print latest_build
