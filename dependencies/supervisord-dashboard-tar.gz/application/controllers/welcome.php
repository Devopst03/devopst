<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller {

    /**
     * This is main method of the dashboard which will load supervisord dashboard first time.
     */
	public function Index()	{

        // By default list all product.
        $productJson = shell_exec("/home/prod/talos/src/wrapper/talos show product --json");
        $products = json_decode($productJson, true);
        if(!empty($products))
            $data['products'] = $products;

        // By default list all env.
        $envJson =  shell_exec("/home/prod/talos/src/wrapper/talos show env --json");
        $envs = json_decode($envJson, true);
        if(!empty($envs))
            $data['envs'] = $envs;

        // By default list all services.
        $serviceJson =  shell_exec("/home/prod/talos/src/wrapper/talos show service --json");
        $services = json_decode($serviceJson, true);
        if(!empty($services))
            $data['services'] = $services;

        $mute = $this->input->get('mute');
		if($this->input->get('mute') == 1){
			$mute_time = time()+600;
			setcookie('mute',$mute_time,$mute_time,'/');
			Redirect();
		}

		if($this->input->get('mute')==-1){
			setcookie('mute',0,time()-1,'/');
			Redirect();
		}
		$data['muted'] = $this->input->cookie('mute');

		$this->load->helper('date');
		$servers = $this->config->item('supervisor_servers');

		foreach($servers as $name=>$config){
			$data['list'][$name] = $this->_request($name,'getAllProcessInfo');
		}
		$data['cfg'] = $servers;

        if(!empty($_REQUEST['env_flag']) && !empty($_REQUEST['env'])) {
            $this->load->view('env_product',$data);
        } else {
            $this->load->view('welcome',$data);
        }
	}

    /**
     * Method to get host based on selected Env.
     *
     * @return string Drop-down html.
     */
    public function getenv() {
        $product = $this->input->post('product');
        $productEnvJson = shell_exec("/home/prod/talos/src/wrapper/talos show env product=$product --json");
        $productEnv = json_decode($productEnvJson, true);

        $option = '';
        if(!empty($productEnv)) {
            $option .= '<option>--Select Env--</option>';
            $option .= '<option value="all">All</option>';
            foreach($productEnv as $env) {
                $option .= '<option value=' . $env['name'] . '>'. ucfirst($env['name']) .'</option>';
            }
        } else {
            $option = '<option></option>';
        }
        echo $option;
    }
}

