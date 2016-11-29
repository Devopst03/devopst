<!DOCTYPE html>
<html>
<head>
	<title>Talos<?= isset($this->title) ? " - ".strip_tags($this->title) : "" ?></title>
	<link rel="stylesheet" type="text/css" href="/main.css">
</head>
<body>
	<?php
	if (!isset($this->title)) {
		?>
		<h1 class="packed">Talos <span style="color: gray;">
		| <a href="http://app260.glam.colo/ganglia/ganglia-web/">Ganglia</a>
		| <a href="http://sfcolo-logstash.mode.com:5001/#/dashboard/elasticsearch/Mode">Logstash</a>
		| <a href="http://app148.glam.colo/memdash/stats.php?cluster=Yoko">Memcache</a>
		| Cloudera:
			<a href="http://cscpapp30.glam.colo:7180/cmf/home">prod</a>
			<a href="http://ggcsapp01.glam.colo:7180/cmf/home">dev0</a>
			<a href="http://cscpapp71.glam.colo:7180/cmf/home">dev2</a>
		| HAProxy:
			<a href="http://sfcolo.mode.com/haproxy?stats">yoko</a>
			<a href="http://sfcolo-bacon.mode.com/haproxy?stats">bacon</a>
			<a href="http://sfcolo-mysql.mode.com/haproxy?stats">mysql</a>
			<a href="http://sfcolo-mock.mode.com/haproxy?stats">mock</a>
		| Search:
			<a href="http://app154.glam.colo:9200/_plugin/head/">head</a>
			<a href="http://app303.glam.colo:9200/_plugin/HQ/">HQ</a>
		| Search2:
			<a href="http://search2.glam.colo/_plugin/head/">head</a>
			<a href="http://search2.glam.colo/_plugin/kopf/">kopf</a>
		| <a href="http://sfcolo-solr.mode.com:8080/solr/">Solr</a>
		| <a href="http://maven.mode.com/">Nexus</a>
		| Supervisord:
			<a href="dashboard/index.php">dashboard</a>
		| Kafka:
			<a href="http://cscpapp64.glam.colo:5000/">manager</a>
		| Caviar:
			<a href="http://cdn-orig.mode.com/admin/">sync admin</a>
		</span></h1><?php
	} else {
		?><h1><a href="/">Talos</a> - <?= $this->title ?></h1><?php
	} ?></h1>
