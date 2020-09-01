<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_service_verify")) {

	if(!isset($_SESSION['SESS_PRIVILEGE_NAME'])) {
		return;
	}

	function _service_verify() {
		return [
	        "SITENAME"=>SITENAME,
	        "SERVICE_SESSION"=>checkServiceSession(true,false),
	        "SERVICE_ACCESS"=>in_array(SITENAME,$_SESSION['SESS_ACCESS_SITES']),
	        "USERID"=>$_SESSION['SESS_USER_ID'],
	      ];
	}

	function _service_me() {
		return array_filter($_SESSION, "sessUserInfo",ARRAY_FILTER_USE_KEY);
	}

	function _service_team() {
		return [];
	}

	function _service_sitelist() {
		return $_SESSION['SESS_ACCESS_SITES'];
	}

	function _service_checkaccess() {
		if(!isset($_REQUEST['src']) || strlen($_REQUEST['src'])<=1) {
	      $_REQUEST['src'] = SITENAME;
	    }    
	    return [
	      "src"=>SITENAME,
	      "status"=>in_array($_REQUEST['src'],$_SESSION['SESS_ACCESS_SITES'])
	  	];
	}
}
?>