<?php

class Product extends Model {
	public static $table = "product";

	public function link() {
		return "/product/" . $this->id;
	}

	static $cache = array();
	static function find_by_id($id) {
		if (array_key_exists($id, self::$cache)) return self::$cache[$id];
		$r = self::$cache[$id] = self::find_by_sql("SELECT * FROM product WHERE id=? AND status=1 LIMIT 1", array($id));
		return $r;
	}

}