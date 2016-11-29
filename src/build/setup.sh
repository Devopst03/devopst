#!/bin/bash
set -euo pipefail

# Talos setup script
TALOS_HOME=/home/prod/talos
ANSIBLE_HOME=$TALOS_HOME/lib/ansible

echo "Setting permissions"
chmod g+rx,o+rx ~prod/.
chgrp ansible ~prod/.

echo "Setting \$PATH using /etc/profile.d/talos.sh"
echo "export PATH=$PATH:/home/prod/talos/src/wrapper:\$PATH" > /etc/profile.d/talos.sh
echo "export PYTHONIOENCODING='utf-8'" >> /etc/profile.d/talos.sh

echo "Creating $TALOS_HOME/conf/local.conf"
echo "env: $1" > $TALOS_HOME/conf/local.conf

echo "Installing PHP and Java, among other things"
yum -y install php-talos java-talos libffi-devel

echo "Installing Ansible"
cd $ANSIBLE_HOME
make install

echo "Setting up logs cleanup in cron.daily for Talos"
ln -sf $TALOS_HOME/src/utils/talos-logs-cleanup.cron /etc/cron.daily/talos-logs-cleanup.cron

echo "Setting up Git hooks"
ln -sf $TALOS_HOME/src/build/post-commit $TALOS_HOME/.git/hooks/post-commit

if [ $# -eq 2 ]; then
    ln -sf $TALOS_HOME/src/build/pre-commit /home/prod/$2/releases/live/.git/hooks/pre-commit
    ln -sf $TALOS_HOME/src/build/pre-push /home/prod/$2/releases/live/.git/hooks/pre-push
    ln -sf $TALOS_HOME/src/build/post-merge /home/prod/$2/releases/live/.git/hooks/post-merge
    if [ -f /home/prod/$2/releases/live/.git/hooks/post-commit ];then
        mv /home/prod/$2/releases/live/.git/hooks/post-commit /home/prod/$2/releases/live/.git/hooks/post-commit.bk
    fi
    pushd /home/prod/$2/releases/live
    git config reviewboard.arcfile /home/prod/$2/releases/live/.already_reviewed_commits
    git config reviewboard.url http://reviewb.glam.com
    popd
fi

echo "Setting up Maven"
mkdir -p /usr/local/apache-maven
cd /usr/local/apache-maven
curl -O http://talos.mode.com/talos-repo/maven/apache-maven-3.3.3-bin.tar.gz
tar xvf apache-maven-3.3.3-bin.tar.gz

rm -f current
ln -sf apache-maven-3.3.3 current
ln -sf $TALOS_HOME/src/build/maven/conf/settings.xml /usr/local/apache-maven/current/conf/settings.xml

(
echo "PATH=/usr/local/apache-maven/current/bin:\$PATH"
echo "export PATH"
) > /etc/profile.d/maven.sh
source /etc/profile.d/maven.sh

echo "Checking that the prod user has sudo permissions"
grep 'Build/Deploy permissions' /etc/sudoers &>/dev/null
OUT=$?
if [ $OUT -eq 0 ];then
   echo "Ok!"
else
   echo -e "#### Added for user Build/Deploy permissions to work with sudo ###########\n%ansible     ALL=  NOPASSWD: /bin/chown, /bin/chmod, /bin/chgrp, /bin/mkdir, /bin/sh, /usr/bin/python " >> /etc/sudoers
fi
