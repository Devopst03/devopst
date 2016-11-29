import ansible.runner, pprint

runner = ansible.runner.Runner(
    module_name='talos_agent.py',
    module_args='dump',
    remote_user='prod',
    sudo=False,
    pattern='all',
    host_list='production.inventory',
)

# Ansible now pops off to do it's thing via SSH
response = runner.run()

pprint.pprint(response)
