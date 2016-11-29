<?php

echo "# Ansible inventory file for environment: {$this->environment->name}\n";

foreach ($this->hosts as $host) {
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/host/' . $host->id . '/status';
	$hostname = $host->name;
	if (strpos($hostname, '.') === FALSE) {
		$hostname .= '.glam.colo';
	}
	echo "$hostname talos_host_id=$host->id talos_ip=$host->ip talos_status_url=$url\n";
}