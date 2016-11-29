<?php

// Dashboard columns. 2 or 3
$config['supervisor_cols'] = 2;

// Refresh Dashboard every x seconds. 0 to disable
$config['refresh'] = 0;

// Enable or disable Alarm Sound
$config['enable_alarm'] = false;

// Show hostname after server name
$config['show_host'] = false;

// By default show all products host.
$inventoryCommand = "show host";
if (!empty($_REQUEST['env_flag']) && !empty($_REQUEST['env']) && !empty($_REQUEST['product'])) {
    $env = $_REQUEST['env'];
    $product = $_REQUEST['product'];
    $inventoryCommand = "show product=$product env=$env";
    if( $env == "all") {
        $inventoryCommand = "show product=$product";
    }
}
$hostsJson = shell_exec("/home/prod/talos/src/wrapper/talos $inventoryCommand --json");
$hosts = json_decode($hostsJson, true);

$supervisorServers = array();
foreach ($hosts as $key => $value) {

    $hostname = $value['name'];
    if (!empty($_REQUEST['env_flag']) && !empty($_REQUEST['env']) && !empty($_REQUEST['product'])) {
        $hostname = $value['host_name'];
    }
    $supervisorServers[$hostname]['url'] = 'http://' . $hostname . '.glam.colo/RPC2';
    $supervisorServers[$hostname]['port'] = 9001;
}

$config['supervisor_servers'] = $supervisorServers;

// Set timeout connecting to remote supervisord RPC2 interface
$config['timeout'] = 3;

// Path to Redmine new issue url
$config['redmine_url'] = 'http://redmine.url/path_to_new_issue_url';

// Default Redmine assigne ID
$config['redmine_assigne_id'] = '69';
