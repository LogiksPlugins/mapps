<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("setupMAUTHEnviroment")) {

    include_once __DIR__."/datacontrols.php";
    include_once __DIR__."/dev.php";

    //To Enable Device Control
    // $_HEADERS['x-device-uuid']
    // $_HEADERS['x-device-model']
    // $_HEADERS['x-device-os']

    function setupMAPPEnviroment() {
        define("MAPPS_RESOURCE_DIR", APPROOT."misc/mapps/");

        $mappInfo = _db(true)->_selectQ("mapps_tbl","id as app_id,app_key,app_secret,app_site,app_name,default_policies,single_device",[
                    "blocked"=>"false",
                    "md5(app_key)"=>md5(MAPPS_APP_KEY)
                ])->_GET();

        if($mappInfo===false || !isset($mappInfo[0])) {
            printServiceErrorMsg(406,"Mobile App Expired or Not Updated<br>Please try updating the app from the appstore");
            exit();
        }
        
        $_HEADERS = getallheaders();

        $mappInfo = $mappInfo[0];
        if($mappInfo['default_policies']==null || strlen($mappInfo['default_policies'])<=0) {
            $mappInfo['default_policies'] = "{}";
        }
        $mappInfo['default_policies'] = json_decode($mappInfo['default_policies'], true);
        if($mappInfo['default_policies']==null) $mappInfo['default_policies'] = [];
        if(!isset($mappInfo['single_device'])) $mappInfo['single_device'] = "false";
        
        $_SESSION['SESS_MAPPS'] = $mappInfo;

        define("MAPPS_APP_SECRET", $mappInfo['app_secret']);
        define("MAPPS_APP_NAME", $mappInfo['app_name']);
        define("MAPPS_APP_SITE", $mappInfo['app_site']);
        define("MAPPS_DEFAULT_POLICIES", json_encode($mappInfo['default_policies']));

        if($mappInfo['single_device']=='true') {
            $mobData1 = _db(true)->_selectQ("mapps_devices", "*", [
                "guid"=> (isset($_SESSION['SESS_GUID'])?$_SESSION['SESS_GUID']:"-"),
                "app_key"=> $mappInfo['app_id'],
                "userid"=> $_SESSION['SESS_USER_ID'],
                "device_uuid"=> (isset($_HEADERS['x-device-uuid'])?$_HEADERS['x-device-uuid']:"-"),
                "device_model"=> (isset($_HEADERS['x-device-model'])?$_HEADERS['x-device-model']:"-"),
                "is_active"=>"true"
            ])->_GET();


            if($mobData1) {
                _db(true)->_updateQ("mapps_devices", [
                    "access_count"=> ((int)$mobData1[0]['access_count'])+1,
                    "last_accessed"=> date("Y-m-d H:i:s"),
                    "ip_address"=> get_client_ip(),
                    "geolocation"=> (isset($_REQUEST['geolocation'])?$_REQUEST['geolocation']:"0,0"),
                ],[
                    "id"=> $mobData1[0]['id']
                ])->_RUN();
            } else {
                $mobData2 = _db(true)->_selectQ("mapps_devices", "*", [
                    "guid"=> (isset($_SESSION['SESS_GUID'])?$_SESSION['SESS_GUID']:"-"),
                    "app_key"=> $mappInfo['app_id'],
                    "userid"=> $_SESSION['SESS_USER_ID'],
                    "is_active"=>"true"
                ])->_GET();

                if($mobData2) {
                    printServiceErrorMsg(406,"Only one device is allowed for user, please contact admin for further actions");
                    exit();
                } else {
                    _db(true)->_insertQ1("mapps_devices", [
                        "guid"=> (isset($_SESSION['SESS_GUID'])?$_SESSION['SESS_GUID']:"-"),
                        "app_key"=> $mappInfo['app_id'],
                        "userid"=> $_SESSION['SESS_USER_ID'],
                        "device_uuid"=> (isset($_HEADERS['x-device-uuid'])?$_HEADERS['x-device-uuid']:"-"),
                        "device_model"=> (isset($_HEADERS['x-device-model'])?$_HEADERS['x-device-model']:"-"),
                        "os_version"=> (isset($_HEADERS['x-device-os'])?$_HEADERS['x-device-os']:"-"),
                        "ip_address"=> get_client_ip(),
                        "geolocation"=> (isset($_REQUEST['geolocation'])?$_REQUEST['geolocation']:"0,0"),
                        "last_accessed"=> date("Y-m-d H:i:s"),
                        "access_count"=> 1,
                    ])->_RUN();
                }
            }
        } else {
            $mobData1 = _db(true)->_selectQ("mapps_devices", "*", [
                "guid"=> (isset($_SESSION['SESS_GUID'])?$_SESSION['SESS_GUID']:"-"),
                "app_key"=> $mappInfo['app_id'],
                "userid"=> $_SESSION['SESS_USER_ID'],
                "device_uuid"=> (isset($_HEADERS['x-device-uuid'])?$_HEADERS['x-device-uuid']:"-"),
                "device_model"=> (isset($_HEADERS['x-device-model'])?$_HEADERS['x-device-model']:"-"),
                "is_active"=>"true"
            ])->_GET();


            if($mobData1) {
                _db(true)->_updateQ("mapps_devices", [
                    "access_count"=> ((int)$mobData1[0]['access_count'])+1,
                    "last_accessed"=> date("Y-m-d H:i:s"),
                    "ip_address"=> get_client_ip(),
                    "geolocation"=> (isset($_REQUEST['geolocation'])?$_REQUEST['geolocation']:"0,0"),
                ],[
                    "id"=> $mobData1[0]['id']
                ])->_RUN();
            } else {
                _db(true)->_insertQ1("mapps_devices", [
                    "guid"=> (isset($_SESSION['SESS_GUID'])?$_SESSION['SESS_GUID']:"-"),
                    "app_key"=> $mappInfo['app_id'],
                    "userid"=> $_SESSION['SESS_USER_ID'],
                    "device_uuid"=> (isset($_HEADERS['x-device-uuid'])?$_HEADERS['x-device-uuid']:"-"),
                    "device_model"=> (isset($_HEADERS['x-device-model'])?$_HEADERS['x-device-model']:"-"),
                    "os_version"=> (isset($_HEADERS['x-device-os'])?$_HEADERS['x-device-os']:"-"),
                    "ip_address"=> get_client_ip(),
                    "geolocation"=> (isset($_REQUEST['geolocation'])?$_REQUEST['geolocation']:"0,0"),
                    "last_accessed"=> date("Y-m-d H:i:s"),
                    "access_count"=> 1,
                ])->_RUN();
            }

        }
        
        // printServiceErrorMsg(406,"X123");
        // exit();
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
