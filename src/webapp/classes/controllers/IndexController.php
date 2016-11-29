<?php

class IndexController extends Controller {
  function action_index() {
    $this->environments = Environment::find_all("name");
    $this->products = Product::find_all("name");
    $this->services = Service::find_all("name");
    $this->builds = Build::find_all();
    $this->deploys = Deploy::find_all();
    $this->service_mgt = ServiceMgt::find_all();
  }

  function action_env($args) {
    $this->environment = Environment::find_by_name($args['name']);
    $this->title = 'Environment: <a href="' . h($this->environment->link()) . '">' . $this->environment->name . '</a>';
    $this->links = Link::find($this->environment);
  }

  function action_env_ansibleinventory($args) {
    $this->raw_output("text/plain");
    $this->environment = Environment::find_by_name($args['name']);
    $this->hosts = $this->environment->hosts();
  }

  function action_host($args) {
    $this->host = Host::find_by_id($args['id']);
    $this->title = 'Host: <a href="' . h($this->host->link()) . '">' . $this->host->name_and_ip() . "</a>";
    $this->links = Link::find($this->host);
  }

  function action_host_status($args) {
    $this->host = Host::find_by_id($args['id']);
    if (!$this->host) { return new JSONResponse(array("success" => FALSE, "error" => 404, "msg" => "Host not found")); }

    $status = json_decode(file_get_contents("php://input"));
    if (!$status) { return new JSONResponse(array("success" => FALSE, "error" => 400, "msg" => "Couldn't parse JSON payload")); }

    $this->host->update_status($status);
    return new JSONResponse(array("success" => TRUE, "msg" => "Status received"));
  }

  function action_notify_gatekeeper($args) {
    $jsonDecoded = json_decode(file_get_contents("php://input"));
    if (!$jsonDecoded) { return new JSONResponse(array("success" => FALSE, "error" => 400, "msg" => "Couldn't parse JSON payload")); }

    $product = $jsonDecoded->{"Product Name"};
    $env = $jsonDecoded->{"Env"};
    $build_name = $jsonDecoded->{"Build Name"};
    $status = $jsonDecoded->{"Status"};
    $url = $jsonDecoded->{"Test Result Url"};

    #example: talos -A notify --notify-status started --notifier gatekeeper -P talos-test -E dev1 -b build1 -u gatekeeper --url http://testurl.com
    $commandLine = TALOS_COMMAND_LINE . " -A notify -P " . $product . " -E " . $env . " -b " . $build_name . " --notify-status " . $status . " --url " . $url . " --notifier gatekeeper -u gatekeeper > /dev/null 2>&1 &";
    exec($commandLine, $outputArray, $returnStatus);

    if ($returnStatus != 0) { return new JSONResponse(array("success" => FALSE, "msg" => "Error executing command.", "return status" => $returnStatus)); }

    return new JSONResponse(array("success" => TRUE, "msg" => "Notification received"));
  }

  function action_product($args) {
    $this->product = Product::find_by_id($args['id']);
    $this->title = 'Product: <a href="' . h($this->product->link()) . '">' . $this->product->name . '</a>';
    $this->links = Link::find($this->product);
    $this->first_build = (int)ifseta($_GET, 'first_build', 0);
    $this->n_builds = (int)ifseta($_GET, 'n_builds', 10);
    $this->builds = Build::find_by_product($this->product, $this->n_builds, $this->first_build);
  }

  function action_service($args) {
    $this->service = Service::find_by_id($args['id']);
    $this->title = 'Service: <a href="' . h($this->service->link()) . '">' . $this->service->name . '</a>';
    $this->hosts = $this->service->hosts();
    $this->links = Link::find($this->service);
  }

  function action_build($args) {
    $this->build = Build::find_by_id($args['id']);
    $this->title = 'Build: <a href="' . h($this->build->link()) . '">' . h($this->build->name()) . '</a>';
  }

  function action_deploy($args) {
    $this->deploy = Deploy::find_by_id($args['id']);
    $this->deployed_services = $this->deploy->deployed_services();
    $this->title = 'Deploy: <a href="' . h($this->deploy->link()) . '">' . h($this->deploy->name()) . '</a>';
  }

  function action_service_mgt($args) {
    $this->service_mgt = ServiceMgt::find_by_id($args['id']);
    $this->service_mgt_services = $this->service_mgt->service_mgt_service();
    $this->title = 'Service Action: <a href="' . h($this->service_mgt->link()) . '">' . h($this->service_mgt->name()) . '</a>';
  }

}
