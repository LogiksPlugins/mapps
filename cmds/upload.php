<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_service_upload")) {

	if(!isset($_SESSION['SESS_PRIVILEGE_NAME'])) {
		return;
	}

	function _service_upload() {
		$storageDir = APPROOT."usermedia/uploadImages/";
		if(!is_dir($storageDir)) {
			mkdir($storageDir, 0777, true);
		}
		// printArray([$_POST, $_FILES]);

		if(isset($_POST['fname']) && strlen($_POST['fname'])>0) {
	        $fname = $_POST['fname'];
  		} else {
	        $fname=md5(time().rand());
      	}

		if(isset($_FILES) && count($_FILES)>0) {

		} elseif(isset($_POST['image_base64data'])) {
      		$finalFile = save_base64_image($_POST['image_base64data'], $fname, $storageDir);

      		return [
      			"file"=>$finalFile,
      			"fullpath"=>APPROOT."usermedia/uploadImages/{$finalFile}",
      			"url"=>WEBAPPROOT."usermedia/uploadImages/{$finalFile}"
      		];
      	}

		return [
			"status"=> false,
			"msg"=>"Error uploading",
		];
	}
}
?>