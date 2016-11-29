<h2>Recent builds (<?= h($this->first_build + 1) ?> - <?= h($this->first_build + count($this->builds)) ?>)</h2>
<ul>
<?php
foreach ($this->builds as $build) {
	require(dirname(__FILE__)."/_build.php");
}
?>
</ul>
<?php
$more_builds = mod_url($this->url, 'n_builds', max(25, $this->n_builds));
if ($this->first_build > 0) {
	?><a href="<?= mod_url($more_builds, 'first_build', max(0, $this->first_build - $this->n_builds)) ?>">&larr; prev</a> <?php
}
if (count($this->builds) == $this->n_builds) {
	?><a href="<?= mod_url($more_builds, 'first_build', $this->first_build + $this->n_builds) ?>">next &rarr;</a> <?php
}
?>
<h2>Where it's installed</h2>
<ul>
<?php
$envs = $this->links->collect(array("env_id", "service_name"));
ksort($envs);
foreach ($envs as $env_id => $services) {
	$env = Environment::find_by_id($env_id);
	?><li><a href="<?= h($env->link()) ?>"><?= h($env->name) ?></li>
	<ul><?php
	ksort($services);
	foreach ($services as $service => $links) {
		echo "<li>".h($service)."</li><ul>";
		foreach ($links as $link) {
			echo '<li><a href="/host/'.h($link->host_id).'">'.h($link->host_name)."</a>";
			$deployed_service = $link->most_recent_deployment();
			require("_deployed_service.php");
			echo "</li>";
		}
		?></ul><?php
	}
	?></ul><?php
}
?>
</ul>
