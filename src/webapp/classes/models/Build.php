<?php

class Build extends Model {
	public static $table = "build";
	static $cols = "*, unix_timestamp() - unix_timestamp(created_date) created_ago, unix_timestamp(finished_date) - unix_timestamp(created_date) time_taken";

	public function failed() {
		return $this->failed_date ? TRUE : FALSE;
	}

	public function success() {
		if ($this->failed_date) return 0;
		if ($this->finished_date) return 1;
		return NULL;
	}

	public function link() {
		return "/build/" . $this->id;
	}

	public function name() {
		return $this->product . "/" . $this->id;
	}

	public function product() {
		if (!$this->product_id) return NULL;
		return Product::find_by_id($this->product_id);
	}

	static function find_by_product($product, $count=NULL, $first=NULL) {
		$count = min(100, max(10, (int)$count));
		$first = max(0, (int)$first);

		return self::find_all_by_sql("SELECT ".self::$cols." FROM build WHERE product_id=? ORDER BY created_date DESC LIMIT $first, $count", array($product->id));
	}

	static function find_all($limit=15) {
		return self::find_all_by_sql("SELECT ".self::$cols." FROM build ORDER BY created_date DESC LIMIT $limit");
	}

	static function find_by_id($id) {
		return self::find_by_sql("SELECT ".self::$cols." FROM build WHERE id=? AND status=1 LIMIT 1", array($id));
	}

}
