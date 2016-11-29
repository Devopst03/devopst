<h1>In this environment: by host</h1>
<ul>
<?php
$hosts = $this->links->collect(array("host_name", "product_name"));
ksort($hosts);
foreach ($hosts as $host => $products) {
	ksort($products);
	$prods = array();
	foreach ($products as $product => $links) {
		$services = array();
		foreach ($links as $link) {
			$services[] = '<a href="/service/'.h($link->service_id).'">'.h($link->service_name).'</a>';
		}
		$prods[] = '<a href="/product/'.h($link->product_id).'">'.h($link->product_name).'</a> ('.implode(", ", $services).')';
	}

	echo '<li><a href="/host/'.h($link->host_id).'">'.h($host)."</a>: ".implode(", ", $prods)."</li>";
}
?>
</ul>

<h1>In this environment: by product/service</h1>
<ul>
<?php
foreach ($this->links->collect(array("product_id", "service_name")) as $product_id => $services) {
	$product = Product::find_by_id($product_id);
	?><li><a href="<?= h($product->link()) ?>"><?= h($product->name) ?></a></li><ul><?php
	foreach ($services as $service => $links) {
		echo "<li>".h($service)."</li><ul>";
		foreach ($links as $link) {
			echo '<li><a href="/host/'.h($link->host_id).'">'.h($link->host_name)."</a>";
			$deployed_service = $link->most_recent_deployment();
			require("_deployed_service.php");
			echo "</li>";
		}
		echo "</ul>";
	}
	echo "</ul>";
}
?>
</ul>
