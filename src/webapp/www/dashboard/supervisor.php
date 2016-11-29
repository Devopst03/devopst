<?php
/**
 * Start - To get dynamic inventory host list.
 **/
$product = !empty ($_REQUEST['product']) ? $_REQUEST['product'] : '';
$env = !empty ($_REQUEST['env']) ? $_REQUEST['env'] : '';
$service = !empty ($_REQUEST['service']) ? $_REQUEST['service'] : '';

$inventoryCmd = '/home/prod/talos/src/wrapper/talos show';

if ($product)
    $inventoryCmd .= ' product='. $product;

if ($env)
    $inventoryCmd .= ' env='. $env;

if ($service)
    $inventoryCmd .= ' service='. $service;


$allHostFlag = false;
if ($product == '' && $env == '' && $service == '') {
    $allHostFlag = true;
    $inventoryCmd .= ' host';
}

$inventoryCmd .= ' --json';
$all = shell_exec($inventoryCmd);
$all = json_decode($all, true);

/**
 * End - To get dynamic inventory host list.
 **/

// Dashboard columns. 2 or 3
$config['supervisor_cols'] = 2;

// Refresh Dashboard every x seconds. 0 to disable
$config['refresh'] = 0;

// Enable or disable Alarm Sound
$config['enable_alarm'] = false;

// Show hostname after server name
$config['show_host'] = false;

$supervisorServers = array();

$serviceId = 1;
foreach ($all as $key => $value) {
    $host = ($allHostFlag) ? $value['name'] : $value['host_name'];
    $hostname = (strpos($host, '.glam.colo') !== false) ? $host : $host.'.glam.colo';
    $supervisorServers[$hostname]['url'] = 'http://' . $hostname . '/RPC2';
    $supervisorServers[$hostname]['port'] = 9001;
    $serviceId++;
}

$config['supervisor_servers'] = $supervisorServers;

// Set timeout connecting to remote supervisord RPC2 interface
$config['timeout'] = 3;

// Path to Redmine new issue url
$config['redmine_url'] = 'http://redmine.url/path_to_new_issue_url';

// Default Redmine assigne ID
$config['redmine_assigne_id'] = '69';

//Overwrite the base url config
$config['base_url'] = '/supervisord-dashboard';
$config['index_page'] = '';
$config['uri_protocol']  = 'REQUEST_URI';
