<?php
$idusuario_session =  null;
$usuario_session = null;
$email_session = null;
$nivel_session = null;
$gestion_session = null;
function getsession_decrypted(){
    session_start();
    global $idusuario_session;
    global $usuario_session;
    global $nivel_session;
    global $email_session;
    global $gestion_session;
    if(isset($_SESSION['idusuario_session'.SESSIONACTIVE])&&$_SESSION['idusuario_session'.SESSIONACTIVE]&&decrypt($_SESSION['idusuario_session'.SESSIONACTIVE])){
        $idusuario_session = decrypt($_SESSION['idusuario_session'.SESSIONACTIVE]);//usuario que ingreso el registro
        $email_session = decrypt($_SESSION['email_session'.SESSIONACTIVE]);
        $usuario_session = decrypt($_SESSION['usuario_session'.SESSIONACTIVE]);
        $nivel_session = decrypt($_SESSION['nivel_session'.SESSIONACTIVE]);
        $gestion_session = explode(',',decrypt($_SESSION['gestion_session'.SESSIONACTIVE]));   
        session_commit();
        return true;
    }
    session_commit();
    return false;
}
function getsession_encrypted(){
    session_start();
    global $idusuario_session;
    global $usuario_session;
    global $nivel_session;
    global $email_session;
    global $gestion_session;
    if(isset($_SESSION['idusuario_session'.SESSIONACTIVE])&&$_SESSION['idusuario_session'.SESSIONACTIVE]){
        $idusuario_session = ($_SESSION['idusuario_session'.SESSIONACTIVE]);//usuario que ingreso el registro
        $usuario_session = ($_SESSION['usuario_session'.SESSIONACTIVE]);
        $email_session = ($_SESSION['email_session'.SESSIONACTIVE]);
        $nivel_session = ($_SESSION['nivel_session'.SESSIONACTIVE]);
        $gestion_session = ($_SESSION['gestion_session'.SESSIONACTIVE]);   
        session_commit();
        return true;
    }
    session_commit();
    return false;
}

function unsetsession(){
    global $idusuario_session;
    global $usuario_session;
    global $email_session;
    global $nivel_session;
    global $gestion_session;
    $idusuario_session =  null;
    $usuario_session = null;
    $nivel_session = null;
    $email_session = null;
    $gestion_session = null;
    session_start();
    unset($_SESSION['idusuario_session'.SESSIONACTIVE]);
    unset($_SESSION['usuario_session'.SESSIONACTIVE]);
    unset($_SESSION['email_session'.SESSIONACTIVE]);
    unset($_SESSION['nivel_session'.SESSIONACTIVE]);
    unset($_SESSION['gestion_session'.SESSIONACTIVE]);
    session_commit();
}
/*
session_start();
$link = 'index.php?link='.basename($_SERVER['PHP_SELF']); 
$idusuario_session =  null;
$usuario_session = null;
$nivel_session = null;
$gestion_session = null;
if(isset($_SESSION['saveoutsidethesession']) || isset($_POST['saveoutsidethesession'])){
    //solo para salvar fuera de la session   
    if(isset( $_SESSION['idusuario'])){
        $idusuario_session =  $_SESSION['idusuario_session'.SESSIONACTIVE];//usuario que ingreso el registro    
    }
}else{
    if($_SESSION['nivel']!= encrypt('admin') && $_SESSION['nivel']!= encrypt('ROOT') && $_SESSION['nivel']!=encrypt('limitado')){
        session_commit();
        include 'bootstrap.html';
        exit('<div class="container" style="margin-top:20px;">
                <div class="alert alert-danger">
                    <h4>Oops!</h4>                            
                    <p>Lo sentimos, pero su cuenta no tiene permisos para realizar cambios!</p>
                    <a href="'.$link.'">Ir al Login</a>
                </div>
              </div>');   
    }else{
        $idusuario_session = decrypt($_SESSION['idusuario']);//usuario que ingreso el registro
        $usuario_session = decrypt($_SESSION['nombre']);
        $nivel_session = decrypt($_SESSION['nivel']);
        $gestion_session = explode(',',decrypt($_SESSION['gestion']));
    }   
}
session_commit();
*/
function checkModulo($modulo){
    getsession();
    global $gestion_session;
    global $nivel_session;
    global $link;
    if(!in_array($modulo,$gestion_session) && $nivel_session !== 'admin'){
        include 'bootstrap.html';
        exit('<div class="container" style="margin-top:20px;">
                <div class="alert alert-danger">
                    <h4>Oops!</h4>                            
                    <p>Lo sentimos, pero su cuenta no tiene permisos para realizar cambios!</p>
                    <a href="'.$link.'">Ir al Login</a>
                </div>
              </div>');  
    }
}
getsession_decrypted();


