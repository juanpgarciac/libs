<?php
function db_config(){
    return  array('host'=>'localhost','port'=>3306,'user'=>'','password'=>'','dbname'=>'','socket'=>null);
}
function encrypt_method(){
    return "AES-256-CBC";
}
function encrypt_key(){
    return 'add a key';
}
