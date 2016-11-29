#!/bin/sh


product=$1
branch=$2
basePath=$3
releasesPath="/home/prod/$product/releases/live"
productConfig="$basePath/conf/product.conf"

if [ -f $productConfig ]
then
    echo "Reading product config from $productConfig"
else
    echo "$productConfig missing -- not generating service scripts"
    exit
fi

preStart='#!/bin/bash \n. /etc/rc.d/init.d/functions \n
start() {'
postStart='echo "starting up!!!" \n
} \n
stop() {'

postStop='
	echo "stopping down!!!" \n
} \n
status() {'
postStatus='} \n
case "$1" in \n
  start) \n
	start \n
	;; \n
  stop) \n
	stop \n
	;; \n
  status) \n
    status  \n
	;; \n
  restart) \n
	stop \n
	start \n
	;; \n
  *) \n
	echo $"Usage: $prog \n {start|stop|restart|status}" \n
    RETVAL=2 \n
esac \n
exit $RETVAL \n
'

# This is added for monitored services, i.e supervisord
monitoredFileName="$basePath/deploy/supervisord.conf"
monitoredPreString='[supervisord]\nlogfile=/home/prod/logs/supervisord.log ; (main log file;default $CWD/supervisord.log)\nlogfile_maxbytes=50MB ; (max main logfile bytes b4 rotation;default 50MB)\nlogfile_backups=10 ; (num of main logfile rotation backups;default 10)\nloglevel=info ; (log level;default info; others: debug,warn,trace)\npidfile=/tmp/supervisord.pid ; (supervisord pidfile;default supervisord.pid)\nnodaemon=false ; (start in foreground if true;default false)\nminfds=1024 ; (min. avail startup file descriptors;default 1024)\nminprocs=200 ; (min. avail process descriptors;default 200)\n\n[inet_http_server]\nport=9001\n\n[supervisorctl]\nserverurl=unix:///tmp/supervisor.sock\n\n[rpcinterface:supervisor]\nsupervisor.rpcinterface_factory=supervisor.rpcinterface:make_main_rpcinterface\n\n[unix_http_server]\nfile=/tmp/supervisor.sock\nchmod=0700'
monitoredString=''

# make sure product.conf is valid json
cat $productConfig | jq '.services | length' || exit 1

extlength=$(cat $productConfig | jq '.services | length')
extlength=`expr $extlength - 1`
serviceCommands=''
for i in $(seq 0 $extlength)
do
    finalString=''    
    if [ $(cat $productConfig | jq ".services[${i}].name") != 'null' ]
    then
        name=$(cat $productConfig | jq ".services[${i}].name" | sed "s/\"//g")

        #adding start command into start() function.
        startLength=$(cat $productConfig | jq ".services[${i}].start  | length")
        startLength=`expr $startLength - 1`
        #loop to filter out start commands from json file.
        temp=''
        for j in $(seq 0 $startLength)
        do
            startCommand=$(cat $productConfig | jq ".services[${i}].start[${j}]" | sed "s/\"//g")
            # Check service is supervisord, if found then forcefully copy generated supervisord.conf in /etc/.            
            if [ "$startCommand" != "${startCommand/supervisord/}" ]; then
                startCommand="sudo cp -f $releasesPath/deploy/supervisord.conf /etc/ \n $startCommand"
            fi

            temp="$temp \n $startCommand"
        done
        finalString="$preStart \n $temp \n $postStart \n"

        temp=''
        stopLength=$(cat $productConfig | jq ".services[${i}].stop  | length")
        stopLength=`expr $stopLength - 1`
        for j in $(seq 0 $stopLength)
        do
            stopCommand=$(cat $productConfig | jq ".services[${i}].stop[${j}]" | sed "s/\"//g")
            temp="$temp \n $stopCommand"
        done
        finalString="$finalString \n $temp \n $postStop"

        temp=''
        statusLength=$(cat $productConfig | jq ".services[${i}].status  | length")
        statusLength=`expr $statusLength - 1`
        for j in $(seq 0 $statusLength)
        do
            statusCommand=$(cat $productConfig | jq ".services[${i}].status[${j}]" | sed "s/\"//g")
            temp="$temp \n $statusCommand"
        done
        finalString="$finalString \n $temp \n $postStatus"

        # Added for monitored services, i.e supervisord start===
        temp=''
        monitoredLength=$(cat $productConfig | jq ".services[${i}].monitored  | length")
        monitoredLength=`expr $monitoredLength - 1`        

        if [ $monitoredLength != -1 ]
        then
            temp="[program:$name]\nprocess_name=$name""_%(process_num)02d"
        fi

        for j in $(seq 0 $monitoredLength)
        do            
            monitoredCommand=$(cat $productConfig | jq ".services[${i}].monitored[${j}]" | sed "s/\"//g")
            temp="$temp\n$monitoredCommand"
        done

        if [ $monitoredLength != -1 ]
        then
            temp="$temp\nuser=root\nstdout_logfile=/home/prod/logs/$name.log\nstdout_logfile_maxbytes=10MB\nstderr_logfile=/home/prod/logs/$name.log\nstderr_logfile_maxbytes=10MB"
            monitoredString="$monitoredString\n$temp\n"
        fi
        # Added for monitored services, i.e supervisord end===

        serviceFileName="$basePath/deploy/$name"
        serviceFileSymlinks="$releasesPath/deploy/$name"

        if [ -f $serviceFileName ]
        then
            rm $serviceFileName
        fi
        echo $serviceFileName
        echo -e $finalString >> "$serviceFileName"
        chmod 655 "$serviceFileName"
        serviceCommands="$serviceCommands \nsudo ln -sf $serviceFileSymlinks /etc/init.d/"
    fi
done

# Added for monitored services, i.e supervisord
if [ "$monitoredString" != "" ]
then
    monitoredFinalString="$monitoredPreString \n $monitoredString"    
    echo -e $monitoredFinalString > "$monitoredFileName"
    chmod 655 "$monitoredFileName"
fi


echo "Service files generated in $basePath/deploy directory."
if [ -f "$basePath/conf/serviceLinks.sh" ]
    then
        rm "$basePath/conf/serviceLinks.sh"
fi
echo -e $serviceCommands >> "$basePath/conf/serviceLinks.sh"
echo "Service symlinks are written in $basePath/conf/serviceLinks.sh"


