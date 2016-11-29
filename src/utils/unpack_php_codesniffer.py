#!/usr/bin/python

import os, os.path

HERE = os.path.split(__file__)[0]
phpcs_path = os.path.join(HERE, '..', '..', 'dependencies', 'php-codesniffer-tar.gz')

print "Unpacking PHP_Codesniffer in %s" % phpcs_path

os.system("cd %s && tar xf php-codesniffer.tar.gz" % phpcs_path)

print "PHP_Codesniffer unpacked."
