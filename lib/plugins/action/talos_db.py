# Action plugin to allow updating Talos database while we're doing deployments.
# This is an action plugin rather than a module because we want it to execute
# on the control machine rather than the host we're deploying to.

from __future__ import (absolute_import, division, print_function)
__metaclass__ = type

import json
import os
import os.path
import pprint
import tempfile
import sys
HERE = os.path.split(__file__)[0]
TALOS_HOME = os.path.abspath(os.path.join(HERE, '..', '..', '..'))
TALOS_LIB = os.path.join(TALOS_HOME, 'lib', 'talos')
if TALOS_LIB not in sys.path: sys.path.insert(0, TALOS_LIB)
import talos_data

from ansible import constants as C
from ansible.plugins.action import ActionBase
from ansible.utils.boolean import boolean
from ansible.utils.hashing import checksum
from ansible.utils.unicode import to_bytes


class ActionModule(ActionBase):

    def fail(self, result, msg):
        result['failed'] = True
        result['msg'] = msg
        return result

    def get_host_info(self, result, task_vars):
        #print("get host info")
        host = self.talos_db.load_by_id('host', task_vars['host_id'])
        #print("host: %s" % `host`)
        result['host'] = host.json()
        #print("host json: %s" % `result['host']`)
        products = {}
        for link in self.talos_db.load('link', {'host_id': host.id}):
            #print("link: %s" % `link`)
            last_deployment = link.most_recent_deployment()
            #print("last dep: %s" % `last_deployment`)
            products.setdefault((link.product_name, link.env_name, link.product_id), []).append([link.service_name, last_deployment.json() if last_deployment else None])
        result['products'] = []
        for k, v in products.items():
            result['products'].append(list(k) + [v])
        return result

    def run(self, tmp=None, task_vars=None):
        if task_vars is None:
            task_vars = dict()

        result = super(ActionModule, self).run(tmp, task_vars)

        self.talos_db = talos_data.TalosDB(task_vars['talos_env']) if task_vars['database'].get(task_vars['talos_env']) else None

        deploy_id = task_vars.get('deploy_id', None)
        product_id = task_vars.get('product_id', None)
        env_id = task_vars.get('env_id', None)
        service_id = task_vars.get('service_id', None)
        host_id = task_vars.get('host_id', None)
        print(self._task.args)

        command = self._task.args.get('command')
        if not command:
            pass
        elif command == 'get_host_info':
            return self.get_host_info(result, task_vars)
        else:
            return self.fail(result, "Invalid talos_db command: %s" % command)

        where = 'deploy_id=%s AND product_id=%s AND env_id=%s AND host_id=%s AND service_id=%s'
        where_values = [deploy_id, product_id, env_id, host_id, service_id]

        keys = []
        values = []

        for k in ('service_status', 'rotation_status', 'alert_status', 'installed_build', 'supervisor_status', 'deploying', 'success'):
            v = self._task.args.get(k, None)
            if v is None: continue

            print("update %s" % k)
            keys.append("%s=%%s" % k)
            values.append(v)

        if not len(keys):
            return self.fail(result, "talos_db can't figure out what to do with this command!")

        self.talos_db.q("update deployed_service set updated_date=now(), %s WHERE %s" % (", ".join(keys), where),
            values + where_values)

        return result
