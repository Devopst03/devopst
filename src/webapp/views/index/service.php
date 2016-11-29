<h2>Where it's installed</h2>
<ul>
<?php foreach ($this->links as $link) {
    $deployed_service = $link->most_recent_deployment();
    $host = $link->host();
    $env = $link->env();
    $product = $link->product();
    ?><li>
        <a href="<?= h($host->link()) ?>"><?= h($host->name_and_ip()) ?></a> - <a href="<?= h($product->link()) ?>"><?= h($product->name) ?></a> <a href="<?= h($env->link()) ?>"><?= h($env->name) ?></a>
        <?php include("_deployed_service.php"); ?>
    </li>
<?php } ?>
</ul>
