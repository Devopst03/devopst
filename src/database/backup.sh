#!/bin/bash

#########################################################################
# PATH					: /home/prod/talos/src/wrapper/backup.sh		#
# USAGE					: TALOS Script to take backups					#
#																		#
# LAST MODIFIED			: brijesh (brijeshs@mode.com)					#
# LAST MODIFIED DATE	: 08 APRIL 2015									#
# HELP					: ./backup.sh -h								#
# REQUIREMENTS			: PLEASE MAKE SURE THAT YOU HAVE THE 			#
#						  FOLLOWING DIRECTORY ON ALL THE SYSTEMS		#
#						  /mnt/backups/									#
#						  For dev this can be a local folder			#
#						  For prod it should be a nfs or backup			#
#########################################################################


## SOURCE shflags
. /usr/share/shflags/shflags

## PARAMETER DEFINITION
DEFINE_string 'env' 'null' 'Environment Name' 'e'
DEFINE_string 'service' 'null' 'Service Name' 's'
DEFINE_string 'product' 'null' 'Product Name' 'p'

## PARSE THE COMMAND-LINE
FLAGS "$@" || exit 1
eval set -- "${FLAGS_ARGV}"

if [[ ${FLAGS_service} == 'cassandra' || ${FLAGS_service} == 'elasticsearch' ]]; then
	## PARAMETER VALIDATION
	if [[ ${FLAGS_env} == '' || ${FLAGS_env} == 'null' ]]; then
		echo "Environment Name Is Required"
		exit 
	fi

	if [[ ${FLAGS_product} == '' || ${FLAGS_product} == 'null' ]]; then
		echo "Product Is Required"
		exit
	fi

	echo "GETTING SERVERS FOR ${FLAGS_service} IN ${FLAGS_product}"	
	
	## GET LIST OF HOST VIA INVENTORY PLAYBOOK
	SERVERLIST=`/home/prod/talos/src/wrapper/inventory "action=list entity=host env=${FLAGS_env} service=${FLAGS_service} product=${FLAGS_product}" --format=json`
	
	## PARSE HOST LIST
	SERVERHOST=`(echo ${SERVERLIST} | jq ".[].HostName"  2> /dev/null) || echo -1`
	
	## IF NO HOSTS FOUND EXIT
	if [ "${SERVERHOST}" = "-1" ]; then
		echo "No Hosts Found. Please add servers via Inventory Playbook. Stopping Process"
		echo "./inventory -h"
		exit
	fi
	
	## CREATE LIST OF HOSTS
	SERVERHOST=`echo ${SERVERHOST} | sed -e 's/ /,/g' -e "s/\"/'/g"`
	
	## SET DIRECTORY NAME FOR BACKUP
	FOR_DIR=`date '+%Y.%m.%d.%H.%M'`
	SNAPSHOT_DIR="hourly_${FOR_DIR}"
	FOR_DIR=`date +%s`
	SNAPSHOT_DIR="$FOR_DIR"
	
	KEEP_DIR=`date '+%Y.%m.%d.%H' -d "7 hours ago"`
	KEEP_SNAPSHOT_DIR="hourly_${KEEP_DIR}"
	
	echo "STARTING BACKUP ON HOSTS: ${SERVERHOST}"
	
	## CALL BACKUP PLAYBOOK
	ansible-playbook /home/prod/talos/src/playbooks/backup.yml -e "backup=${FLAGS_service} env=${FLAGS_env} servers=[${SERVERHOST}] snapshotname=${SNAPSHOT_DIR}"
	
else
	echo "Use 'backup -h' for usage."
fi
