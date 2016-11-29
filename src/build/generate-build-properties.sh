#!/bin/bash

# author: ken s
# description: generate a build.properties file that contains build time and build name
# parameters: buildname

PROPERTIES_FILE="build.properties"


cat > $PROPERTIES_FILE <<EOL
BUILD_TIME_LOCAL=`date`
BUILD_TIME_UNIX=`date +%s`
BUILD_TIME_UTC=`date -u`
BUILD_VERSION_SYMBOLIC=$1
EOL