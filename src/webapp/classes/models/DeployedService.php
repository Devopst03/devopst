<?php

class DeployedService extends Model {
	public static $table = "deployed_service";
	static $cols = "*";

	static function find_all_by_deploy($deploy) {
		return self::find_all_by_sql("SELECT ".self::$cols." FROM ".self::$table." WHERE deploy_id=? AND status=1 ORDER BY updated_date DESC", array($deploy->id));
	}

	static function find_most_recent_by_link($link) {
		return self::find_by_sql("SELECT ".self::$cols." FROM ".self::$table." WHERE product_id=? AND service_id=? AND env_id=? AND host_id=? ORDER BY created_date DESC LIMIT 1", array($link->product_id, $link->service_id, $link->env_id, $link->host_id));
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

	function deploy() {
		return Deploy::find_by_id($this->deploy_id);
	}

}
