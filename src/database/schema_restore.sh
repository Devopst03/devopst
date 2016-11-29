#!/bin/bash

#################################################################################
# PATH					: /home/prod/talos/src/wrapper/restore_schema.sh		#
# USAGE					: Script to restore Schema								#
#																				#
# LAST MODIFIED			: brijesh (brijeshs@mode.com)							#
# LAST MODIFIED DATE	: 22 JANUARY 2016										#
# HELP					: ./schema_restore.sh -h								#
#																				#
#################################################################################

## SOURCE shflags
. /usr/share/shflags/shflags

## PARAMETER DEFINITION
DEFINE_string 'deployUserMySQL' 'null' 'Deploy MySQL User' 'u'
DEFINE_string 'deployPassMySQL' 'null' 'Deploy MySQL Password' 'p'
DEFINE_string 'deployUserHbase' 'null' 'Deploy Hbase User' 'x'
DEFINE_string 'deployHost' 'null' 'Deploy Host' 't'
DEFINE_string 'schemaFile' 'null' 'Schema File' 'f'

## PARSE THE COMMAND LINE
FLAGS "$@" || exit 1
eval set -- "${FLAGS_ARGV}"

if [[ $FLAGS_deployHost == '' || $FLAGS_deployHost == 'null' ]]; then
	echo "Host is Required. Use -h for help."
	exit
fi

if [[ $FLAGS_deployUserMySQL == '' || $FLAGS_deployUserMySQL == 'null' ]]; then
	echo "Deploy User is Required. Use -h for help."
	exit
fi

if [[ $FLAGS_schemaFile == '' || $FLAGS_schemaFile == 'null' ]]; then
	echo "Schema File is Required. Use -h for help."
	exit
fi

dbtype=`cat ${FLAGS_schemaFile} | jq '.type | length'`
i=0

dbtypename=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].name"`

if [[ "${dbtypename,,}" = 'mysql' ]]; then
	
	totalSchema=`cat ${FLAGS_schemaFile} | jq ".type[$i].schema | length"`
	
	for (( j=0; j<$totalSchema; j++ ))
	do
				schemaName=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].schema[$j].name"`
				
				if [[ "$FLAGS_deployHost" != null ]]; then
					QUERY="CREATE DATABASE IF NOT EXISTS ${schemaName}"
					mysql -A -h$FLAGS_deployHost -u$FLAGS_deployUserMySQL -p$FLAGS_deployPassMySQL -e "${QUERY}"
					
					numberOfTables=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].schema[$j].tables | length"`
					
					for (( k=0; k<$numberOfTables; k++ ))
					do
						tableQuery=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].schema[$j].tables[$k].statement"`
						mysql -A -h$FLAGS_deployHost -u$FLAGS_deployUserMySQL -p$FLAGS_deployPassMySQL -e "use ${schemaName}; ${tableQuery}"
					done
				fi
	done
else
	totalHbaseSchema=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].schema | length"`
	
	for (( j=0; j<$totalHbaseSchema; j++ ))
	do
		schemaName=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].schema[$j].name"`
		
		if [[ "$FLAGS_deployHost" != null ]]; then
			numberOfTables=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].schema[$j].tables | length"`
			
			for (( k=0; k<$numberOfTables; k++ ))
			do
				tableQuery=`cat ${FLAGS_schemaFile} | jq -r ".type[$i].schema[$j].tables[$k].statement"`
				
				ssh $FLAGS_deployUserHbase@$FLAGS_deployHost << EOF
					export JAVA_HOME=/usr/lib/jvm/java-1.8.0-openjdk-1.8.0.65-0.b17.el6_7.x86_64/jre/
					echo "$tableQuery" | hbase shell
EOF
			done
		fi
	done
fi














