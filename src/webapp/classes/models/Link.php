<?php

class Link extends Model {
	static $table = "product_env_service_host";

	public static function find($o) {
		return new LinkList(self::find_all_by_sql("SELECT product_env_service_host.*, product.name product_name, env.name env_name, service.name service_name, host.name host_name
			FROM product_env_service_host, product, env, service, host
			WHERE product_env_service_host.status=1 AND product.status=1 AND env.status=1 AND service.status=1 AND host.status=1
			AND product.id=product_env_service_host.product_id AND env.id=product_env_service_host.env_id AND service.id=product_env_service_host.service_id AND host.id=product_env_service_host.host_id
			AND ".$o::$table.".id=?", array($o->id)));
	}

	public function most_recent_deployment() {
		return DeployedService::find_most_recent_by_link($this);
	}

	function product() {
		return Product::find_by_id($this->product_id);
	}

	function env() {
		return Environment::find_by_id($this->env_id);
	}

	function service() {
		return Service::find_by_id($this->service_id);
	}

	function host() {
		return Host::find_by_id($this->host_id);
	}

}
