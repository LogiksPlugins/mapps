<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("setupMAUTHEnviroment")) {

  function setupMAUTHEnviroment() {
    if(isset($_GET['scmd']) && $_GET['scmd']=="auth") return;
    if(!defined("SERVICE_ROOT")) return;
    
    $_HEADERS = getallheaders();
    if(isset($_HEADERS['token']) && strlen($_HEADERS['token'])>1) {
      $token = $_HEADERS['token'];
      
      $jwt = new LogiksJWT();
      $tokenData = $jwt->decodeToken($token);

      if($tokenData) {
        if($tokenData['exp']<time()) {
            printServiceErrorMsg(401,"Please login to continue");
            exit();
        }
        // if($_REQUEST['site']!=$tokenData["site"]) {
        //     printServiceErrorMsg(401,"Please login to continue for this site");
        //     exit();
        // }
        //$tokenData["site"]        //SESS_LOGIN_SITE

        $_SESSION['SESS_GUID']=$tokenData["guid"];
        $_SESSION['SESS_USER_NAME']=$tokenData["username"];

        $_SESSION['SESS_USER_ID']=$tokenData["user"];
        $_SESSION['SESS_USER_CELL']=$tokenData["mobile"];
        $_SESSION['SESS_USER_EMAIL']=$tokenData["email"];
        $_SESSION['SESS_USER_COUNTRY']=$tokenData["country"];

        $_SESSION['SESS_PRIVILEGE_ID']=$tokenData["privilegeid"];
        $_SESSION['SESS_PRIVILEGE_NAME']=$tokenData["privilege_name"];
        $_SESSION['SESS_ACCESS_ID']=$tokenData["accessid"];
        $_SESSION['SESS_GROUP_ID']=$tokenData["groupid"];
        $_SESSION['SESS_ACCESS_SITES']=$tokenData["access"];
        $_SESSION['SESS_USER_AVATAR']=$tokenData["avatar"];

        $_SESSION['SESS_LOGIN_SITE']=$tokenData["site"];
        if(!defined("SITENAME")) define("SITENAME",$_SESSION['SESS_LOGIN_SITE']);
        
        $_SESSION['SESS_SITEID'] = SiteID;
        $_SESSION['SESS_TOKEN'] = session_id();
        $_SESSION['MAUTH_KEY'] = generateMAuthKey();
        $_REQUEST['syshash'] = getSysHash();
        
        $_SESSION['MAUTH_KEY']=$tokenData["authkey"];
      } else {
        printServiceErrorMsg(401,"Please login to continue");
        exit();
      }
    }
  }

  function sessUserInfo($var) {
    if(substr($var,0,5)=="SESS_") {
        if(in_array($var,["SESS_TOKEN","SESS_SITEID"])) {//,"SESS_LOGIN_SITE"
            return false;
        }
        return true;
    } elseif(in_array($var,[])) {//,"ROLESGLOBAL","SCOPEMAP","siteList"
        return true;
    }
    return false;
  }
}
?>