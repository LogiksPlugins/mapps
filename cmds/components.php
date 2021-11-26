<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_service_menu")) {

	if(!isset($_SESSION['SESS_PRIVILEGE_NAME'])) {
		return;
	}

	function _service_menu() {
		if(!isset($_REQUEST['menuid']) || strlen($_REQUEST['menuid'])<=1) {
	        $_REQUEST['menuid'] = "mapps";
	    }
	    //loadModuleLib("navigator","api");
	    //$menuTree = generateNavigationFromDB($_REQUEST['menuid'],"links");

	    $menuData = _db(true)->_selectQ("mapps_menu","*", [
	    			"menuid"=>$_REQUEST['menuid'],
	    			"mapps_id"=>$_SESSION['SESS_MAPPS']['app_id'],
					"blocked"=>"false",
					// "privilege"=>[["*",$_SESSION['SESS_PRIVILEGE_NAME']],"IN"],
				])
	    		->_whereRAW("(privilege='*' OR FIND_IN_SET('{$_SESSION['SESS_PRIVILEGE_NAME']}',privilege))")
	    		->_orderBy("weight DESC")->_GET();

	    if(!$menuData) $menuData = [];

	    $menuTree = [];
	    foreach($menuData as $row) {
	    	if($row['category']==null || strlen($row['category'])<=0) $row['category'] = "/";

	    	if(!isset($menuTree[$row['category']])) $menuTree[$row['category']] = [];
	    	
	    	$menuTree[$row['category']][] = array_merge(getDefaultMenu(), [
	    			"title"=>$row['title'],
					"category"=>$row['category'],
					"menugroup"=>$row['menugroup'],
					"class"=>$row['class'],
					"target"=>$row['target'],
					"link_url"=>$row['link_url'],
					"tips"=>$row['tips'],
					"iconpath"=>$row['iconpath'],
	    		]);
	    }
	    
	    return $menuTree;
	}

	function _service_floatbutton() {
		return [];
	}

	function _service_panel() {
		if(!isset($_REQUEST['src'])) {
			if(isset($_REQUEST['panel'])) {
				$_REQUEST['src'] = $_REQUEST['panel'];
			} else {
				return "Source not defined";
			}
		}

		if($_REQUEST['src']=="uitest") {
			return uitestPanel();
		}

		$panelID = $_REQUEST['src'];
		$panelInfo = _db(true)->_selectQ("mapps_panels","*",[
					"mapps_id"=>$_SESSION['SESS_MAPPS']['app_id'],
					"panel_code"=>$panelID,
					"blocked"=>"false",
				])->_GET();
		if(!$panelInfo) {
			return "Panel not Found";
		}

		$panelInfo = $panelInfo[0];
		
		if($panelInfo['config']==null || strlen($panelInfo['config'])<=0) {
			$panelInfo['config'] = "{}";
		}
		$panelInfo['config'] = json_decode($panelInfo['config'], true);
		if($panelInfo['config']==null) $panelInfo['config'] = [];

		//All Blocks for Panel
		$panelBlocks = _db(true)->_selectQ("mapps_components","*",[
					"mapps_id"=>$_SESSION['SESS_MAPPS']['app_id'],
					"panel_code"=>$panelID,
					"blocked"=>"false",
				])->_orderby("sort_order DESC")->_GET();

		if(!$panelBlocks) $panelBlocks = [];

		$panelBlockList = [];
		foreach($panelBlocks as $row) {
			if($row['comp_params']==null || strlen($row['comp_params'])<=0) $row['comp_params'] = "{}";
			$row['comp_params'] = json_decode($row['comp_params'], true);
			if($row['comp_params']==null) $row['comp_params'] = [];

			$panelBlockList[_slugify($row['comp_name'])] = array_merge([
					"comp_name"=>$row['comp_name'],
					"src_type"=>$row['src_type'],
					"src_path"=>$row['src_path'],
				],$row['comp_params']);
		}

		//All Menu/Buttons for Panel
		$menuList = _db(true)->_selectQ("mapps_menu","menuid,title,category,menugroup,class,target,link_url,iconpath,tips,weight",[
					"mapps_id"=>$_SESSION['SESS_MAPPS']['app_id'],
					"menuid"=>[[
							"panel_header_{$panelID}",
							"panel_footer_{$panelID}",
							"panel_float_{$panelID}"],"IN"],
					"blocked"=>"false",
				])
				->_whereRAW("(privilege='*' OR FIND_IN_SET('{$_SESSION['SESS_PRIVILEGE_NAME']}',privilege))")
	    		->_orderBy("weight DESC")
	    		->_GET();

		if(!$menuList) $menuList = [];

		$headerMenu = [];
		$footerMenu = [];
		$floatButtons = [];
		foreach($menuList as $row) {
			if($row['category']==null || strlen($row['category'])<=0) $row['category'] = "/";

	    	switch ($row['menuid']) {
				case "panel_header_{$panelID}":
					if(!isset($headerMenu[$row['category']])) $headerMenu[$row['category']] = [];
					$headerMenu[$row['category']][] = $row;
					break;
				case "panel_footer_{$panelID}":
					if(!isset($footerMenu[$row['category']])) $footerMenu[$row['category']] = [];
					$footerMenu[$row['category']][] = $row;
					break;
				case "panel_float_{$panelID}":
					if(!isset($floatButtons[$row['category']])) $floatButtons[$row['category']] = [];
					$floatButtons[$row['category']][] = $row;
					break;
			}
		}
		

	    return array_merge(getDefaultPanelParams(), [
	    		"panel"=>$panelID,
	    		"title"=>$panelInfo['title'],
	    		"config"=>$panelInfo['config'],
	    		
	          	"blocks"=>$panelBlockList,

	          	"banner"=>[],

	          	"header"=>$headerMenu,
	          	"footer"=>$footerMenu,
	          	"floatbutton"=>$floatButtons,
	        ]);
	}

	function _service_component() {
		if(!is_dir(MAPPS_RESOURCE_DIR)) {
	      return "Resource Does Not Exist";
	    }
	    if(isset($_REQUEST['src']) && strlen($_REQUEST['src'])>1) {

	    	if($_REQUEST['src']=="uitest") {
				echo uitestComponent();
			} else {
				$f = MAPPS_RESOURCE_DIR."comps/{$_REQUEST['src']}.php";
			    if(file_exists($f)) {
			    	$box = explode("/", $_REQUEST['src']);
			    	if(count($box)>0) {
			    		$containerClass = current($box);
			    		$containerClass = _slugify($containerClass);
			    		$containerID = _slugify($_REQUEST['src']);
						echo "<div class='mapps_component {$containerClass}' refid='{$containerID}'>";
			    		include_once $f;
			    		echo "</div>";
			    	} else {
			    		echo "<div class='mapps_component'>";
			    		include_once $f;
			    		echo "</div>";
			    	}
			    } else {
			      	echo "Resource Does Not Exist";
			    }
			}
	    } else {
	      return "No Command Defined";
	    }
	}

	function _service_forms() {
		if(!isset($_REQUEST['src'])) {
			return "Source not defined";
		}

		loadModuleLib("forms","api");

		if($_REQUEST['src']=="uitest") {
			$formConfig = uitestForms();
		} else {
			$formConfig = findForm($_REQUEST['src']);	
		}

		printFormMobile("new",$formConfig);
	}

	function _service_reports() {
		if(!isset($_REQUEST['src'])) {
			return "Source not defined";
		}

		loadModuleLib("reports","api");

		if($_REQUEST['src']=="uitest") {
			$reportConfig = uitestReports();
		} else {
			$reportConfig = findReport($_REQUEST['src']);
		}

		if($reportConfig && isset($reportConfig['cards']) && isset($reportConfig['cards']['colmap'])) {
			$reportID = md5($_REQUEST['src'].time());

			unset($reportConfig['source']);

			return [
					"refid"=>$_REQUEST['src'],
					//"refid"=>$reportID,
					"report"=>$reportConfig
				];
		} else return false;
	}

	function _service_infoview() {
		if(!isset($_REQUEST['src'])) {
			return "Source not defined";
		}
		if(!isset($_REQUEST['refid'])) {
			return "Details Reference not found";
		}

		loadModuleLib("infoview","api");

		if($_REQUEST['src']=="uitest") {
			$infoviewConfig = uitestInfoview();
		} else {
			$infoviewConfig = findInfoview($_REQUEST['src']);
		}

		if(!$infoviewConfig) {
			return "Details Panel Not Found";
		}

		$_SERVER['SERVICE_URI'] = "a/{$_REQUEST['src']}/{$_GET['refid']}/subcat/subtype/code";

		$_ENV['INFOVIEW-REFHASH']=$_GET['refid'];

		if(isset($infoviewConfig['infoview']) && isset($infoviewConfig['infoview']['groups'])) {
			foreach ($infoviewConfig['infoview']['groups'] as $key => $tabInfo) {
				if(isset($tabInfo['mapps']) && !$tabInfo['mapps']) {
					unset($infoviewConfig['infoview']['groups'][$key]);
				}
			}
		}
		if(isset($infoviewConfig['buttons'])) {
			foreach ($infoviewConfig['buttons'] as $key => $btnInfo) {
				if(isset($btnInfo['mapps']) && !$btnInfo['mapps']) {
					unset($infoviewConfig['buttons'][$key]);
				}
			}
		}
		if(is_numeric($_GET['refid'])) $_GET['refid'] = md5($_GET['refid']);
		
		printInfoviewMobile($infoviewConfig,$infoviewConfig['dbkey'],["md5(id)"=>$_GET['refid']]);
	}

	function _service_infovisual() {
		
	}
}
?>