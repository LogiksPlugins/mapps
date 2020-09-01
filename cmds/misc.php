<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_service_feeds")) {
	
	function _service_feedback() {
		return "Sorry, can not record your feedback now";
	}

	function _service_cmd() {
		if(!is_dir(MAPPS_RESOURCE_DIR."cmds/")) {
	      return "Resource Does Not Exist";
	    }
	    if(isset($_POST['cmd'])) {
	      return "{$_POST['cmd']} Not Found";
	    } else {
	      return "No Command Defined";
	    }
	}

	function _service_uri() {
		if(isset($_REQUEST['uri'])) {
	      echo "{$_REQUEST['uri']} Not Found";
	    } else {
	      echo "URI Not Found";
	    }
	}

	function _service_unilink() {
		if(isset($_REQUEST['unilink'])) {
	      echo "{$_REQUEST['unilink']} Not Found";
	    } else {
	      echo "UNILink Not Found";
	    }
	}
	
	function _service_notifications() {
		return [];
	}

	//messages from notifyMatrix
	function _service_msgs() {
		return [];
	}

	//logging
	function _service_errorReport() {
		return "OK";
	}
}
?>