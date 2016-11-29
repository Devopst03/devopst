<?php

class Deploy extends Model {
	public static $table = "deploy";
	static $cols = "*, unix_timestamp() - unix_timestamp(created_date) created_ago, unix_timestamp(finished_date) - unix_timestamp(created_date) time_taken";

	public function link() {
		return "/deploy/" . $this->id;
	}
	
	public function name() {
		return $this->product()->name . "/" . $this->id;
	}

	static function find_all($limit=15) {
		return self::find_all_by_sql("SELECT ".self::$cols." FROM deploy ORDER BY created_date DESC LIMIT $limit");
	}

	static function find_by_id($id) {
		return self::find_by_sql("SELECT ".self::$cols." FROM ".self::$table." WHERE id=? AND status=1 LIMIT 1", array($id));
	}

	function product() {
		return Product::find_by_id($this->product_id);
	}

	function env() {
		return Environment::find_by_id($this->env_id);
	}

	function deployed_services() {
		return DeployedService::find_all_by_deploy($this);
	}

}
