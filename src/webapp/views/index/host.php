<h2>SSH command</h2>
<p>Click to select for easy copy and pasting:</p>
<p><input style="height: 2em; font-size: 2em;" id="hostname" onclick="this.select();" size="40" value="ssh prod@<?= h($this->host->name) ?>"></p>

<h2>Host info</h2>
<ul>
<?php foreach ($this->host->environments() as $env) { ?>
<li>Environment: <a href="<?= h($env->link()) ?>"><?= h($env->name) ?></a></li>
<?php } ?>
</ul>

<ul>
<?php foreach (array("name", "description", "ip", "mac_id", "hardware", "kernel", "cpu", "os", "memory", "disk") as $k) { ?>
<li><?= $k ?>: <b><?= h($this->host->$k) ?></b></li>
<?php } ?>
</ul>

<?php /* $status = $this->host->status();
if ($status) {
	?>
	<h2>Status<?php if ($this->host->status_received_date) { ?> (<?= human_date_ago($this->host->status_received_ago) ?>)<?php } ?></h2>
	<ul>
	<?php foreach ($status as $k => $v) {
		?>
		<li><?= h($k) ?>: <?= h(is_string($v) ? $v : json_encode($v)) ?></li>
		<?php
	}
	?>
	</ul>
	<?php
} */
?>

<h2>Services configured here</h2>

<ul>
<?php foreach ($this->links as $link) {
	$deployed_service = $link->most_recent_deployment();
	$product = $link->product();
	$service = $link->service();
	$env = $link->env();
	?>
	<li>
		<a href="<?= h($service->link()) ?>"><?= h($service->name) ?></a> for <a href="<?= h($product->link()) ?>"><?= h($product->name) ?></a> on <a href="<?= h($env->link()) ?>"><?= h($env->name) ?></a>
		<?php require("_deployed_service.php"); ?>
	</li>
<?php } ?>
</ul>
