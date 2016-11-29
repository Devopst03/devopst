import os
import time
import json
from prettytable import PrettyTable

def pretty_print(data):
    return json.dumps(data, sort_keys=True, indent=4)

class CallbackModule(object):
    """
    This is a very trivial example of how any callback function can get at play and task objects.
    play will be 'None' for runner invocations, and task will be None for 'setup' invocations.
    """

    def runner_on_failed(self, host, res, ignore_errors=False):
        if(res['invocation']['module_name']=='fail' or res['invocation']['module_name']=='debug'):
            print "DEBUG-ENDS"

    def runner_on_error(self, host, res):
        if(res['invocation']['module_name']=='fail' or res['invocation']['module_name']=='debug'):
            print "DEBUG-ENDS"

    def runner_on_ok(self, host, res):
        host=''
        if(res['invocation']['module_name']=='fail' or res['invocation']['module_name']=='debug'):
            print "DEBUG-ENDS"

        if ( res['invocation']['module_name'] == 'formatprint' ):
            data = res['invocation']['module_args'].replace('msg=','').replace('"','').replace('\t',',')
            count = 0
            for line in data.splitlines():
                list = line.split(',')
                if (count == 0):
                    x = PrettyTable(list)
                else:
                    x.add_row(list)
                x.align = "l" # Left align city names
                count = count + 1
            print "OUTPUT-START"
            print(x)
            print "OUTPUT-ENDS"
