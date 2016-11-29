<?php

function h($s) {
  return htmlspecialchars($s);
}

# switch out or add a variable in a URL's query string
function mod_url($url, $kk, $vv) {
	if (strpos($url, '?') === FALSE) {
		$pre = $url;
		$qs = "";
	} else {
		list($pre, $qs) = explode('?', $url, 2);
	}
	$bits = array();
	if ($qs) {
		foreach (explode('&', $qs) as $q) {
			list($k, $v) = explode('=', $q, 2);
			if ($k != $kk) $bits[] = $q;
		}
	}
	$bits[] = urlencode($kk) . "=" . urlencode($vv);
	return $pre . "?" . implode("&", $bits);
}

function ifseta($a, $k, $v=NULL) {
	if (array_key_exists($k, $a)) {
		return $a[$k];
	}
	return $v;
}

function fail500($s) {
  header("HTTP/1.0 500 Internal Server Error");
  echo "<h1>".h($s)."</h1>";
  exit;
}

// $d is a number of seconds in the past
function human_date_ago($d) {
	return human_duration($d) . " ago";
}

function human_duration($t) {
	if ($t == 1) return "1 sec";
	if ($t < 60) return "$t secs";
	if ($t < 3600) return sprintf("%.1f mins", $t / 60);
	if ($t < 86400) return sprintf("%.1f hours", $t / 3600);
	return sprintf("%.1f days", $t / 86400);
}

function linkify($url, $text) {
	if ($url) {
		return '<a href="'.h($url).'">'.$text.'</a>';
	}
	return $text;
}

function git_linkify($url, $branch, $text) {
	if (preg_match("|^git\@git(?:lab?)\.glam\.colo\:(.*?)/(.*?)(?:\.git)$|", $url, $m)) {
		list ($_, $group, $repo) = $m;
		$url = "http://gitlab.glam.colo/$group/$repo/commits/$branch";
		return '<a href="'.h($url).'">'.h($text).'</a>';
	}
	return h($text);
}