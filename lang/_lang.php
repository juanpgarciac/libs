<?php 
global $global_language;
session_start();
if(!isset($_SESSION['global_language'])||!$_SESSION['global_language']||isset($_GET['lang'])||isset($_GET['rlang'])){
    $_SESSION['global_language'] = isset($_GET['lang'])&&$_GET['lang']?$_GET['lang']:'es';    
}
$global_language = $_SESSION['global_language'];
session_commit();
function userLanguage(){
    $file = 'lang/_lang.js';
    if(!file_exists($file)){
        return false;
    }else return parse_ini_file($file);
}
function printo($index){   
    global $global_language;
    $arr = userLanguage();
    $indice = $index.'_'.$global_language;
    if($arr)return isset($arr[$indice])&&$arr[$indice]?$arr[$indice]:$indice;
    else return $indice;
}
function getgloballanguage(){
    global $global_language;
    return $global_language;
}