<?php

class Environment extends Model {
	static $table = "env";

	function link() {
		return "/env/".$this->name;
	}

	static $cache = array();
	static function find_by_id($id) {
		if (array_key_exists($id, self::$cache)) return self::$cache[$id];
		$r = self::$cache[$id] = self::find_by_sql("SELECT * FROM env WHERE id=? AND status=1 LIMIT 1", array($id));
		return $r;
	}

	static function find_by_name($name) {
		return self::find_by_sql("SELECT * FROM env WHERE name=? AND status=1 LIMIT 1", array($name));
	}

	static function find_all_by_host($host) {
		return self::find_all_by_sql("SELECT env.* FROM env, product_env_service_host WHERE env.status=1 AND product_env_service_host.status=1 AND env.id=product_env_service_host.env_id AND product_env_service_host.host_id=? GROUP BY env.id ORDER BY env.name", array($host->id));
	}

	public function hosts() {
		return Host::find_by_environment($this);
	}
}
