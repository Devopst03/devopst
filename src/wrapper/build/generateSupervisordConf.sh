#!/bin/sh

product=$1
branch=$2
basePath="/home/prod/$product/build/$branch"
releasesPath="/home/prod/$product/releases/live"
productConfig="$basePath/conf/product.conf"

if [ -f $productConfig ]
then
    echo "file exists."
else
    echo "Product conf file not present."
    exit
fi


# This is added for monitored services, i.e supervisord
monitoredPreString='[supervisord]\nlogfile=/tmp/supervisord.log ; (main log file;default $CWD/supervisord.log)\nlogfile_maxbytes=50MB ; (max main logfile bytes b4 rotation;default 50MB)\nlogfile_backups=10 ; (num of main logfile rotation backups;default 10)\nloglevel=info ; (log level;default info; others: debug,warn,trace)\npidfile=/tmp/supervisord.pid ; (supervisord pidfile;default supervisord.pid)\nnodaemon=false ; (start in foreground if true;default false)\nminfds=1024 ; (min. avail startup file descriptors;default 1024)\nminprocs=200 ; (min. avail process descriptors;default 200)\n\n[inet_http_server]\nport=9001\n\n[supervisorctl]\nserverurl=unix:///tmp/supervisor.sock\n\n[rpcinterface:supervisor]\nsupervisor.rpcinterface_factory=supervisor.rpcinterface:make_main_rpcinterface\n\n[unix_http_server]\nfile=/tmp/supervisor.sock\nchmod=0700'
monitoredString=''

extlength=$(cat $productConfig | jq '.services | length')
extlength=`expr $extlength - 1`

for i in $(seq 0 $extlength)
do
    finalString=''    
    if [ $(cat $productConfig | jq ".services[${i}].name") != 'null' ]
    then
        name=$(cat $productConfig | jq ".services[${i}].name" | sed "s/\"//g")

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
            temp="$temp\nautostart=true\nautorestart=true\nuser=prod\nstdout_logfile=/tmp/$name.log\nstdout_logfile_maxbytes=10MB\nstderr_logfile=/tmp/$name.log\nstderr_logfile_maxbytes=10MB"
            monitoredString="$monitoredString\n$temp\n"
        fi
        # Added for monitored services, i.e supervisord end===
    fi
done

# Added for monitored services, i.e supervisord
monitoredFinalString="$monitoredPreString \n $monitoredString"
monitoredFileName="/etc/supervisord.conf"
echo -e $monitoredFinalString > "$monitoredFileName"
chmod 655 "$monitoredFileName"

echo "Service supervisord conf is created on /etc/supervisord.conf"


