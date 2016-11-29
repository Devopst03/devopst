<?php

class DB {
  public static $db = null;

  public static function setup($conf) {
    self::$db = new PDO("mysql:host=".$conf['host'].";dbname=".$conf['name'], $conf['user'], $conf['password']);
  }

  public static function q($sql, $args=null) {
    $s = self::$db->prepare($sql);
    if (defined("LOG_SQL") && LOG_SQL) error_log($sql . " " . json_encode($args));
    if (!$s->execute($args ? $args : array())) {
      var_dump($s->errorinfo());
      return false;
    }
    return $s;
  }

  public static function qa($sql, $args=null, $classname=null) {
    $s = self::q($sql, $args);
    if ($classname) { return $s->fetchAll(PDO::FETCH_CLASS, $classname); }
    return $s->fetchAll();
  }

  public static function q1($sql, $args=null, $classname=null) {
    $s = self::q($sql, $args);
    if ($classname) { return $s->fetchObject($classname); }
    return $s->fetch();
  }
}
