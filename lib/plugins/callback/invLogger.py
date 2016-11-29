#Logging on various events of a playbook.

import os
import time
import json
import os.path
from ansible import utils

HERE = os.path.split(__file__)[0]
TALOS_HOME = os.path.abspath(os.path.join(HERE, '..', '..'))
LOG_PATH = os.path.join(TALOS_HOME, 'log')

class LogMech(object):
    def __init__(self):
        self.started = time.time()
        self.logpath = LOG_PATH
        if not os.path.exists(self.logpath):
            try:
                os.makedirs(self.logpath, mode=0750)
            except OSError, e:
                if e.errno != 17:
                    raise


    def log(self, extraParams, msg):
        tstamp = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(self.started))
        entity = '' if 'entity' not in extraParams else extraParams['entity']
        product = '' if 'product' not in extraParams else extraParams['product']
        env = '' if 'env' not in extraParams else extraParams['env']
        action = '' if 'action' not in extraParams else extraParams['action']
        service = '' if 'service' not in extraParams else extraParams['service']
        name = '' if 'name' not in extraParams else extraParams['name']
        src_product = '' if 'src_product' not in extraParams else extraParams['src_product']
        src_env = '' if 'src_env' not in extraParams else extraParams['src_env']
        productName = ''

        if 'product' in extraParams or ('entity' in extraParams and ( extraParams['entity'] == 'product' or extraParams['entity'] == 'host')) and action != 'list' :
            if 'product' in extraParams :
                productName = product

            if 'entity' in extraParams and extraParams['entity'] == 'product' :
                productName = name

            if 'action' in extraParams and extraParams['action'] == 'assign' :
                productName = src_product
                env = src_env

            logtext= tstamp + ' - ' + os.getlogin() + ' - ' + productName + ' - ' + env + ' - ' + service + ' - ' + action + ' - ' + name + ' - ' + entity + ' - ' + msg + '\n'

            if productName:
                fd = open(self.logpath + '/' + productName + '.log', 'a')
                fd.write(logtext)
                fd.close()

logmech = LogMech()



class CallbackModule(object):

    def on_any(self, *args, **kwargs):
        play = getattr(self, 'play', None)

    def runner_on_ok(self, host, res):
        play = getattr(self, 'play', None)
        logmech.log(play.playbook.extra_vars, json.dumps(res))

    def runner_on_skipped(self, host, item=None):
        play = getattr(self, 'play', None)
        logmech.log(play.playbook.extra_vars, 'skipped')

    def runner_on_failed(self, host, res, ignore_errors=False):
        play = getattr(self, 'play', None)
        logmech.log(play.playbook.extra_vars, json.dumps(res))

    def runner_on_error(self, host, res):
        play = getattr(self, 'play', None)
        logmech.log(play.playbook.extra_vars, json.dumps(res))

    def runner_on_async_poll(self, host, res, jid, clock):
        play = getattr(self, 'play', None)
        logmech.log(play.playbook.extra_vars, json.dumps(res))

    def runner_on_async_ok(self, host, res, jid):
        play = getattr(self, 'play', None)
        logmech.log(play.playbook.extra_vars, json.dumps(res))

    def runner_on_async_failed(self, host, res, jid):
        play = getattr(self, 'play', None)
        logmech.log(play.playbook.extra_vars, json.dumps(res))
