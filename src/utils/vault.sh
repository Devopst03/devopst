#!/bin/bash
# Path: utils/vault_encrypt.sh
# Description   :  Uses ansible-vault to encrypt a list of files. These are files that we want to keep in git but not in plain text format.
#                  Read about ansible-vault for the allowed types of files to include for encryption.
#                  http://docs.ansible.com/ansible/playbooks_vault.html
#                  When setting a vault password after decryption, make sure to use the same password.


BASEDIR="/home/prod/talos"
FILE_LIST="$BASEDIR/conf/vault_encrypted_files"


. /usr/share/shflags/shflags

# define command-line string flags
DEFINE_boolean 'encrypt' 'false' 'Encrypt all files specified in talos/conf/vault_encrypted_files. Must be done before these files can be checked in to git' 'e'
DEFINE_boolean 'decrypt' 'false' 'Decrypt all files. Use this option if you need to edit these files.' 'd'

FLAGS "$@" || exit 1
eval set -- "${FLAGS_ARGV}"

action=
if [[ ${FLAGS_encrypt} == ${FLAGS_TRUE} ]]; then
    action="encrypt"
elif [[ ${FLAGS_decrypt} == ${FLAGS_TRUE} ]]; then
    action="decrypt"
else
    printf "Encrypt or decrypt option is missing.\n\n"
    flags_help
    exit 1
fi

if [[ -f $FILE_LIST ]] && [[ -s $FILE_LIST ]]; then
   echo "processing $FILE_LIST"

   files=
   while read -r line || [[ -n $line ]]; do
       files="$files $BASEDIR/$line"
   done < $FILE_LIST

   ansible-vault $action $files

else
   echo "$FILE_LIST does not exist or is empty."
fi



