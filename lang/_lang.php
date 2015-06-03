<?php 
global $global_language;
if(!isset($_COOKIE['global_language'])||!$_COOKIE['global_language']||isset($_GET['lang'])){
    $global_language = isset($_GET['lang'])&&$_GET['lang']?$_GET['lang']:GLOBAL_LANGUAGE;
    setcookie("global_language",$global_language,time()+60*60*24*365,'/');//guardar la cookie por un año?
}else $global_language = isset($_COOKIE['global_language'])&&$_COOKIE['global_language']?$_COOKIE['global_language']:GLOBAL_LANGUAGE;
function userLanguage(){
    $file = DOCUMENTROOT.SERVERHOST.'lang/_lang.ini';
    if(!file_exists($file)){
        return false;
    }else return parse_ini_file($file);
}
function printo($index){
    global $global_language;
    $indice = ($index)?mb_strtoupper($index."_$global_language"):mb_strtoupper("MSJ_NO_INDEX_GIVEN_$global_language");
    if(strpos($indice,'MSJ_')===false)$indice = "MSJ_$indice";
    $arr = userLanguage();
    if($arr)return isset($arr[$indice])&&$arr[$indice]?stripslashes($arr[$indice]):$indice;
    else return $indice;
}
function getgloballanguage(){
    global $global_language;
    return $global_language;
}