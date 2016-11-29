<?php
class Control extends MY_Controller{
	function Start($server,$worker){
		$this->_request($server,'startProcess',array($worker,1));
		$this->FilterRedirect();
	}
	function Startall($server){
		$this->_request($server,'startAllProcesses',array(1));
		$this->FilterRedirect();
	}
	function Stop($server,$worker){
		$this->_request($server,'stopProcess',array($worker,1));
		$this->FilterRedirect();
	}
	function Stopall($server){
		$this->_request($server,'stopAllProcesses',array(1));
		$this->FilterRedirect();
	}
	function Restartall($server){
		$this->_request($server,'stopAllProcesses',array(1));
		sleep(2);
		$this->_request($server,'startAllProcesses',array(1));
		$this->FilterRedirect();
	}
	function Clear($server,$worker){
		$this->_request($server,'clearProcessLogs',array($worker));
		$this->FilterRedirect();
	}
    // Added custom function to preserve filters at the time of redirecting.
    function FilterRedirect() {
		$this->load->library('user_agent');
        redirect($this->agent->referrer());
	}
}
