<?php
if(!defined('ROOT')) exit('No direct script access allowed');

switch($_REQUEST['action']) {
  case "config":
    printServiceMsg([
      "sitename"=>SITENAME,
    ]);
    break;
  case "menu-main":case "menu-primary":
    printServiceMsg([]);
    break;
  case "menu-secondary":
    printServiceMsg([]);
    break;
  case "menu-tray":
    printServiceMsg([]);
    break;
  case "panel":
    if(isset($_REQUEST['panelid'])) {
      printServiceMsg([
        "panelid"=>$_REQUEST['panelid'],
        "pagelink"=>_service("mapps","panel-page")."&panelid={$_REQUEST['panelid']}"
      ]);
    } else {
      printServiceMsg([]);
    }
    break;
  case "panel-page":
    if(isset($_REQUEST['panelid'])) {
      echo "{$_REQUEST['panelid']} Not Found";
    } else {
      echo "Nothing Found";
    }
    break;
  case "datalist":
    printServiceMsg([]);
    break;
  case "lingbook":
    printServiceMsg([]);
    break;
}
?>