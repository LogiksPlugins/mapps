<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$mappsResourceDir = APPROOT."misc/mapps/";

switch($_REQUEST['action']) {
  //System related Services
  case "verify":
    printServiceMsg([
        "SITENAME"=>SITENAME,
        "SERVICE_SESSION"=>checkServiceSession(true,false),
        "SERVICE_ACCESS"=>in_array(SITENAME,$_SESSION['SESS_ACCESS_SITES']),
        "USERID"=>$_SESSION['SESS_USER_ID'],
      ]);
    break;
  case "me":
    printServiceMsg(array_filter($_SESSION, "sessUserInfo",ARRAY_FILTER_USE_KEY));
    break;
  case "team":
    printServiceMsg([]);
    break;
  case "checkaccess":
    if(!isset($_REQUEST['src']) || strlen($_REQUEST['src'])<=1) {
      $_REQUEST['src'] = SITENAME;
    }
    printServiceMsg(["src"=>SITENAME,"status"=>in_array($_REQUEST['src'],$_SESSION['SESS_ACCESS_SITES'])]);
    break;
  case "config":
    printServiceMsg([
      "sitename"=>SITENAME,
    ]);
    break;
  case "sitelist":
    printServiceMsg($_SESSION['SESS_ACCESS_SITES']);
    break;
  case "modulelist":
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
    
    printServiceMsg($fs);
    break;
  
    
  //UI and functionality related services
  case "menu":
    if(!isset($_REQUEST['menuid']) || strlen($_REQUEST['menuid'])<=1) {
        $_REQUEST['menuid'] = "mapps";
    }
    loadModuleLib("navigator","api");
    $menuTree = generateNavigationFromDB($_REQUEST['menuid'],"links");
    printServiceMsg($menuTree);
    break;
  case "datalist":
    if(!isset($_REQUEST['groupid']) || strlen($_REQUEST['groupid'])<=1) {
        printServiceMsg([]);
        return;
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

    printServiceMsg($sqlObj->_GET());
    break;
  case "lingbook":
    $ling=Lingulizer::getInstance();
    printServiceMsg($ling->lang);
    break;
  case "feeds"://notifications
    printServiceMsg([]);
    break;
  case "msgs"://messages from notifyMatrix
    printServiceMsg([]);
    break;
    
  //More powerfull hotplug commands
  case "panel":
    if(!is_dir($mappsResourceDir."panels/")) {
      echo "Resource Does Not Exist";
      return;
    }
    if(isset($_REQUEST['panelid'])) {
      echo "{$_REQUEST['panelid']} Not Found";
    } else {
      echo "Panel Not Found";
    }
    break;

  case "cmd":
    if(!is_dir($mappsResourceDir."cmds/")) {
      echo "Resource Does Not Exist";
      return;
    }
    if(isset($_POST['cmd'])) {
      printServiceMsg("{$_POST['cmd']} Not Found");
    } else {
      printServiceMsg("No Command Defined");
    }
    break;
  
  case "uri":
    if(isset($_REQUEST['uri'])) {
      echo "{$_REQUEST['uri']} Not Found";
    } else {
      echo "URI Not Found";
    }
    break;
  
  case "unilink":
    if(isset($_REQUEST['unilink'])) {
      echo "{$_REQUEST['unilink']} Not Found";
    } else {
      echo "Reference Not Found";
    }
    break;
}
?>