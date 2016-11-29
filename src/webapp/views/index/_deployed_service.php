<?php
if ($deployed_service) {
	$deploy = $deployed_service->deploy();
	?>
	<img src="/img/<?= $deploy->success === NULL ? "hourglass" : ($deploy->success ? "accept" : "exclamation") ?>.png">
	<a href="<?= h($deploy->link()) ?>"><?= h($deploy->name()) ?></a>

	<?= (!$deployed_service->deploying && $deployed_service->success === NULL) ? "" :
		'<img src="/img/' . ($deployed_service->deploying ? "hourglass" : (
		$deployed_service->success ? "accept" : "exclamation")) . '.png">' ?>

	<?php if ($deployed_service->installed_build) { ?><b><?= h($deployed_service->installed_build) ?></b><?php } ?>
	<?php
}
