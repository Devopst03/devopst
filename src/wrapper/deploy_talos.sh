#!/bin/bash
# Description   :  Used to install/deploy talos to varios environments.
#               :  This intalls all dependencies and services (including db).
#                  Also configrures and restart the webapp.
#                  Uses the default talos hosts file. The location is defined in ansible.cfg.


# source shflags
. /usr/share/shflags/shflags


BASEDIR="/home/prod/talos"
VAULT_PASSWORD_FILE="$BASEDIR/conf/.vault_pass.txt"

# define command-line string flags
DEFINE_string 'action' 'deploy' 'install or deploy. Install means install talos along with dependencies. Deploy means, make deploy branch active.' 'A'
DEFINE_string 'branch' '' 'talos branch name to make active' 'B'
DEFINE_string 'env' '' 'dev1, dev2, staging or production' 'E'
DEFINE_boolean 'dbsetup' 'false' 'Setup db and mysql root password, create a bare talos db. This is only to be done on a brand new server. This is not a data restore solution.' ''
DEFINE_string 'tags' '' 'Ansible tags to activate (this will ONLY run tasks and playbooks that are tagged with these. Format: tag1,tag2,tag3' ''
DEFINE_string 'skiptags' '' 'Ansible tags to skip (this will ONLY run sections in playbooks that are NOT tagged with these. Format: tag1,tag2,tag3' ''
DEFINE_boolean 'verbose' 'false' 'Extra verbosity' 'v'
DEFINE_string 'user' '' 'User who is initiating the action, used for reporting and notification purposes, required for all types of actions' 'u'

FLAGS "$@" || exit 1
eval set -- "${FLAGS_ARGV}"


# check for required product param
if [ -z "${FLAGS_branch}" ]; then
    printf "\n*** Branch parameter is missing. \n\n"
    flags_help
    exit 1
fi

if [ -z "${FLAGS_env}" ]; then
    printf "\n*** Env parameter is missing.\n\n"
    flags_help
    exit 1
fi

if [ -z "${FLAGS_user}" ]; then
    printf "\n*** User parameter is missing.  Example: -u yourname\n\n"
    flags_help
    exit 1
fi


ANSIBLEFLAGS=
if [ ${FLAGS_verbose} == ${FLAGS_TRUE} ]; then
  ANSIBLEFLAGS="-vvvv"
fi

if [ ! -z ${FLAGS_tags} ]; then
  ANSIBLEFLAGS="${ANSIBLEFLAGS} --tags ${FLAGS_tags}"
fi

if [ ! -z ${FLAGS_skiptags} ]; then
  ANSIBLEFLAGS="${ANSIBLEFLAGS} --skip-tags ${FLAGS_skiptags}"
fi

# check for ansible vault password file. If doesn't exist ask for vault password
if [ -f "${VAULT_PASSWORD_FILE}" ]; then
  echo using vault password file: $VAULT_PASSWORD_FILE
  ANSIBLEFLAGS="${ANSIBLEFLAGS} --vault-password-file ${VAULT_PASSWORD_FILE}"
else
  echo "$VAULT_PASSWORD_FILE password file not found. To avoid this prompt create ~talos/conf/.vault_pass.txt file with a password in a single line."
  ANSIBLEFLAGS="${ANSIBLEFLAGS} --ask-vault-pass"
fi

ansible-playbook $BASEDIR/src/playbooks/deploy_talos.yml -e "action=${FLAGS_action} branch=${FLAGS_branch} env=${FLAGS_env} user=${FLAGS_user} dbsetup=${FLAGS_dbsetup} " ${ANSIBLEFLAGS} -k
