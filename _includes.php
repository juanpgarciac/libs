<?php
set_time_limit(0);
date_default_timezone_set('America/Caracas');
ini_set('mbstring.internal_encoding','UTF-8');
define("SERVERHOST", '/');
define("DEBUGME", FALSE);
define("TMPUPLOADS", SERVERHOST.'panelAdmin/tmp_uploads');
define("SESSIONACTIVE", 'POSADASMARGARITA');
/**/
if(DEBUGME){
    ini_set("display_startup_errors", "1");
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
}
/**
echo "<pre>";
echo "POST:";
print_r($_POST);
echo "FILES:";
print_r($_FILES);
echo "</pre>";                    
/**/ 
include SERVERHOST."lang/_lang.php";
include SERVERHOST."lib/_config.php";
include SERVERHOST."lib/_mysqli.php";
include SERVERHOST."lib/_funciones.php";
include SERVERHOST."lib/_procesocomun.php";
include SERVERHOST."lib/_checksession.php";

