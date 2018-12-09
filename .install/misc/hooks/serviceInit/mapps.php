<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//MAUTH KEY, checking and setting up the Mobile APP Rest Key Transactions
loadModuleLib("mapps","api");
if(function_exists("setupMAUTHEnviroment")) {
  setupMAUTHEnviroment();
}
?>