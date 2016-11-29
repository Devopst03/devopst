#!/usr/bin/python

# Path          : talos/src/utils/generateBuildList.py
# Usage         : A quick hack to generate a list of available builds for a product. Currently there is no easy way to tell
#                 which builds are available for deploy. The generated list will be available through http://<talos url>/talos-repo/<product>/buildlist
# Params        : product name, talos repo path


import os, sys, cStringIO, time


BUILDNAME_EXTENSION = ".noarch.rpm"
LIST_FILENAME = "buildlist"


def main():

    product = str(sys.argv[1])
    repoPath = str(sys.argv[2]) + product


    output = cStringIO.StringIO()
    output.write('\n *** Builds available for deploy *** \n')

    branches = os.listdir( repoPath )

    for branch in branches:
        b = branch.split("-")
        if len(b) > 1:
            print >>output, '\n'
            print >>output, b[1]
            for root, dirs, files in os.walk( repoPath + '/' + branch ):
               path = root.split('/')
               for file in files:
                  if BUILDNAME_EXTENSION  in file: print >>output, '--- ' + file.split(BUILDNAME_EXTENSION)[0]


    print >>output, '\n\n\n\n\n\nupdated ' + time.strftime("%c")

    # generate a new list
    buildlistFile = open(repoPath + '/' + LIST_FILENAME, 'w')
    buildlistFile.write( output.getvalue() )

    output.close()
    buildlistFile.close()



if __name__ == '__main__':
    main()