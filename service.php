<?php
if(!defined('ROOT')) exit('No direct script access allowed');

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL); 

if(!defined("MAPPS_APP_KEY")) {
	echo "Not a MAPP Call";
	exit();
}

setupMAPPEnviroment();

$fs = scandir(__DIR__."/cmds/");
array_shift($fs);array_shift($fs);

foreach ($fs as $f) {
  	include_once __DIR__."/cmds/{$f}";
}

handleActionMethodCalls();
?>