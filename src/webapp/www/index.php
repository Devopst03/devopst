<?php

// Talos web interface

define("LOG_SQL", FALSE);
define("DOC_ROOT", dirname(__FILE__));
define("WEBAPP_ROOT", dirname(DOC_ROOT));
define("TALOS_COMMAND_LINE", dirname(WEBAPP_ROOT) . "/wrapper/talos");
define("CONTROLLER_ROOT", WEBAPP_ROOT . "/classes/controllers");
define("MODEL_ROOT", WEBAPP_ROOT . "/classes/models");
define("VIEW_ROOT", WEBAPP_ROOT . "/views");
ini_set("display_errors", 1);

require_once(WEBAPP_ROOT . "/classes/lib/common.php");
require_once(WEBAPP_ROOT . "/classes/lib/db.php");
require_once(WEBAPP_ROOT . "/classes/lib/Spyc.php");
if (!ini_get("short_open_tag")) {
  fail500("short_open_tag is set to off in your php.ini (".php_ini_loaded_file().").  Please enable it.");
}

class JSONResponse {
  public $response = null;
  function __construct($response) {
    $this->response = $response;
  }
}

class Controller {
  public $content_type = 'text/html';
  public $outputting_raw = false;

  function raw_output($content_type) {
    $this->content_type = $content_type;
    $this->outputting_raw = true;
  }

  function render($view_fn) {
    require(VIEW_ROOT . "/" . $view_fn . ".php");
  }
}

class Model {
  static function find_by($col, $val) {
    return DB::q1("SELECT * FROM ".static::$table." WHERE status=1 AND $col=? LIMIT 1", array($val), get_called_class());
  }

  static function find_by_sql($sql, $args=null) {
    return DB::q1($sql, $args, get_called_class());
  }

  static function find_all_by_sql($sql, $args=null) {
    return DB::qa($sql, $args, get_called_class());
  }

  static function find_all($order_by = null) {
    return DB::qa("SELECT * FROM ".static::$table." WHERE status=1 ".($order_by ? " ORDER BY $order_by" : ""), null, get_called_class());
  }

  static function query($sql, $args = null) {
    return DB::qa($sql, $args);
  }
}

foreach (glob(MODEL_ROOT."/*.php") as $f) { require_once($f); }

class EntryPoint {
  function route_request() {
    if (!isset($_SERVER['PATH_INFO'])) {
      fail500("PATH_INFO not accessible to PHP; .htaccess is probably not being read.  Check that the relevant Directory block in your httpd.conf has AllowOverride All.");
    }
    $routes = array(
		    "/" => array("controller" => "index", "action" => "index"),
        "/env/(?<name>[^/]+)" => array("controller" => "index", "action" => "env"),
        "/env/(?<name>[^/]+)/ansibleinventory" => array("controller" => "index", "action" => "env_ansibleinventory"),
        "/host/(?<id>\d+)" => array("controller" => "index", "action" => "host"),
        "/host/(?<id>\d+)/status" => array("controller" => "index", "action" => "host_status"),
        "/product/(?<id>\d+)" => array("controller" => "index", "action" => "product"),
        "/service/(?<id>\d+)" => array("controller" => "index", "action" => "service"),
        "/build/(?<id>\d+)" => array("controller" => "index", "action" => "build"),
        "/deploy/(?<id>\d+)" => array("controller" => "index", "action" => "deploy"),
		    "/service_mgt/(?<id>\d+)" => array("controller" => "index", "action" => "service_mgt"),
        "/notify/gatekeeper" => array("controller" => "index", "action" => "notify_gatekeeper"),
		    );

    foreach ($routes as $re => $info) {
      $m = null;
      if (preg_match("#^$re$#", $_SERVER['PATH_INFO'], $m)) {
        $info['args'] = $m;
        return $info;
      }
    }
  }

  static function main() {
    $local_conf = array_merge(
      array('env' => 'local'),
      spyc_load_file(WEBAPP_ROOT . "/../../conf/local.conf")
    );
    $conf = spyc_load_file(WEBAPP_ROOT . "/../playbooks/group_vars/all");
    if (!isset($conf["database"][$local_conf['env']])) {
      fail500("src/playbooks/group_vars/all is missing MySQL connection information for your environment (".$local_conf['env'].", which is set in conf/local.conf)");
    }

    DB::setup($conf["database"][$local_conf['env']]);

    $e = new self;
    $route_info = $e->route_request();
    if (!$route_info) {
      header("HTTP/1.0 404 not found");
      echo "404";
      return;
    }

    $classname = ucfirst($route_info['controller']) . "Controller";
    require(CONTROLLER_ROOT . "/$classname.php");
    $controller = new $classname;

    $controller->url = $_SERVER['REQUEST_URI'];

    $action = "action_" . $route_info['action'];
    $action_result = $controller->$action($route_info['args']);

    if ($action_result instanceof JSONResponse) {
      header("Content-Type: application/json");
      echo json_encode($action_result->response);
    } elseif ($controller->outputting_raw) {
      // the controller has called $this->raw_output($content_type);
      header("Content-Type: ".$controller->content_type);
      $controller->render($route_info['controller'].'/'.$route_info['action']);
    } else {
      // render as usual, in html with header and footer
      $controller->render("common/header");
      $controller->render($route_info['controller'].'/'.$route_info['action']);
      $controller->render("common/footer");
    }
  }
}

EntryPoint::main();