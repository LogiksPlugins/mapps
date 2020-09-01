<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_service_search")) {

	if(!isset($_SESSION['SESS_PRIVILEGE_NAME'])) {
		return;
	}

	function _service_submitData() {
		//Setup Mapps Addon Data Fields
		if(isset($_POST['mapps'])) {
			$mapps = $_POST['mapps'];
			unset($_POST['mapps']);
		} else {
			$mapps = [];
		}
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$mapps['client_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$mapps['client_ip'] = $_SERVER['REMOTE_ADDR'];
		}
		$mapps['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

		$tables = _db()->get_tableList();

		//Define Addon Variables
		$_SESSION['SESS_PROFILE_ID'] = 0;
		if(in_array("profiletbl", $tables)) {
			$userid = $_SESSION['SESS_USER_ID'];
			$profileData = _db()->_selectQ("profiletbl", "id", ["blocked"=>"false","loginid"=>$userid])->_GET();
			if($profileData && isset($profileData[0])) {
				$_SESSION['SESS_PROFILE_ID'] = $profileData[0]['id'];
			}
		}

		if(!isset($mapps['formid'])) return "Sorry, form could not defined";

		//printArray([$mapps,$_POST,$_REQUEST]);

		loadModuleLib("forms","api");

		$formConfig = findForm($mapps['formid']);

		if(!$formConfig) return "Sorry, form could not be found";

		$formConfig['mode'] = "new";
		
		if(isset($mapps['updateid']) && strlen($mapps['updateid'])>0) {
			$formConfig['mode'] = "update";

			if(isset($formConfig['source']) && isset($formConfig['source']['where']) && isset($formConfig['source']['where'][0])) {
				$formConfig['data'][$formConfig['source']['where'][0]] = $mapps['updateid'];
			} else {
				$formConfig['data']['md5(id)'] = $mapps['updateid'];
			}
		}

		if(!isset($formConfig["forcefill"])) $formConfig["forcefill"] = [];

		handleFormPostUpload($formConfig);

		if(isset($formConfig['source']) && $formConfig['source']['type']) {
			if($formConfig['source']['type']=="sql") {
				$columns = _db()->get_columnList($formConfig['source']['table']);

				$addonCols = array_intersect($columns,array_keys($mapps));

				foreach ($addonCols as $key) {
					$formConfig["forcefill"][$key] = $mapps[$key];
				}
			}
		}
		
		$_REQUEST["action"]="submit";
		$_REQUEST['formid'] = md5(time().rand());

		$_SESSION['FORM'][$_REQUEST['formid']] = $formConfig;

		ob_start();
		loadModuleLib("forms","service");

		$formMsg = ob_get_contents();
		ob_end_clean();

		ob_clean();
		$fMsg = current(explode("<script", trim($formMsg)));

		if($fMsg == "MSG:Submitted/Updated Successfully") {
			return "success";
		} else {
			return "Sorry, failed to submit your data"."-{$fMsg}";
		}
	}

	function _service_search() {
		if(!isset($_REQUEST['q']) || strlen($_REQUEST['q'])<=1) {
	        return [];
	    }
	    if(!isset($_REQUEST['type']) || strlen($_REQUEST['type'])<=1) {
	        return [];
	    }

	    if(isset($_REQUEST['p'])) $page=$_REQUEST['p'];
		elseif(isset($_REQUEST['page'])) $page=$_REQUEST['page'];
		else $page=0;

	    $searchFile = APPROOT."misc/search/{$_REQUEST['type']}.json";
	    if(!file_exists($searchFile)) {
	    	return "Search Type Not Supported";
	    }
	    $jsonConfig = json_decode(file_get_contents($searchFile),true);
	    if(!$jsonConfig || $jsonConfig == null || !isset($jsonConfig['source'])) {
	    	return "Search Config Is Corrupted";
	    }

	    if(!isset($jsonConfig['source']['scols'])) $jsonConfig['source']['scols'] = $jsonConfig['source']['cols'];

		if(!isset($jsonConfig['DEBUG'])) {
	    	$jsonConfig['DEBUG'] = false;
	    }
	    if(!isset($jsonConfig['source']['sort'])) $jsonConfig['source']['sort'] = "created_at desc";
	    if(!isset($jsonConfig['source']['limit'])) $jsonConfig['source']['limit'] = 20;

	    if(isset($_REQUEST['l'])) $limit=$_REQUEST['l'];
		elseif(isset($_REQUEST['limit'])) $limit=$_REQUEST['limit'];
		else $limit=$jsonConfig['source']['limit'];


	    $searchResponse = [];
	    switch ($jsonConfig['source']['type']) {
	    	case 'sql':
	    		$searchResponse=getMAPPSSearchDBData($_REQUEST['q'], $jsonConfig['source']['table'], $jsonConfig['source']['cols'], $jsonConfig['source']['where'], $jsonConfig['source']['scols'], 
						$jsonConfig['source']['sort'], $page, $limit, $jsonConfig['DEBUG']);

	    		//printArray([$_POST,$searchResponse]);exit();
	    		break;
	    	default:
	    		return "Search Source Not Supported";
	    		break;
	    }
	    if(!isset($jsonConfig['hiddencols'])) $jsonConfig['hiddencols'] = false;

	    foreach ($searchResponse as $rowKey => $row) {
    		$searchResponse[$rowKey]['buttons'] = [];

    		if(isset($jsonConfig['unilinks']) && is_array($jsonConfig['unilinks'])) {
	    		foreach ($jsonConfig['unilinks'] as $key => $value) {
	    			if(isset($row[$key]) && strlen($row[$key])>0) {
	    				$searchResponse[$rowKey]['buttons'][] = [
	    					"type"=>"unilink",
	    					"link"=>"infoview/{$value}/{$row[$key]}",
	    					"title"=>$value
	    				];
	    			}
	    		}
	    	}

	    	if(isset($jsonConfig['buttons']) && is_array($jsonConfig['buttons'])) {
		    	$searchResponse[$rowKey]['buttons'] = array_merge($searchResponse[$rowKey]['buttons'], $jsonConfig['buttons']);
		    }

		    foreach ($searchResponse[$rowKey]['buttons'] as $keyFunc => $btnConfig) {
		    	unset($searchResponse[$rowKey]['buttons'][$keyFunc]);
		    	$searchResponse[$rowKey]['buttons'][processFuncKey($keyFunc,$row)] = $btnConfig;
		    }

		    if($jsonConfig['hiddencols']) {
		    	foreach ($jsonConfig['hiddencols'] as $key) {
		    		if(isset($searchResponse[$rowKey][$key])) unset($searchResponse[$rowKey][$key]);
		    	}
		    }
    	}
    	
		return $searchResponse;
	}

	function _service_reportData() {
		if(!isset($_REQUEST['refid'])) {
			return "Source not defined";
		}

		if($_REQUEST['refid'] == "uitest") {
			return uitestReportsData();
		}

		loadModuleLib("reports","api");

		$reportConfig = findReport($_REQUEST['refid']);

		if(!($reportConfig && isset($reportConfig['cards']) && isset($reportConfig['cards']['colmap']))) {
			return "Report Not Found";
		}

		$ruleSet = [
			"row_class"=>[],
			"col_class"=>[],
		];
		if(isset($reportConfig['rules']) && is_array($reportConfig['rules'])) {
			$ruleSet = array_merge($ruleSet,$reportConfig['rules']);
		}
		
		$dbData = _db()->queryBuilder()->fromArray($reportConfig['source'],_db());

		if(isset($_GET['limit'])) $limit = $_GET['limit'];
		else $limit = 20;
		if(isset($_GET['page'])) $page = $_GET['page'];
		else $page = 0;

		$index = $page*$limit;
		$dbData->_limit($limit, $index);

		$dbData = $dbData->_GET();
		if(!$dbData) $dbData = [];

		$reportConfig['processed'] = [];
		foreach($reportConfig['datagrid'] as $a => $row) {
			if(!isset($row['alias'])) {
				$a1 = explode(".", $a);
				if(!isset($a1[1])) $a1[1] = $a1[0];
				$row['alias'] = $a1[1];
			}
			$reportConfig['processed'][$row['alias']] = $row;
		}

		if(!isset($reportConfig['buttons'])) $reportConfig['buttons'] = [];

		$fData = [];$resultSet = [];
		foreach($dbData as $a=>$row) {
			if(!isset($row['hashid'])) {
				if(isset($row['id'])) {
					$row['hashid'] = md5($row['id']);
					$dbData[$a]["hashid"] = md5($row['id']);
				}
			}

			$fData[$a] = [];
			foreach ($reportConfig['processed'] as $key => $colRules) {
				if(isset($dbData[$a][$key])) {
					if(!isset($colRules['formatter'])) $colRules['formatter'] = "text";

					//$key,$value,$type="text",$hidden=false,$record=[], $ruleSet = []
					$fData[$a][$key] = getReportColumn($key,$row[$key],$colRules['formatter'],false,[],$ruleSet);
				}
			}

			foreach($reportConfig['cards']['colmap'] as $k=>$v) {
				if(isset($dbData[$a]['hashid'])) {
					$resultSet[$a]['hashid'] = $dbData[$a]['hashid'];
				}
				$resultSet[$a]['reportid'] = $_REQUEST['refid'];

				if(isset($fData[$a][$v])) {
					$resultSet[$a][$k] = $fData[$a][$v];
				}
				$resultSet[$a]['buttons'] = [];
				if(isset($reportConfig['buttons'])) {
					foreach ($reportConfig['buttons'] as $keyFunc => $btnConfig) {
						$resultSet[$a]['buttons'][processFuncKey($keyFunc,$row)] = $btnConfig;
					}
				}
				
				$resultSet[$a]['actions'] = [];
				if(isset($reportConfig['actions'])) {
					foreach ($reportConfig['actions'] as $keyFunc => $btnConfig) {
						$resultSet[$a]['actions'][processFuncKey($keyFunc,$row)] = $btnConfig;
					}
				}
				if(isset($reportConfig['cards']['unilink'])) {
					$resultSet[$a]['link_url'] = processFuncKey($reportConfig['cards']['unilink'],$row);
				} else {
					$resultSet[$a]['link_url'] = "#";
				}
			}
		}

		return $resultSet;
	}
	
	function _service_formData() {
		if(!isset($_GET['src'])) return [];
		if(!isset($_GET['refhash'])) return [];

		loadModuleLib("forms","api");

		$formConfig=findForm($_GET['src']);

		if(!is_array($formConfig) || count($formConfig)<=2) {
			return [];
		}

		$formData = [];
		switch ($formConfig['source']['type']) {
			case 'sql':
				$tbl = $formConfig['source']['table'];
				$cols = array_keys($formConfig['fields']);
				$where = array_flip($formConfig['source']['where']);
				foreach($where as $a=>$b) {
					if($a == "md5(id)") $b = "#refhash#";
					$where[$a] = _replace($b);
				}
				$formData = _db()->_selectQ($tbl, $cols, $where)->_GET();
				break;
			
			case 'php':
				$formData = false;
				if(isset($formConfig['source']['file'])) {
					if(file_exists($formConfig['source']['file'])) {
						$formData = include_once $formConfig['source']['file'];
					} elseif(file_exists(APPROOT.$formConfig['source']['file'])) {
						$formData = include_once APPROOT.$formConfig['source']['file'];
					}
				}
				break;
			default:
				return [];
		}
		if(!$formData) $formData = [];
		else $formData = $formData[0];

		foreach($formConfig['fields'] as $field => $fieldConfig) {
			//Alter data as per field type
		}
		
		return $formData;
	}

	function _service_datalist() {
		if(!isset($_REQUEST['groupid']) || strlen($_REQUEST['groupid'])<=1) {
	        return [];
	    }
	    $sqlObj = _db()->_selectQ(_dbTable("lists","app"),
	        "id,groupuid,title,value,class,sortorder",["blocked"=>"false","groupid"=>$_REQUEST['groupid'],"guid"=>$_SESSION['SESS_GUID']]);//,site,privilege
	    if(isset($_SESSION['SESS_PRIVILEGE_HASH'])) {
	        $sqlObj=$sqlObj->_whereMulti([["privilege","*"],["privilege",[$_SESSION['SESS_PRIVILEGE_HASH'],"FIND"]]],"AND","OR");
	    } else {
	        $sqlObj=$sqlObj->_where(array("privilege"=>"*"));
	    }
	    
	    $sqlObj=$sqlObj->_whereMulti([["site","*"],["site",[$_REQUEST['site'],"FIND"]]],"AND","OR");

	    $sqlObj=$sqlObj->_orderby('sortorder,title');

	    $sqlData = $sqlObj->_GET();

	    if(!$sqlData) $sqlData = [];

	    return $sqlData;
	}
}
?>