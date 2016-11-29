<?php
$serviceAction = ($this->service_mgt->action == NULL) ? '' : $this->service_mgt->action;
?>
<h2><?= ($this->service_mgt->success === NULL) ? "IN PROGRESS" : ($this->service_mgt->success ? "Service Action $serviceAction successful" : "Service Action $serviceAction failed") ?></h2>

<ul><?php
foreach ($this->service_mgt as $k => $v) {
	if (!trim($v)) continue;
	switch ($k) {
		case 'log_file_path':
			$v = '<a href="/log/' . h(basename($v)) . '">' . h($v) . '</a>';
			break;
		default:
			$v = h($v);
	}

	?><li><b><?= h($k) ?></b>: <?= $v ?></li><?
}
?>
</ul>

<h2>Services</h2>
<table width="100%">
<th>
<tr>
	<td></td>
	<td>host</td>
	<td>service</td>
	<td>service</td>
	<td>alert</td>
	<td>rotation</td>
	<td>supervisor</td>
</tr>
</th>
<?php
foreach ($this->service_mgt_services as $service_mgt_services) {
	$host = $service_mgt_services->host();
	$service = $service_mgt_services->service();
	?><tr>
		<td><?= (!$service_mgt_services->deploying && $service_mgt_services->success === NULL) ? "" :
			'<img src="/img/' . ($service_mgt_services->deploying ? "hourglass" : (
				$service_mgt_services->success ? "accept" : "exclamation")) . '.png">'
				?></td>
		<td><a href="<?= h($host->link()) ?>"><?= h($host->name) ?></a></td>
		<td><a href="<?= h($service->link()) ?>"><?= h($service->name) ?></a></td>
		<td><?= h($service_mgt_services->service_status) ?></td>
		<td><?= h($service_mgt_services->alert_status) ?></td>
		<td><?= h($service_mgt_services->rotation_status) ?></td>
		<td><?= h($service_mgt_services->supervisor_status) ?></td>
	</tr><?php
}
?>
</table>
