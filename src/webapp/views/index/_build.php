	<li style="color: <?= $build->finished_date ? "black" : "grey" ?>;">
		<img src="/img/<?= $build->success() === NULL ? "hourglass" : ($build->success() ? "accept" : "exclamation") ?>.png">
		<a href="<?= h($build->link()) ?>"><?= h($build->build_name ? $build->build_name : $build->name()) ?></a>
		(<?= git_linkify($build->git_url, $build->branch, $build->build_name ? "git" : $build->branch) ?>)
		<?= human_date_ago($build->created_ago) ?>
		by <b><?= h($build->human) ?></b>
		<?= $build->failed() ? "FAILED" :
			($build->finished_date ? "(took ".human_duration($build->time_taken).")" : "(unfinished)") ?>
	</li>
