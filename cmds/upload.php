<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_service_upload")) {

	if(!isset($_SESSION['SESS_PRIVILEGE_NAME'])) {
		return;
	}

	function _service_upload() {
		printArray([$_POST,$_FILES]);
		return [];
	}
}
?>