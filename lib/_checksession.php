<?php
function init_session($arr){
    unsetsession();
    $config = session_vars();        
    if(session_start()){
        $_SESSION[SESSIONACTIVE]=array();//abro la session que viene del _includes                 
        foreach ($config as $arrkey => $varsession) {
            if($varsession){
                $_SESSION[SESSIONACTIVE][$varsession] = encrypt($arr[$arrkey]);            
            }
        }
        session_commit();           
    }
}
function getsession(){
    getsession_decrypted();
}
function getsession_decrypted(){
    unsetsession(false);
    if(session_start()){
        if(isset($_SESSION[SESSIONACTIVE])&&!empty($_SESSION[SESSIONACTIVE])){
            foreach ($_SESSION[SESSIONACTIVE] as $key => $value) {
                if($value)$GLOBALS[$key] = decrypt($value);
            }
        }
    }
    session_commit();
}
function unsetsession($destroysession=true){
    if($destroysession){
        session_start();
        //session_unset();
        unset($_SESSION[SESSIONACTIVE]);
        //session_destroy();        
        session_commit();        
    }
    $config = session_vars();
    foreach ($config as $varsession) {
        if($varsession){
            $GLOBALS[$varsession] = null;
        }
    }
}
function checkModulo($modulo){    
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
function checksession($link = null,$msj = null){
    getsession_decrypted();
    global $idusuario_session;
    if(!isset($idusuario_session)||!$idusuario_session){
       if($link)header('Location:'.$link);
       else if($msj)echo $msj;
       die();
    } 
}
getsession();