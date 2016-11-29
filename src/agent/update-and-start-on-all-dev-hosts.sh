# make an inventory file that incldues all dev hosts
echo "# All dev hosts" > dev.inventory
for envid in 11 12 13 14 15 16 17; do
    DEV=dev$envid
    echo "Fetching hosts for $DEV"
    curl http://talos.mode.com/env/$DEV/ansibleinventory > $DEV.inventory
    cat $DEV.inventory >> dev.inventory
done

# install
ansible-playbook -i dev.inventory install.playbook

# start
ansible-playbook -i dev.inventory start_agent.playbook
