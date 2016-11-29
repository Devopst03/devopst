<?php

class Host extends Model {
	public static $table = "host";

	static $cols = "host.*, unix_timestamp() - unix_timestamp(host.status_received_date) status_received_ago";

	function link() {
		return "/host/".$this->id;
	}

	function name_and_ip() {
		$r = $this->name;
		if ($this->ip) {
			$r .= " (" . $this->ip . ")";
		}
		return $r;
	}

	static function find_by_environment($env) {
		return self::find_all_by_sql("SELECT ".self::$cols." FROM host, product_env_service_host WHERE host.status=1 AND product_env_service_host.status=1 AND host.id=product_env_service_host.host_id AND product_env_service_host.env_id=? GROUP BY host.id ORDER BY host.name", array($env->id));
	}

	static function find_by_service($service) {
		return self::find_all_by_sql("SELECT ".self::$cols." FROM host, product_env_service_host WHERE host.status=1 AND product_env_service_host.status=1 AND host.id=product_env_service_host.host_id AND product_env_service_host.service_id=? GROUP BY host.id ORDER BY host.name", array($service->id));
	}

	static function find_by_id($id) {
		return self::find_by_sql("SELECT ".self::$cols." FROM host WHERE id=? AND status=1 LIMIT 1", array($id));
	}

	function environments() {
		return Environment::find_all_by_host($this);
	}

	function services() {
		return Service::find_all_by_host($this);
	}

	// update all relevant status fields provided from talos-agent
	function update_status($status) {
		DB::q("UPDATE host SET status_received_date=NOW(), extrastatus=? WHERE id=?", array(json_encode($status), $this->id));
	}

	function status() {
		if (!$this->extrastatus) return null;
		$status = json_decode($this->extrastatus);
		if (empty($status)) return null;
		return $status;
	}

}
