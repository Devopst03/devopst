<?php

// an array of Link objects, sortable in various ways
class LinkList extends ArrayObject {

	// makes, for example, a tree of array(product_id => array(service_id => array(Link))), if you pass it array("product_id", "service_id")
	public function collect($fields) {
		$tree = array();
		foreach ($this as $link) {
			$node =& $tree;
			foreach ($fields as $field) {
				$v = $link->$field;
				if (!isset($node[$v])) $node[$v] = array();
				$node =& $node[$v];
			}
			$node[] = $link;
		}
		return $tree;
	}

}
