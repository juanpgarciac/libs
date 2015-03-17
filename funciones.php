<?php
function enviarEmail($email,$nombre,$asunto,$text){
    
    //---------------------------CONFIGURAR SERVER----------------------------//
    if(file_exists('res/phpmailer/class.phpmailer.php'))
        require_once 'res/phpmailer/class.phpmailer.php';
    else if(file_exists('../res/phpmailer/class.phpmailer.php'))
        require_once '../res/phpmailer/class.phpmailer.php';
    $host='server-0116a.dnsprincipal.com';
    $sender='sender@posadas-venezuela.com.ve';
    $clave="nu54P<znjM";
    $hello = "www.posadas-venezuela.com.ve";  
    $from = 'www.posadas-venezuela.com.ve';
    $mail = new PHPMailer();                                                                    
    /* DEBUGGING 
    $mail->Debugoutput = 'echo';
    $mail->SMTPDebug  =true;
    /**/    
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->FromName = $from;
    $mail->SMTPSecure = "ssl";
    $mail->Hello = $hello;
    $mail->Host = $host;                    
    $mail->Port = 465;//25//587//465            
    $mail->Username = $sender;
    $mail->Password = $clave;   
    //------------------------------------------------------------------------//    
    $mail->SetFrom($sender, $nombre);
    //$mail->AddReplyTo($sender,$nombre);    
    $mail->Subject = $asunto;
    $mail->MsgHTML($text);
    $mail->AddAddress($email);   //agregar email para enviar      
    //$mail->AddAddress(''); 
    if($mail->Send()):    
        $mail->ClearAddresses();
        return true;
    else:
        return false;
endif;

}
function encrypt_decrypt($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'jpgsolucionestecnologicas key';
    $secret_iv = 'jpgsolucionestecnologicas iv';
    // hash
    $key = hash('sha256', $secret_key);    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if( $action == 'encrypt' || $action == 'e' ) {
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        //$output = base64_encode($output);
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

