<?php
date_default_timezone_set('America/Caracas');
set_time_limit(0);
ini_set('mbstring.internal_encoding','UTF-8');
include '_config.php';
$GLOBALS['IMG_MYME_TYPES'] = array('image/png','image/jpeg','image/jpeg','image/jpeg','image/gif','image/bmp','image/vnd.microsoft.icon','image/tiff','image/tiff','image/svg+xml','image/svg+xml');
function db_config(){
    return  array('host'=>'localhost','port'=>3306,'user'=>DBUSER,'password'=>DBPASSWORD,'dbname'=>DBNAME,'socket'=>null);//jpgsoluc_ads &wT@^aGbunMd
}
function session_vars(){
    return array('id'=>'idusuario_session','nombre'=>'usuario_session','email'=>'email_session','nivel'=>'nivel_session','gestion'=>'gestion_session');
}
function encrypt_key(){
    return PROJECTSALTKEY;
}
function encrypt_method(){
    return "AES-256-CBC";
}
if(file_exists(DOCUMENTROOT.SERVERHOST."lang/_lang.php"))
    include DOCUMENTROOT.SERVERHOST."lang/_lang.php";
else die('Error el archivo _lang.php no esta presente');
if(file_exists(DOCUMENTROOT.SERVERHOST."lib/_mysqli.php"))
    include DOCUMENTROOT.SERVERHOST."lib/_mysqli.php";
else die('Error el archivo _mysqli.php no esta presente');
if(file_exists(DOCUMENTROOT.SERVERHOST."lib/_funciones.php"))
    include DOCUMENTROOT.SERVERHOST."lib/_funciones.php";
else die('Error el archivo _funciones.php no esta presente');
if(file_exists(DOCUMENTROOT.SERVERHOST."lib/_procesocomun.php"))
    include DOCUMENTROOT.SERVERHOST."lib/_procesocomun.php";
else die('Error el archivo _procesocomun.php no esta presente');
if(file_exists(DOCUMENTROOT.SERVERHOST."lib/_checksession.php"))
    include DOCUMENTROOT.SERVERHOST."lib/_checksession.php";
else die('Error el archivo _checksession.php no esta presente');
if(DEBUGME){
    ini_set("display_startup_errors", "1");
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
}