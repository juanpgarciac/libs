<?php

function encrypt_decrypt($action, $string) {
    $output = false;
    $encrypt_method = encrypt_method();
    $secret_key = encrypt_key();
    $secret_iv = encrypt_key();
    // hash
    $key = hash('sha256', $secret_key);// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if( $action == 'encrypt' || $action == 'e' ) {
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
    }else 
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    return $output;
}
function encrypt($string) {
    return encrypt_decrypt('encrypt',$string);
}
function decrypt($string) {
    return encrypt_decrypt('decrypt',$string);
}
function alertaBoostrap($mensaje,$tipo="",$container=false){
    /*
    * tipo: -info,-success,-danger, vacio es warning
    */
    $class = '';
    if($container){
        include_once 'bootstrap.html';
        $class = 'class="container" style="margin-top:20px;"';
    }
    $alerta = '
        <div '.$class.'>
            <div class="alert alert'.$tipo.'" style="text-align:center;">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>'.$mensaje.'</strong>
            </div>
        </div>';        
    return $alerta;
}
function numero_a_mes($value,$completo = false){
    $mes = '';
   
    switch ($value) {
        case 1:
        case '01':
            $mes = 'Enero';
            break;
        case 2:
        case '02':
            $mes = 'Febrero';
            break;
        case 3:
        case '03':
            $mes = 'Marzo';
            break;            
        case 4:
        case '04':
            $mes = 'Abril';
            break;                       
        case 5:
        case '05':
            $mes = 'Mayo';
            break;                               
        case 6:
        case '06':
            $mes = 'Junio';
            break;        
        case 7:
        case '07':
            $mes = 'Julio';
            break;        
        case 8:
        case '08':
            $mes = 'Agosto';
            break;        
        case 9:
        case '09':
            $mes = 'Septiembre';            
            break;        
        case 10:
        case '10':
            $mes = 'Octubre';
            break;        
        case 11:
        case '11':
            $mes = 'Noviembre';
            break;        
        case 12:
        case '12':
            $mes = 'Diciembre';
            break;                    
    }
    if(!$completo)
        return substr($mes,0,3);    
    return $mes;
}
function getinput($name,$placeholder="",$type="text",$required='required="required"',$class="form-control",$pattern=""){
    $placeholder = $placeholder?$placeholder:mb_strtoupper($name);    
    $value = $GLOBALS[$name];    
    echo "<input class=\"$class\" id=\"$name\" name=\"$name\" placeholder=\"$placeholder\" $required type=\"$type\" value=\"$value\" pattern=\"$pattern\"  />";
}
function addlabel($id,$label,$tags=null){
    return '<label '.$id.' '.$tags.'>'.$label.'</label>';    
}
function addinput($id,$name,$value,$tags=null,$placeholder=null,$type='text',$aftercode=null){
    
    //'email','email', $email, 'required="" '.$readonly,'Email address', 'email','<span class="icon-email icon-right"></span>'
    return '<input id="'.$id.'" name="'.$name.'"  value="'.$value.'"  '.$tags.' placeholder="'.$placeholder.'" type="'.$type.'"  class="form-control ff-rounded" />'.$aftercode;    
}
