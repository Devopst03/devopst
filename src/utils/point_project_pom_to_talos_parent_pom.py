#!/usr/bin/env python

import os, os.path, re

pom = open("pom.xml").read()

m = re.search("<relativePath>([^<]+)</relativePath>", pom)

if not m:
	print "No <relativePath> found in pom.xml"

else:
	talos = os.environ['TALOS_HOME'].split("/")
	here = os.getcwd().split("/")

	while talos[0] == here[0]:
		talos.pop(0)
		here.pop(0)

	relative_talos_path = "../" * len(here) + "/".join(talos)
	relative_pom_path = "%s/src/build/maven/pom.xml" % relative_talos_path

	orig_relative_path = pom[m.start(1):m.end(1)]
	if orig_relative_path == relative_pom_path:
		print "<relativePath> was already correct"
	else:
		pom = pom[:m.start(1)] + relative_pom_path + pom[m.end(1):]
		open(".pom.xml.updated", 'w').write(pom)
		os.rename(".pom.xml.updated", "pom.xml")
		print "Changed <relativePath> from %s to %s in pom.xml" % (orig_relative_path, relative_pom_path)
