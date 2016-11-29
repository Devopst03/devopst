<?php

class Service extends Model {
	public static $table = "service";

	public function link() {
		return "/service/" . $this->id;
	}

	static function find_by_id($id) {
		return self::find_by_sql("SELECT * FROM service WHERE id=? AND status=1 LIMIT 1", array($id));
	}

	public function hosts() {
		return Host::find_by_service($this);
	}

	static function find_all_by_host($host) {
		return self::find_all_by_sql("SELECT service.* FROM service, product_env_service_host WHERE service.status=1 AND product_env_service_host.status=1 AND service.id=product_env_service_host.service_id AND product_env_service_host.host_id=? GROUP BY service.id ORDER BY service.name", array($host->id));
	}

}
