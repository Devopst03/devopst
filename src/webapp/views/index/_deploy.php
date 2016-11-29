	<li>
		<img src="/img/<?= $deploy->success === NULL ? "hourglass" : ($deploy->success ? "accept" : "exclamation") ?>.png">
		<a href="<?= h($deploy->link()) ?>"><?= h($deploy->name()) ?></a>
		<?= "<b>".h($deploy->build_name)."</b>" ?>
		<?= $deploy->success === NULL ? "is being deployed" : ($deploy->success ? " deployed" : "failed to deploy") ?>
		on <b><a href="<?= h($deploy->env()->link()) ?>"><?= h($deploy->env()->name) ?></a></b>
		by <b><?= h($deploy->human) ?></b>
		<?= human_date_ago($deploy->created_ago) ?>
		<?= $deploy->finished_date ? "(took ".human_duration($deploy->time_taken).")" : "" ?>
	</li>
