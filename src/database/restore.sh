#!/bin/bash

#########################################################################
# PATH					: /home/prod/talos/src/wrapper/restore.sh		#
# USAGE					: TALOS Script to take restore backup			#
#																		#
# LAST MODIFIED			: brijesh (brijeshs@mode.com)					#
# LAST MODIFIED DATE	: 22 JANUARY 2016								#
# HELP					: ./restore.sh -h								#
#																		#
#########################################################################

## SOURCE shflags
. /usr/share/shflags/shflags

## PARAMETER DEFINITION
DEFINE_string 'env' 'null' 'Environment Name' 'e'
DEFINE_string 'service' 'null' 'Serive Name' 's'
DEFINE_string 'product' 'null' 'Product Name' 'p'
DEFINE_string 'schemafile' 'null' 'Database Schema File' 'a'
DEFINE_string 'datafile' 'null' 'Database Data File' 'd'

## PARSE THE COMMAND LINE
FLAGS "$@" || exit 1
eval set -- "${FLAGS_ARGV}"

if [[ ${FLAGS_schemafile} == '' || ${FLAGS_schemafile} == 'null' ]]; then
	echo "Schema File is to be Provided"
	exit
fi

if [[ ${FLAGS_datafile} == '' || ${FLAGS_datafile} == 'null' ]]; then
	echo "Data File is to be Provided"
	exit
fi

if [[ ${FLAGS_env} == '' || ${FLAGS_env} == 'null' ]]; then
	echo "Environment Name is Required"
	exit
fi

if [[ ${FLAGS_service} == '' || ${FLAGS_service} == 'null' ]]; then
	echo "Service Name is Required"
	exit
fi

if [[ ${FLAGS_product} == '' || ${FLAGS_product} == 'null' ]]; then
	echo "Product Name is Required"
	exit
fi

## GET SERVERS FROM ENVIRONMENT
SERVERLIST=`/home/prod/talos/src/wrapper/inventory "action=list entity=host env=${FLAGS_env} service=${FLAGS_service} product=${FLAGS_product}" --format=json`

##PARSE HOST LIST
SERVERHOST=`(echo ${SERVERLIST} | jq ".[].HostName" 2> /dev/null) || echo -1`

if [ "${SERVERHOST}" = "-1" ]; then
	echo "No Hosts Found. Please add servers via Inventory Playbook"
	exit
fi

SERVERHOST=`echo ${SERVERHOST} | sed -e 's/ /,/g' -e "s/\"/'/g"`

ansible-playbook /home/prod/talos/src/playbooks/restore.yml -e "restore=${FLAGS_service} env=${FLAGS_env} servers=[${SERVERHOST}] schemafile=${FLAGS_schemafile} datafile=${FLAGS_datafile}"






