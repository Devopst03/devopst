#!/bin/sh
cd /home/prod/talos
TALOS_HOME=/home/prod/talos
ANSIBLE_CONFIG=/home/prod/talos/conf/ansible.cfg
ANSIBLE_LOG_PATH=/home/prod/talos/log/ansible.log
PYTHONPATH=/home/prod/talos/lib/ansible/lib
export whoami=prod
export HOME=/tmp
#/home/prod/talos/lib/ansible/bin/ansible-playbook /home/prod/talos/src/playbooks/inventory.yml -e 'talos_env=localdb action=list #entity=host'
/home/prod/talos/src/wrapper/inventory "action=list entity=host" --format=json