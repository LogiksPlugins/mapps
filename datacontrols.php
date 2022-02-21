<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printFormMobile")) {

	function getReportColumn($key,$value,$type="text",$hidden=false,$record=[], $ruleSet = []) {
		switch($type) {
			case "photo":case "picture":case "media":
				if($value==null || strlen($value)<=0) return SiteLocation."media/images/noimg.png";
				$media = searchMedia($value);
				if($media) {
					return $media['url'];
				} else {
					return SiteLocation."media/images/noimg.png";
				}
			break;
			case "content":
				return $value;
			break;
			default:
				//formatReportColumn($key,$record[$key],$column['formatter'],$column['hidden'],$record,$ruleSet);
				$tempData = formatReportColumn($key,$value,$type, false, [], $ruleSet);
				return getTextBetweenTags($tempData,"td");
		}
		return "";
	}

	function printInfoviewMobile($formConfig,$dbKey=false,$whereCondition=[], $params=[]) {
		if(!is_array($formConfig)) $formConfig=findForm($formConfig);

		if(!is_array($formConfig) || count($formConfig)<=2) {
			trigger_logikserror("Corrupt form defination");
			return false;
		}

		if(isset($formConfig['policy']) && strlen($formConfig['policy'])>0) {
	      $allow=checkUserPolicy($formConfig['policy']);
	      if(!$allow) {
	        trigger_logikserror("Sorry, you are not allowed to access this form");
	        return false;
	      }
	    }

	    if($params==null) $params=[];
		$formConfig=array_replace_recursive($formConfig,$params);

		$formConfig['infoviewkey']=md5(session_id().time());
		
		if(!isset($formConfig['infoview'])) $formConfig['infoview']=[];
		
		$formConfig['infoviewcode']=md5($_SESSION['SESS_USER_ID'].$formConfig['sourcefile']);
		$formConfig['infoviewuid']=md5($formConfig['sourcefile']);

		$formConfig['dbkey']=$dbKey;

		if(!isset($formConfig['infoview']['template'])) {
			$formConfig['template']="tabbed";
		} else {
			$formConfig['template']=$formConfig['infoview']['template'];
		}
		
		if(!isset($formConfig['gotolink'])) {
			$formConfig['gotolink']="";
		}
		
		if(!isset($formConfig['config'])) {
			$formConfig['config']=[];
		}
		if(!isset($formConfig['buttons'])) {
			$formConfig['buttons']=[];
		}
		if(!isset($formConfig['secure'])) {
			$formConfig['secure']=true;
		}

		setConfig("INFOVIEWTABLE_SHOW_DISABLED_TABS","false");

		$fieldGroups=[];
		foreach ($formConfig['fields'] as $fieldKey => $fieldset) {
			if(!isset($fieldset['label'])) $fieldset['label']=_ling($fieldKey);
			if(!isset($fieldset['width'])) $fieldset['width']=6;
			if(!isset($fieldset['group'])) $fieldset['group']="default";
			
			$fieldset['group']=str_replace(" ","_",$fieldset['group']);

			$fieldset['fieldkey']=$fieldKey;

			if(!isset($fieldGroups[$fieldset['group']])) $fieldGroups[$fieldset['group']]=[];

			$formConfig['fields'][$fieldKey]=$fieldset;
			$fieldGroups[$fieldset['group']][]=$fieldset;
		}
		
		if(!isset($formConfig['actions'])) $formConfig['actions']=[];

		$formData=[];
		if(isset($formConfig['data']) && count($formConfig['data'])>0) {
			$formData=$formConfig['data'];
		}

		$source=$formConfig['source'];
		switch ($source['type']) {
			case 'sql':
				if(isset($formConfig['config']['GUID_LOCK']) && $formConfig['config']['GUID_LOCK']===true) {
					$whereCondition["guid"]=$_SESSION['SESS_GUID'];
				}
				
				$formConfig['fields'] = array_filter($formConfig['fields'], function($key){
										return strpos($key, '__') !== 0;
								}, ARRAY_FILTER_USE_KEY );
				
// 					printArray($formConfig['fields']);exit();
				$sqlCols=array_keys($formConfig['fields']);
				$sqlCols[]="id";
				
				$sql=_db($dbKey)->_selectQ($source['table'],$sqlCols,$whereCondition);
					// exit($sql->_SQL());
				//$data=$sql->_get();
				//echo $sql->_SQL();printArray([$formConfig['fields'],$whereCondition]);
				
				$res=_dbQuery($sql,$dbKey);
				if($res) {
					$data=_dbData($res,$dbKey);
					_dbFree($res,$dbKey);
					if(isset($data[0])) {
						$formData=$data[0];
						$formConfig['source']['where_auto']=$whereCondition;
					} else {
						$formData=[];
					}
				} else {
					trigger_logikserror(_db($dbKey)->get_error());
				}
				//printArray($data);exit($sql->_SQL());
				
			break;
			case 'php':
				$file=APPROOT.$source['file'];
				if(file_exists($file) && is_file($file)) {
					$formData=include_once($file);
				} else {
					trigger_error("Form Data Source File Not Found");
				}
			break;
		}
	
		$formData=processFormData($formData,$formConfig);
		if(isset($formData['id'])) {
			$_ENV['INFOVIEW-REFID']=$formData['id'];
		} else {
			$_ENV['INFOVIEW-REFID']=0;
		}
		$formConfig['data']=$formData;

		$formKey=$formConfig['infoviewkey'];
		$_SESSION['INFOVIEW'][$formKey]=$formConfig;
		$_ENV['FORMKEY']=$formKey;

		//Loading Form Template
		$templateArr=[
				$formConfig['template'],
				dirname(checkModule("infoview"))."/templates/{$formConfig['template']}.php"
			];

		$dcode=$_ENV['INFOVIEW-REFHASH'];
		$dtuid=$formKey;

		foreach ($templateArr as $f) {
			if(file_exists($f) && is_file($f)) {
				if(isset($formConfig['hooks']) && isset($formConfig['hooks']['preload'])) {
					if(isset($formConfig['hooks']['preload']['modules'])) {
						loadModules($formConfig['hooks']['preload']['modules']);
					}
					if(isset($formConfig['hooks']['preload']['api'])) {
						foreach ($formConfig['hooks']['preload']['api'] as $apiModule) {
							loadModuleLib($apiModule,'api');
						}
					}
					if(isset($formConfig['hooks']['preload']['helpers'])) {
						loadHelpers($formConfig['hooks']['preload']['helpers']);
					}
					if(isset($formConfig['hooks']['preload']['method'])) {
						if(!is_array($formConfig['hooks']['preload']['method'])) $formConfig['hooks']['preload']['method']=explode(",",$formConfig['hooks']['preload']['method']);
						foreach($formConfig['hooks']['preload']['method'] as $m) call_user_func($m,$formConfig);
					}
					if(isset($formConfig['hooks']['preload']['file'])) {
						if(!is_array($formConfig['hooks']['preload']['file'])) $formConfig['hooks']['preload']['file']=explode(",",$formConfig['hooks']['preload']['file']);
						foreach($formConfig['hooks']['preload']['file'] as $m) {
							if(file_exists($m)) include $m;
							elseif(file_exists(APPROOT.$m)) include APPROOT.$m;
						}
					}
				}
				
// 				printArray($formConfig);return;
				// include __DIR__."/vendors/autoload.php";
				// echo _css(["bootstrap.datetimepicker",'infoview']);
				// if(isset($formConfig['infoview']['style']) && strlen($formConfig['infoview']['style'])>0) {
				// 	echo _css(["infoview/{$formConfig['infoview']['style']}",$formConfig['infoview']['style']]);
				// }

				if(isset($_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['data'])) {
					foreach($_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['data'] as $a=>$b) {
						$_REQUEST[$a]=$b;
					}
				}
				if(isset($formConfig['sourcefile'])) {
					printAddonAssets($formConfig['sourcefile'],"forms");
					printAddonAssets($formConfig['sourcefile'],"infoview");
				}
				include $f;
				
				// echo _js(["bootstrap.datetimepicker",'infoview']);
				// if(isset($formConfig['infoview']['script']) && strlen($formConfig['infoview']['script'])>0) {
				// 	echo _js(["infoview/{$formConfig['infoview']['script']}",$formConfig['infoview']['script']]);
				// }
			}
		}
	}

	function printInfovisualMobile($reportConfig,$dbKey=false,$params=[]) {
	}

	function printViewsMobile($reportConfig,$dbKey=false,$params=[]) {
	}

	function printFormMobile($mode,$formConfig,$dbKey="app",$whereCondition=false,$params=[]) {
		if(!is_array($formConfig)) $formConfig=findForm($formConfig);

		if(!is_array($formConfig) || count($formConfig)<=2) {
			trigger_logikserror("Corrupt form defination");
			return false;
		}

		if(isset($formConfig['policy']) && strlen($formConfig['policy'])>0) {
	      $allow=checkUserPolicy($formConfig['policy']);
	      if(!$allow) {
	        trigger_logikserror("Sorry, you are not allowed to access this form");
	        return false;
	      }
	    }

		if($params==null) $params=[];
		$formConfig=array_replace_recursive($formConfig,$params);
		
		if(isset($_SESSION['FORM_CONFIG']) && is_array($_SESSION['FORM_CONFIG'])) {
			if(isset($_SESSION['FORM_CONFIG'][$mode])) {
				$globalParams = $_SESSION['FORM_CONFIG'][$mode];
			} else {
				if(isset($_SESSION['FORM_CONFIG']['edit'])) unset($_SESSION['FORM_CONFIG']['edit']);
				if(isset($_SESSION['FORM_CONFIG']['new'])) unset($_SESSION['FORM_CONFIG']['new']);
				
				$globalParams = $_SESSION['FORM_CONFIG'];
			}
			// $formConfig=array_replace_recursive($formConfig,$globalParams);
			$formConfig=array_merge_recursive($formConfig,$globalParams);
		}

		if(!isset($formConfig['formkey'])) $formConfig['formkey']=md5(session_id().time());

		$formConfig['formcode']=md5($_SESSION['SESS_USER_ID'].$formConfig['sourcefile']);
		$formConfig['formuid']=md5($formConfig['sourcefile']);

		$formConfig['dbkey']=$dbKey;

		if(!isset($formConfig['template'])) {
			$formConfig['template']="simple";
		}

		if(!isset($formConfig['gotolink'])) {
			$formConfig['gotolink']="";
		}
		if(!isset($formConfig['reloadlink'])) {
			$formConfig['reloadlink']="";
		}
		if(!isset($formConfig['cancellink'])) {
			$formConfig['cancellink']="";
		}

		if(!isset($formConfig['config'])) {
			$formConfig['config']=[];
		}

		$fieldGroups=[];
		foreach ($formConfig['fields'] as $fieldKey => $fieldset) {
			if(!isset($fieldset['label'])) $fieldset['label']=_ling($fieldKey);
			if(!isset($fieldset['width'])) $fieldset['width']=6;
			if(!isset($fieldset['group'])) $fieldset['group']="default";

			$fieldset['group']=str_replace(" ","_",$fieldset['group']);

			$fieldset['fieldkey']=$fieldKey;

			if(!isset($fieldGroups[$fieldset['group']])) $fieldGroups[$fieldset['group']]=[];

			$formConfig['fields'][$fieldKey]=$fieldset;
			$fieldGroups[$fieldset['group']][]=$fieldset;
		}

		if(!isset($formConfig['actions'])) $formConfig['actions']=[];

		$formConfig['actions']['cancel']=[
								"type"=>"button",
								"label"=>"Cancel",
								//"icon"=>"<i class='fa fa-angle-left form-icon right'></i>",
								"class"=>"pull-left float-left btn btn-danger"
							];
		
		switch ($mode) {
			case 'update':
			case 'edit':
				$formConfig['actions']['update']=[
							"type"=>"submit",
							"label"=>"Update",
							//"icon"=>"<i class='fa fa-save form-icon right'></i>",
							"class"=>"pull-right float-right btn btn-success"
						];
				break;

			case 'insert':
			case 'new':
			default:
// 				$formConfig['actions']["submitnew"]=[
// 							"type"=>"submitnew",
// 							"label"=>"Submit & New",
// 							"icon"=>"<i class='fa fa-save form-icon right'></i>"
// 						];
				$formConfig['actions']["submit"]=[
							"type"=>"submit",
							"label"=>"Submit",
							//"icon"=>"<i class='fa fa-save form-icon right'></i>"
							"class"=>"pull-right float-right btn btn-success"
						];
				break;
		}

		$formData=[];
		if(isset($formConfig['data']) && count($formConfig['data'])>0) {
			$formData=$formConfig['data'];
		}
		if($mode=="update" || $mode=="edit") {
			$source=$formConfig['source'];
			switch ($source['type']) {
				case 'sql':
					$whereCondition = [];
					if(isset($formConfig['config']['GUID_LOCK']) && $formConfig['config']['GUID_LOCK']===true) {
						$whereCondition["guid"]=$_SESSION['SESS_GUID'];
					}

					$tempFields = array_filter($formConfig['fields'], function($value,$key){
											return ((strpos($key, '__') !== 0) && !(isset($value['nodb']) && $value['nodb']));
									}, ARRAY_FILTER_USE_BOTH );

	// 					printArray($formConfig['fields']);exit();

					$sql=_db($dbKey)->_selectQ($source['table'],array_keys($tempFields),$whereCondition);
	// 					echo $sql->_SQL();exit();
					//echo $sql->_SQL();printArray([$formConfig['fields'],$whereCondition]);

					$res=_dbQuery($sql,$dbKey);
					if($res) {
						$data=_dbData($res,$dbKey);
						_dbFree($res,$dbKey);
						if(isset($data[0])) {
							$formData=$data[0];
							$formConfig['source']['where_auto']=$whereCondition;
						} else {
							$formData=[];
						}
					} else {
						trigger_logikserror(_db($dbKey)->get_error());
					}
					//printArray($data);exit($sql->_SQL());

				break;
				case 'php':
					$file=APPROOT.$source['file'];
					if(file_exists($file) && is_file($file)) {
						$formData=include_once($file);
					} else {
						trigger_error("Form Data Source File Not Found");
					}
				break;
			}
		}

		$formConfig['data']=$formData;


		$formConfig['mode']=$mode;
		if($formConfig['mode']==null || strlen($formConfig['mode'])<=0) {
			$formConfig['mode']="new";
		}

		if(!isset($formConfig['simpleform'])) $formConfig['simpleform'] = false;

		if($formConfig['mode']!="new") {
			$formConfig['simpleform'] = false;
			$formConfig['disable_simpleform'] = true;
		}

		$formConfig['simpleform'] = false;
		$formConfig['disable_simpleform'] = true;

		$formKey=$formConfig['formkey'];
		$_SESSION['FORM'][$formKey]=$formConfig;
		$_ENV['FORMKEY']=$formKey;

		//Loading Form Template
		$templateArr=[
				$formConfig['template'],
				dirname(checkModule("forms"))."/templates/{$formConfig['template']}.php"
			];
		
	// 		printArray($formConfig);return;
		foreach ($templateArr as $f) {
			if(file_exists($f) && is_file($f)) {
				processFormHook("preLoad",["config"=>$formConfig,"mode"=>$formConfig['mode']]);

				// if(isset($formConfig['style']) && strlen($formConfig['style'])>0) {
				// 	echo _css(["forms/{$formConfig['style']}",$formConfig['style']]);
				// }
				
				if(isset($_SESSION['FORM'][$_ENV['FORMKEY']]) && isset($_SESSION['FORM'][$_ENV['FORMKEY']]['data'])) {
					$formConfig['data'] = $_SESSION['FORM'][$_ENV['FORMKEY']]['data'];
					$formData = $formConfig['data'];
				}
				
				if(isset($formConfig['sourcefile'])) {
					printAddonAssets($formConfig['sourcefile'],"forms");
				}
				include $f;
				
				// if(isset($formConfig['script']) && strlen($formConfig['script'])>0) {
				// 	echo _js(["forms/{$formConfig['script']}",$formConfig['script']]);
				// }
				return true;
			}
		}
		trigger_logikserror("Form Template Not Found",null,404);
	}

	function getMAPPSSearchDBData($q, $tables, $cols, $staticWhere=["blocked"=>"false"], $scols=null, $sort="created_at desc",$page=0, $limit=50, $debug = false) {
	    $data=[];

	    if(!is_array($cols)) $cols=explode(",",$cols);
	    
	    if($staticWhere && is_array($staticWhere)) {
	      foreach($staticWhere as $a=>$b) {
	        unset($staticWhere[$a]);
	        if(!is_array($b)) {
	          $staticWhere[_replace($a)] = _replace($b);
	        } else {
	          $staticWhere[_replace($a)] = $b;
	        }
	      }
	    }
	    $primeTable = current(explode(" ", current(explode(" ", $tables))));
	    $sqlQuery=_db()->_selectQ($tables,$cols,$staticWhere);

	    $where2=[];

	    if($scols==null || strlen($scols)<=0) {
	        $scols=$cols;
	    } else {
	    if(!is_array($scols)) $scols=explode(",",$scols);
	    }

	    if(is_numeric($q)) {
	      foreach($scols as $a) {
	          $b=current(explode(" ",$a));
	          $where2[$b]=$q;
	        }
	    } 
	    elseif(substr($q,0,1)=="#") {
	        $where2[$scols[0]]=substr($q,1);
	    }elseif(substr($q,0,1)=="@") {

	    } elseif(strlen($q)==32) {
	        $where2["md5({$scols[0]})"]=substr($q,1);
	    } elseif(strpos($q,'::')>1) {
	        $q=explode("::",$q);
	        if(count($q)>1) {
	        	if(strpos($q[0],".")===false) {
	        		$where2[$primeTable.".".$q[0]]=$q[1];
	        	} else {
	        		$where2[$q[0]]=$q[1];
	        	}
	        } else {
	          $q1=str_replace("%","",$q);
	          $q1=str_replace(" ","%",$q1);
	          foreach($scols as $a) {
	            $b=current(explode(" ",$a));
	            $where2[$b]=["VALUE"=>$q1,"OP"=>"like"];
	            }
	        }
	    } else {
	        $q1=str_replace("%","",$q);
	        $q1=str_replace(" ","%",$q1);
	        foreach($scols as $a) {
	          $b=current(explode(" ",$a));
	          $where2[$b]=["VALUE"=>$q1,"OP"=>"like"];
	        }
	    }

	  $sqlQuery=$sqlQuery->_where($where2,"AND","OR");


	  $index=$page*$limit;

	  $sqlQuery=$sqlQuery->_limit($limit,$index);
	  $sqlQuery=$sqlQuery->_orderby($sort);
	  
	  if($debug) {
	  	echo "<h5 class='searchMsg'>".$sqlQuery->_SQL()."</h5>";
		exit();  
	  }
	  
	  $data=$sqlQuery->_GET();

	  if(!$data) $data = [];

	  return $data;
	}

	function handleFormPostUpload($formConfig) {
		//move files from temporary folder to permanent folder
		//put the path information in $_POST
		
	}

	function _css($files) {

	}

	function _js($files) {
		
	}

	function _slug($arrCfg=null) {
		if($arrCfg==null) {
			if(isset($_ENV['PAGESLUG'])) return $_ENV['PAGESLUG'];
		} else {
			if(isset($_ENV['PAGESLUG-MAIN'])) {
				if(!is_array($arrCfg)) $arrCfg=explode("/", $arrCfg);
				$arrCfg=array_flip($arrCfg);

				foreach ($arrCfg as $key => $value) {
					if(isset($_ENV['PAGESLUG-MAIN'][$value])) $arrCfg[$key]=$_ENV['PAGESLUG-MAIN'][$value];
					else $arrCfg[$key]="";
				}

				return $arrCfg;
			} else {
				$uri=current(explode("?", $_SERVER['SERVICE_URI']));
				$slugs=explode("/", $uri);
				if(strlen($slugs[0])==0) $slugs=array_splice($slugs, 1);

				if(!is_array($arrCfg)) $arrCfg=explode("/", $arrCfg);
				$arrCfg=array_flip($arrCfg);

				foreach ($arrCfg as $key => $value) {
					if(isset($slugs[$value])) $arrCfg[$key]=$slugs[$value];
					else $arrCfg[$key]="";
				}
				return $arrCfg;
			}
		}
		return array();
	}

	function printAddonAssets($srcFile, $type) {
		$baseName = basename($srcFile);

		$cssList = [
			str_replace(".json", ".css", $srcFile),
			APPROOT."css/{$type}/".str_replace(".json", ".css", $baseName)
		];
		$jsList = [
			str_replace(".json", ".js", $srcFile),
			APPROOT."js/{$type}/".str_replace(".json", ".js", $baseName)
		];

		foreach ($cssList as $cssFile) {
			if(file_exists($cssFile)) {
				echo "<style>";
				readfile($cssFile);
				echo "</style>";
			}	
		}
		foreach ($jsList as $jsFile) {
			if(file_exists($jsFile)) {
				echo "<script type='text/javascript' language='javascript'>";
				readfile($jsFile);
				echo "</script>";
			}
		}
	}

	function printGeneralAssets() {
		$list1 = [
			// dirname(checkModule("forms"))."/vendors/autoload.php",
			// dirname(checkModule("infoview"))."/vendors/autoload.php",
			// dirname(checkModule("infoviewTable"))."/vendors/autoload.php",
			// dirname(checkModule("reports"))."/vendors/autoload.php",

			dirname(checkModule("forms"))."/vendors/mapps.php",
			dirname(checkModule("infoview"))."/vendors/mapps.php",
			dirname(checkModule("infoviewTable"))."/vendors/mapps.php",
			dirname(checkModule("reports"))."/vendors/mapps.php",
			
			dirname(checkModule("mapps"))."/vendors/autoload.php",
		];
		$list2 = [];

		$listCoreModules = ["forms","reports","infoview","infoviewTable","infoviewMatrix","infovisuals","views"];

		foreach($listCoreModules as $module) {
			$modPath = checkModule($module);
			if($modPath) {
				$list2[getWebPath(dirname($modPath))."/style.css"] = "css";
				$list2[getWebPath(dirname($modPath))."/script.js"] = "js";
			}
		}
		$list2[WEBAPPROOT."css/mapps.css"] = "css";
		$list2[WEBAPPROOT."js/mapps.js"] = "js";

		foreach($list1 as $f) {
			if(file_exists($f)) {
				include_once $f;
			}
		}
		echo "\n\n";
		foreach($list2 as $f=>$t) {
			$f1 = str_replace(WEBROOT, ROOT, $f);
			if(file_exists($f1)) {
				switch ($t) {
					case 'js':
						echo "<script src='{$f}' type='text/javascript' language='javascript' ></script>\n";
						// echo "<script type='text/javascript' language='javascript' >";
						// readfile($f1);
						// echo "</script>";
						break;
					case 'css':
						echo "<link href='{$f}' rel='stylesheet' type='text/css' />\n";
						// echo "<style>";
						// readfile($f1);
						// echo "</style>";
						break;
				}
			}
		}
		echo "\n";
		
		//Scripts, Styles from DB or mobile app specific files
		$codeBlocks = _db()->_selectQ("mapps_codes","*",[
						"mapps_id"=>$_SESSION['SESS_MAPPS']['app_id'],
						"blocked"=>"false",
					])->_GET();
		if(!$codeBlocks) $codeBlocks = [];

		foreach ($codeBlocks as $codeRow) {
			switch ($codeRow['type']) {
				case 'js':
					echo "<script type='text/javascript' language='javascript' >";
					echo $codeRow['script_code'];
					echo "</script>";
					break;
				case 'css':
					echo "<style>";
					echo $codeRow['script_code'];
					echo "</style>";
					break;
			}
		}

		//Other Assets
		// echo _css(explode(",",FORM_CSS));
		// echo _js(explode(",",FORM_JS));
		//define("FORM_CSS",'bootstrap.datetimepicker,forms');
		//define("FORM_JS",'jquery.validate,moment,bootstrap.datetimepicker,forms');
		// echo _css(explode(",","forms"));
		// echo _js(explode(",","forms"));
	}
}
?>