<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_service_appinfo")) {

	function _service_appinfo() {
		$mappData = _db(true)->_selectQ("mapps_tbl","app_name,app_site,build,default_policies",[
                    "blocked"=>"false",
                    "published"=>"true",
                    "id"=>$_SESSION['SESS_MAPPS']['app_id'],
                ])->_GET();
		if(!$mappData) {
			$appInfo = $_SESSION['SESS_MAPPS'];
		
			unset($appInfo['app_key']);
			unset($appInfo['app_secret']);
			unset($appInfo['app_id']);

			return $appInfo;
		}
		$mappData[0]['default_policies'] = json_decode($mappData[0]['default_policies'], true);

		return $mappData[0];
	}

	function _service_settings() {
		$mappSettings = _db(true)->_selectQ("mapps_settings","*",[
                    "blocked"=>"false",
                    "mapps_id"=>$_SESSION['SESS_MAPPS']['app_id'],
                ])->_GET();

		if($mappSettings) {
			$mappSettings = $mappSettings[0];
			try {
				$mappSettings['options_json'] = json_decode($mappSettings['options_json'], true);

				if($mappSettings['options_json']==null) {
					$mappSettings['options_json'] = [];
				}
			} catch(Exception $e) {
				$mappSettings['options_json'] = [];
			}
			$mappSettings = $mappSettings['options_json'];
		} else {
			$mappSettings = [];
		}

		return $mappSettings;
	}

	function _service_lingbook() {
		$ling=Lingulizer::getInstance();
    	if($ling->lang==null) return [];
    	else return $ling->lang;
	}

	function _service_modulelist() {
		$fs = [];
	    $fs = scandir(APPROOT."plugins/modules");
	    $fs = array_slice($fs,2);
	    
	    $fs1 = scandir(ROOT."plugins/modules");
	    $fs1 = array_slice($fs1,2);
	    
	    $fs = array_merge($fs1,$fs);
	    
	    $fs = array_filter($fs, function($f) {
	      $f1 = substr($f,0,1);
	      return !($f1=="." || $f1=="~" || in_array($f1,["core"]));
	    });
	    
	    return $fs;
	}

	function _service_searchlist() {
		$fs = scandir(APPROOT."misc/search");
		if(!$fs) $fs = [];

		$fs = array_filter($fs, function($f) {
	      $f1 = substr($f,0,1);
	      return !($f1=="." || $f1=="~");
	    });

	    foreach ($fs as $key => $value) {
	    	$fs[$key] = str_replace(".json", "", $value);
	    }
	    

	    return $fs;
	}

	function _service_addonassets() {
		printGeneralAssets();
	}

	function _service_assetrecache() {
		return false;
	}
}
?>