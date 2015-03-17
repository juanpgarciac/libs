<?php 
$global_language_SET = array();
$global_language_PRED =array();
$global_language = 'es';
session_start();
if(!isset($_SESSION['global_language'])||!$_SESSION['global_language']||isset($_GET['lang'])||isset($_GET['rlang'])){
    $_SESSION['global_language'] = isset($_GET['lang'])&&$_GET['lang']?$_GET['lang']:'es';    
    $global_language = $_SESSION['global_language'];
}
setLanguage($global_language);
$global_language_PRED = userLanguage('es');//predeterminado
session_commit();

function userLanguage($lang){
    $file = 'lang/'.$lang.'.ini';
    if(!file_exists($file)){
        return array();
    }else return parse_ini_file($file);
}
function printo($index){
    global $global_language_SET;
    global $global_language_PRED;
    $alt = isset($global_language_PRED[$index])&&$global_language_PRED[$index]?$global_language_PRED[$index]:$index;
    return isset($global_language_SET[$index])&&$global_language_SET[$index]?$global_language_SET[$index]:$alt;
}

function setLanguage($global_language){
    global $global_language_SET;
    $global_language_SET = userLanguage($global_language);
}
function getgloballanguage(){
    global $global_language;
    return $global_language;
}