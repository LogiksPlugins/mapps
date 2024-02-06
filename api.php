<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("setupMAUTHEnviroment")) {

    include_once __DIR__."/datacontrols.php";
    include_once __DIR__."/dev.php";

    function setupMAPPEnviroment() {
        define("MAPPS_RESOURCE_DIR", APPROOT."misc/mapps/");

        $mappInfo = _db(true)->_selectQ("mapps_tbl","id as app_id,app_key,app_secret,app_site,app_name,default_policies",[
                    "blocked"=>"false",
                    "md5(app_key)"=>md5(MAPPS_APP_KEY)
                ])->_GET();

        if($mappInfo===false || !isset($mappInfo[0])) {
            printServiceErrorMsg(406,"Mobile App Expired or Not Updated<br>Please try updating the app from the appstore");
            exit();
        }

        $mappInfo = $mappInfo[0];
        if($mappInfo['default_policies']==null || strlen($mappInfo['default_policies'])<=0) {
            $mappInfo['default_policies'] = "{}";
        }
        $mappInfo['default_policies'] = json_decode($mappInfo['default_policies'], true);
        if($mappInfo['default_policies']==null) $mappInfo['default_policies'] = [];

        $_SESSION['SESS_MAPPS'] = $mappInfo;

        define("MAPPS_APP_SECRET", $mappInfo['app_secret']);
        define("MAPPS_APP_NAME", $mappInfo['app_name']);
        define("MAPPS_APP_SITE", $mappInfo['app_site']);
        define("MAPPS_DEFAULT_POLICIES", json_encode($mappInfo['default_policies']));
    }

    function setupMAUTHEnviroment() {
        if(isset($_REQUEST['scmd']) && in_array($_REQUEST['scmd'], [
            "auth","logout","alive","ping","resources","avatar"
        ])) {
            return;
        }

        if(!defined("SERVICE_ROOT")) return;

        $_HEADERS = getallheaders();

        if(!isset($_HEADERS['Authorization'])) {
          // return false;
        }

        if(isset($_HEADERS['appkey'])  && strlen($_HEADERS['appkey'])>1) {
            $appkey = $_HEADERS['appkey'];
            define("MAPPS_APP_KEY", $appkey);
        }

        if(isset($_HEADERS['token']) && strlen($_HEADERS['token'])>1) {
            $token = $_HEADERS['token'];

            $jwt = new LogiksJWT();
            $tokenData = $jwt->decodeToken($token);

            if($tokenData) {
                if($tokenData['exp']<time()) {
                    printServiceErrorMsg(401,"Please login to continue (1)");
                    exit("");
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
                printServiceErrorMsg(401,"Please login to continue (2)");
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

    function getDefaultMenu() {
        return [
                "title"=>"",
            ];
    }

    function getDefaultPanelParams() {
        return [
                "title"=>"",
                "panel"=>"",
                "blocks"=>[],
                "header"=>[],
                "footer"=>[],
                "banner"=>[],
                "floatbutton"=>[],
                "config"=>[]
            ];
    }

    function getTextBetweenTags($string, $tagname) {
        $pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
        preg_match($pattern, $string, $matches);
        if(!isset($matches[1])) return $string;
        return $matches[1];
    }

    function processFuncKey($funcKey, $dbData) {
        if(isset($dbData["hashid"])) {
            $funcKey = str_replace("{hashid}",$dbData["hashid"],$funcKey);
        }
        foreach ($dbData as $key => $value) {
            $funcKey = str_replace("{".$key."}",$value,$funcKey);
        }
        return $funcKey;
    }
}
?>
