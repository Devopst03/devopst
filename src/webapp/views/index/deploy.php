<h2><?= ($this->deploy->success === NULL) ? "IN PROGRESS" : ($this->deploy->success ? "Deployment successful" : "Deployment failed") ?></h2>

<ul><?php
foreach ($this->deploy as $k => $v) {
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
	<td>build</td>
</tr>
</th>
<?php
foreach ($this->deployed_services as $deployed_service) {
	$host = $deployed_service->host();
	$service = $deployed_service->service();
	?><tr>
		<td><?= (!$deployed_service->deploying && $deployed_service->success === NULL) ? "" :
			'<img src="/img/' . ($deployed_service->deploying ? "hourglass" : (
				$deployed_service->success ? "accept" : "exclamation")) . '.png">'
				?></td>
		<td><a href="<?= h($host->link()) ?>"><?= h($host->name) ?></a></td>
		<td><a href="<?= h($service->link()) ?>"><?= h($service->name) ?></a></td>
		<td><?= h($deployed_service->service_status) ?></td>
		<td><?= h($deployed_service->alert_status) ?></td>
		<td><?= h($deployed_service->rotation_status) ?></td>
		<td><?= h($deployed_service->supervisor_status) ?></td>
		<td><?= h($deployed_service->installed_build) ?></td>
	</tr><?php
}
?>
</table>
