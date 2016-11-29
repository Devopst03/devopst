<style>
ul.navlist {
	padding-left: 0;
}
ul.navlist li {
  display: inline;
  padding-right: 0.5em;
}
</style>

<ul class="navlist">
   <li><b>Products</b>:</li>
<?php foreach ($this->products as $product) { ?>
   <li><a href="<?= h($product->link()) ?>"><?= h($product->name) ?></a></li>
<?php } ?>
</ul>

<ul class="navlist">
   <li><b>Environments</b>:</li>
<?php foreach ($this->environments as $env) { ?>
   <li><a href="<?= h($env->link()) ?>"><?= h($env->name) ?></a></li>
<?php } ?>
</ul>
<h2>Recent deployments</h2>
<ul>
<?php
foreach ($this->deploys as $deploy) {
	require(dirname(__FILE__)."/_deploy.php");
}
?>
</ul>

<h2>Recent builds (see also: <a href="/talos-repo/yoko/buildlist">yoko buildlist</a>)</h2>

<ul>
<?php
foreach ($this->builds as $build) {
	require(dirname(__FILE__)."/_build.php");
}
?>
</ul>


<h2>Services</h2>

<ul class="navlist">
<?php foreach ($this->services as $service) { ?>
   <li><a href="<?= h($service->link()) ?>"><?= h($service->name) ?></a></li>
<?php } ?>
</ul>
