<?php
//==================================COMUNES=====================================
function prepareVar(&$var,$post=null,$alt=null){
    $var = prepareVar_v2($_POST,$post,$alt);
}
function prepareVar_v2($arr=array(),$index='',$alt=null){
    return isset($arr[$index])?$arr[$index]:$alt;   
}
function prepareInsert(&$valores,&$campos,$arrIgnorar,$arrFechas,$arrEnteros,$avoidcontrol = false){
    global $idusuario_session;
    $idusuario_session = ($idusuario_session)?decrypt($idusuario_session):0;
    $i = 0;
    $valores = null;
    $campos = null;
    foreach ($_POST as $campo => $valor){        
        if($campo === 'funcion' || $campo === 'accion')
            break;  
        if(!in_array ($campo, $arrIgnorar)):
            if($i):
                $campos .= ','.$campo;            
                if(in_array ($campo, $arrFechas))://==============esta en fechas
                     $valores .= ",'".date('Y-m-d',strtotime($valor))."'";  
                else:                
                    if(in_array ($campo, $arrEnteros))://========esta en enteros                  
                        if(!$valor || empty($valor)){$valor = 0;}
                        $valores .= ",$valor";    
                    else:                        
                        $valores .= ",'$valor'";                
                    endif;                    
                endif;
            else://primer valor                    
                if(in_array ($campo, $arrFechas))://==============esta en fechas
                    $valores = "'".date('Y-m-d',strtotime($valor))."'";  
                else:                
                   if(in_array ($campo, $arrEnteros))://========esta en enteros                  
                       if(!$valor || empty($valor)){$valor = 0;}
                       $valores = $valor;
                   else: //es cadena
                        $valores = "'".trim($valor)."'" ;                    
                   endif;                    
                endif;            
                $campos = $campo;
            endif;                    
            ++$i;
        endif;
    } 
    if(!$avoidcontrol){
        $campos .= ',idusuariocreador,fcreacion';
        $valores .= ",$idusuario_session,now()";         
    }

    //echo $campos.'<br>'.$valores;die();
}
function prepareUpdate($tabla,$idr,$arrIgnorar,$arrFechas,$arrEnteros,$avoidcontrol = false){
    global $idusuario_session;    
    $idusuario_session = ($idusuario_session)?decrypt($idusuario_session):0;
    $i = 0;
    foreach ($_POST as $campo => $valor){        
        if(!in_array ($campo, $arrIgnorar))://=======================ignorar 
            if($campo === 'funcion' || $campo === 'accion')
                break;   
            if($i):                                    
                if(in_array ($campo, $arrFechas))://==============esta en fechas
                    $query .= ",$campo = '".date('Y-m-d',strtotime($valor))."'"; 
                else:
                    if(in_array ($campo, $arrEnteros))://========esta en enteros 
                        if(!$valor || empty($valor)){$valor = 0;}
                        $query .= ",$campo = $valor";
                    else://========esta en cadenas
                        $valor = str_replace("'",'', $valor);
                        $query .= ",$campo = '$valor'";
                    endif;  
                endif;
            else:
               if(in_array ($campo, $arrFechas))://==============esta en fechas
                        $query = "$campo = '".date('Y-m-d',strtotime($valor))."'"; 
                else:
                    if(in_array ($campo, $arrEnteros))://========esta en enteros 
                        if(!$valor || empty($valor)){$valor = 0;}
                        $query = "$campo = $valor";
                    else:                              //========esta en cadenas
                        $valor = str_replace("'",'', $valor);
                        $query = "$campo = '$valor'";//==========================set inicial
                    endif;  
                endif; 
            endif;
            ++$i;
        endif;
    }
    $query = "update  $tabla set $query";
     if(!$avoidcontrol){
        $query .= ",idusuariomodif = $idusuario_session, fmodif = now()";
     }
    $query .=  "where id = $idr";  
    //echo $query; die(); //verificar el query
    return $query;
    
}
function listar_tabla($columnas,$config){
    $condicion = $config['condicion'];
    $tabla =  $config['tabla'];
    $returnpageAdd = $config['returnpageAdd'];
    $beforeupdate= $config['beforeupdate'];
    $mysqli = new _mysqli();
    $result = $mysqli->resultN($tabla,$condicion);    
    echo '<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered dataTable no-footer" id="table-list">';
    echo '<thead><tr>';
    foreach ($columnas as $key => $value) {
        echo '<th style="text-align:center;">'.mb_strtoupper($key).'</th>';
    }
    echo '<th style="text-align:center;">STATUS</th>'
    . '<th style="text-align:center;">EDIT</th>'
    . '<th style="text-align:center;">-</th></tr></thead>';
    echo '<tbody>';
    while($arr = mysqli_fetch_array($result,MYSQLI_ASSOC)):
        $trclass = '';   
        $tdclass = '';   
        $id = $arr['id'];
        $idencrypted = encrypt( $id);
        $status = isset($arr['status'])?$arr['status']:'activo';
        if($status == 'inactivo'){
            $trclass = 'warning';//definir si row o cell
            $tdclass = 'warning';//definir si row o cell
        }
        echo '<tr class="'.$trclass.'">';
        foreach ($columnas as $value) {
            echo '<td class="'.$tdclass.'">'.($arr[$value]).'</td>';     
        }        
        echo '<td class="'.$tdclass.'">'.$arr['status'].'</td>';
        
        
        
        echo '<td style="text-align:center;width:100px;">
                '.editbuttons($status, $idencrypted,$returnpageAdd,$beforeupdate).'
                '.editbuttons('edit', $idencrypted,$returnpageAdd).'
              </td>';
        echo '<td style=""><input type="checkbox" class="check-lote" value="'.$id.'" /></td>';
    endwhile;
    echo '</tbody>';
    echo '</table>';    
}
function existeCampo(){
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];
    $tipo = $_POST['tipo'];   
    if($tipo != 'int')
        $valor= "'".$valor."'";    
    $tabla = $_POST['tabla'];
    $mysqli = new _mysqli();
    $condicion = "where $campo = $valor";
    $arr = $mysqli->result1($tabla, $condicion);
    if($arr)
        echo $arr['id'];
    else
        echo '';
}
function returnRegistro(){
    $id = $_POST['id'];    
    $tabla = $_POST['tabla'];    
    $mysqli = new _mysqli();        
    $arr = $mysqli->result1($tabla, "where id = '$id'");   
    echo json_encode($arr);
}
function editbuttons($status,$id,$link,$beforeupdate = ''){
    $button = '';    
    switch ($status) {
        case 'activo':
        case 'inactivo':
            if($status === 'inactivo'):
                $status = 'activo';            
                $buttontype = 'btn btn-success btn-icon-only';
                $title = 'Activar Registro';
                $buttontype2 = 'glyphicon glyphicon-ok';
            else:
                $status = 'inactivo';
                $buttontype = 'btn btn-warning btn-icon-only';
                $title = 'Inactivar Registro';
                $buttontype2 = 'glyphicon glyphicon-remove';            
            endif;
            $button = '<button class="btn btn-warning btn-icon-only" title="'.$title.'" type="button" onclick="editarStatusRegistro(\''.$id.'\',\''.$link.'\',\''.$status.'\',\''.$beforeupdate.'\');">'
                    . '<span class="'.$buttontype2.'"></span><span class="sr-only">Edit</span>'
                    . '</button>';
            break;                    
        case 'edit':
            $button = '<a class="btn btn-primary btn-icon-only" title="Editar Registro" href="'.$link.'?idr='.$id.'"><span class="glyphicon glyphicon-edit"></span></a>';
            break;        
        case 'editimg':
            $button = '<a class="btn btn-primary btn-icon-only" title="Editar Imagenes del Registro" href="'.$link.'?idr='.$id.'"><span class="glyphicon glyphicon-picture"></span></a>';
            break;
    }
    return $button;
}
function cambiarStatus($accion){
   $id = $_POST['id'];
   $tabla = $_POST['tabla'];    
   $status = $accion;
   $beforeupdate = isset($_POST['beforeupdate'])?$_POST['beforeupdate']:'';
   try {
        $mysqli = new _mysqli();
        $query = "update $tabla set status = '$status' where id = $id";
        mysqli_query($mysqli->conn, $query);
        if($beforeupdate){
            call_user_func($beforeupdate,$id,$accion); //llamar a una funcion despues de activar o desactivar un registro   
        }
        echo alertaBoostrap('<p>El status del registro ha sido cambiado a <b>'.$status.'!</b></p>','-success',false);
   } catch (Exception $exc) {
        exit(alertaBoostrap('Error al intentar cambiar el status el registro: <p>'.$exc->getTraceAsString().'</p>','-danger',false));          
   } 
}
function saveimg($mysqli,$dirname,$tablaimg,$idr,$idcolumname,$columname = 'imagen',$postname='img',$postdelimg = 'delimg',$mascara = false){    
        $config = array(
          'mysqli'=>$mysqli,
          'dirname' => $dirname,
          'tablaimg' =>$tablaimg,
          'idr'=>$idr,
          'idcolumname'=>$idcolumname,
          'columname' => $columname,
          'postname' => $postname,
          'postdelimg' =>$postdelimg,
          'mascara' => $mascara
    );
    saveimg_v2($config);
    /**
    if(isset($_POST[$postdelimg]) && $_POST[$postdelimg]){
        $img = $_POST[$postdelimg];
        if(file_exists("$dirname/$img"))
            unlink("$dirname/$img");
        mysqli_query($mysqli->conn, "update $tablaimg set $columname = '' where $columname = '$img' and $idcolumname = $idr ");        
    }
    $img = isset($_POST[$postname])?$_POST[$postname]:null;
    if($img){
        $filename = null;
        $extension = null;
        $tmpfile =file_exists("tmp_uploads/$img")?"tmp_uploads/$img":null;
        if($tmpfile){
            $extension = pathinfo($tmpfile,PATHINFO_EXTENSION);
            $filename = $columname."_"."$idr.$extension";   
            $filepath  = "$dirname/$filename";
            if(!rename($tmpfile, $filepath)){
                $filename = false; 
            }else if($mascara)aplicar_mascara($filepath);            
        }
        if($filename){
            if(!$mysqli->executeSQL("update $tablaimg set $columname = '$filename' where $idcolumname = $idr ")){
                echo mysqli_error($mysqli->conn);    
            }                        
        } 
    }
    /**/
}
/*
 * 
 * 
 */
function saveimg_v2($config){    
    $mysqli = prepareVar_v2( $config,'mysqli');
    $dirname = prepareVar_v2( $config,'dirname');
    $tablaimg = prepareVar_v2( $config,'tablaimg');
    $idr = prepareVar_v2( $config,'idr');
    $idcolumname=prepareVar_v2( $config,'idcolumname');
    $columname =prepareVar_v2( $config,'columname','imagen');
    $postname= prepareVar_v2( $config,'postname','img');
    $postdelimg = prepareVar_v2( $config,'postdelimg','delimg');
    $mascara = prepareVar_v2( $config,'mascara',false);
    
    if(isset($_POST[$postdelimg]) && $_POST[$postdelimg]){
        $img = $_POST[$postdelimg];
        if(file_exists("$dirname/$img"))
            unlink("$dirname/$img");
        mysqli_query($mysqli->conn, "update $tablaimg set $columname = '' where $columname = '$img' and $idcolumname = $idr ");        
    }
    $img = isset($_POST[$postname])?$_POST[$postname]:null;
    if($img){
        $filename = null;
        $extension = null;
        $tmpfile =file_exists("tmp_uploads/$img")?"tmp_uploads/$img":null;
        if($tmpfile){
            $extension = pathinfo($tmpfile,PATHINFO_EXTENSION);
            $filename = $columname."_"."$idr.$extension";   
            $filepath  = "$dirname/$filename";
            if(!rename($tmpfile, $filepath)){
                $filename = false; 
            }else if($mascara)aplicar_mascara($filepath);            
        }
        if($filename){
            if(!$mysqli->executeSQL("update $tablaimg set $columname = '$filename' where $idcolumname = $idr ")){
                echo mysqli_error($mysqli->conn);    
            }                        
        } 
    }
}
function saveimgs($mysqli,$dirname,$tablaimg,$idregistro,$idcolumname,$columname = 'imagen',$postname='imgs',$postdelimgs = 'delimgs',$mascara = false){
    $config = array(
          'mysqli'=>$mysqli,
          'dirname' => $dirname,
          'tablaimg' =>$tablaimg,
          'idr'=>$idregistro,
          'idcolumname'=>$idcolumname,
          'columname' => $columname,
          'postname' => $postname,
          'postdelimg' =>$postdelimgs,
          'mascara' => $mascara,
          'limpiar' => true
    );
    saveimgs_v2($config);
    
    /**
    $returnmsj = isset($_POST['returnmsj'])?$_POST['returnmsj']:false;
    //GUARDAR IMAGENES
    if(isset($_POST[$postdelimgs]) && $_POST[$postdelimgs]){
        $imgs = $_POST[$postdelimgs];
        foreach ($imgs as $img) {
            if(file_exists("$dirname/$img"))
                unlink("$dirname/$img");
            mysqli_query($mysqli->conn, "update $tablaimg set $columname = '' where $columname = '$img' ");
        }        
    }
    if(isset($_POST[$postname])){
        $imgs = $_POST[$postname];
        foreach ($imgs as $img) {
            if(file_exists("tmp_uploads/$img")):
                $filename = "$dirname/$img"; 
                if($img && rename("tmp_uploads/$img", $filename)){
                    if($mascara)aplicar_mascara($filename);
                    $mysqli->insertar($tablaimg,"$idcolumname,$columname", "$idregistro,'$img'",false);   
                }else{
                    if($returnmsj == 'mensajes' || $returnmsj == 'panelAdmin')
                        echo alertaBoostrap('<p>Hubo un error al intentar subir una imagen!</p>', '-warning');   
                }                
            endif;
        }                
    }
    mysqli_query($mysqli->connect(), "delete from $tablaimg where $columname = '' or $columname is NULL");  
    /**/
}
function saveimgs_v2($config){
    $mysqli = prepareVar_v2( $config,'mysqli');
    $dirname = prepareVar_v2( $config,'dirname');
    $tablaimg = prepareVar_v2( $config,'tablaimg');
    $idr = prepareVar_v2( $config,'idr');
    $idcolumname=prepareVar_v2( $config,'idcolumname');
    $columname =prepareVar_v2( $config,'columname','imagen');
    $postname= prepareVar_v2( $config,'postname','img');
    $postdelimg = prepareVar_v2( $config,'postdelimg','delimg');
    $mascara = prepareVar_v2( $config,'mascara',false);
    $limpiar = prepareVar_v2( $config,'limpiar',false);
   
    
    if(isset($_POST[$postdelimg]) && $_POST[$postdelimg]){
        $imgs = $_POST[$postdelimg];
        foreach ($imgs as $img) {
            if(file_exists("$dirname/$img"))
                unlink("$dirname/$img");
            mysqli_query($mysqli->conn, "update $tablaimg set $columname = '' where $columname = '$img' and $idcolumname = $idr ");
        }        
    }    
    $imgs = isset($_POST[$postname])?$_POST[$postname]:null;
    if($imgs){
        $filename = null;
        $extension = null;        
        foreach ($imgs as $img) {
            $tmpfile =file_exists("tmp_uploads/$img")?"tmp_uploads/$img":null;
            if($tmpfile){
                $extension = pathinfo($tmpfile,PATHINFO_EXTENSION);
                $filename = $columname."_$idr"."_".sha1(microtime()).".".$extension;   
                
                $filepath  = "$dirname/$filename";
                if(!rename($tmpfile, $filepath)){
                    $filename = false; 
                }else if($mascara)aplicar_mascara($filepath);            
            }
            if($filename){
                if(!$mysqli->insertar($tablaimg,"$idcolumname,$columname", "$idr,'$filename'",false)){
                //if(!$mysqli->executeSQL("update $tablaimg set $columname = '$filename' where $idcolumname = $idr ")){
                    echo mysqli_error($mysqli->conn);    
                }                        
            }
        }
    }
    if($limpiar)mysqli_query($mysqli->connect(), "delete from $tablaimg where $columname = '' or $columname is NULL");   
}
function guardarArchivo($mysqli,$id,$tabla,$campo,$carpeta,$name = 'file',$inputfilename = '',$tam = 9999999,$watermark = false){       //1MB = 1048576       //3MB = 3145728
    if(empty($inputfilename ))
        $inputfilename = $campo;   
    if(!isset($_FILES[$inputfilename]))
        return;
    $error = $_FILES[$inputfilename]["error"];
    $tipo = $_FILES[$inputfilename]["type"];        
    $arrPermitidos = array('image/gif','image/jpeg','image/jpg','image/pjpeg','image/png','image/x-png');        
    //$arrPermitidos = array('application/pdf');        
    $tipo_permitido = in_array($tipo,$arrPermitidos);
    /**
    ini_set("display_startup_errors", "1");
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
    echo "<pre>";
    echo "POST:";
    print_r($_POST);
    echo "FILES:";
    print_r($_FILES);
    echo "</pre>";                    
    /**/    
    $tam_permitido = true;//($_FILES[$inputfilename]["size"] < $tam);
    
    if ($error != 4){        
        if ($tipo_permitido){
            if($tam_permitido){
                if ($error > 0){            
                    echo(alertaBoostrap('<p>Error al intentar guardar el archivo '.$name.'</p>'.'<p>'.$error.'</p>','-danger'));
                }else{                     
                    eliminarArchivo($mysqli, $id, $tabla, $campo, $carpeta);
                    $extension  = '.'.strtolower(pathinfo($_FILES[$inputfilename]["name"], PATHINFO_EXTENSION));
                    $filename = limpiarcadena("$carpeta/".$name.$extension);                    
                    if(move_uploaded_file($_FILES[$inputfilename]["tmp_name"],$filename)){                                                
                        if($watermark){               
                            $width_ = 670;
                            $height_ =521;                             
                            $img_src = imagecreatefromfile($filename);
                            $img_src_w = imagesx($img_src);
                            $img_src_h = imagesy($img_src);
                            $diffh = $img_src_h - $height_ ;
                            $diffw = $img_src_w - $width_;
                            if($diffh < 0 && $diffw < 0 ){
                                //la imagen es mas pequeña en ambos lados, por tanto solo se centra horizontal y verticamente
                                $dst_image = $img_src;
                            }else if($diffh > $diffw){
                                //el alto es mas grande que el ancho por tanto el porcentaje se basa en el alto
                                $percent = $height_ / $img_src_h;
                                $tmpw = $img_src_w * $percent;
                                $dst_image = imagecreatefromfile('../img/site/base.jpg');
                                imagecopyresampled($dst_image, $img_src ,($width_-$tmpw)/2,0,0,0, $tmpw, $height_, $img_src_w, $img_src_h);                                                                
                            }else{    
                                //el ancho es mas grande que el alto por tanto el porcentaje se basa en el ancho
                                $percent = $width_ / $img_src_w;
                                $tmph = $img_src_h * $percent;                                                                
                                $dst_image = imagecreatefromfile('../img/site/base.jpg');
                                imagecopyresampled($dst_image, $img_src , 0,($height_-$tmph)/2, 0, 0, $width_, $tmph, $img_src_w, $img_src_h);                                                                  
                            }                                                        
                            $w = imagesx($dst_image);
                            $h = imagesy($dst_image);
                            $img_base = imagecreatetruecolor($width_,$height_);
                            imagecopy($img_base,$dst_image,($width_-$w)/2,($height_-$h)/2,0,0,$width_,$height_);
                            imagefill($img_base,0,0,imagecolorallocate($img_base, 0xff,0xff,0xff)); // fill the background with white                                                        
                            $mascara = imagecreatefromfile('../img/site/mascaracventas4.png');                           
                            imagecopy($img_base,$mascara,0,0,0,0,$width_,$height_);                            
                            imagejpeg($img_base,$filename);                               
                        }
                        $mysqli->update($campo,"'$filename'", $tabla, "where id = $id",false);
                        //$mysqli->cerrar();
                    }                                
                }
            }else
                echo(alertaBoostrap('<p>Error subiendo el archivo '.$_FILES[$inputfilename]["name"].', tama&ntilde;o de archivo no permitido</p>','-danger'));  
        }else
            echo(alertaBoostrap('<p>Error subiendo el archivo '.$_FILES[$inputfilename]["name"].', tipo de archivo no permitido</p>','-danger'));  
    }else{ 
        //no se subio ningun archivo
        return true;
    }
    return false;
}
function eliminarArchivo($mysqli,$id,$tabla,$campo,$carpeta){        
    $arr = $mysqli->result1($tabla,'where id = '.$id,$campo,false);
    $filename = $arr[$campo];
    if($filename){
        $filename = "$carpeta/".$arr[$campo];
        try {
            if(file_exists($filename))
                if(!unlink($filename))
                    echo(alertaBoostrap('<p>Error al intentar eliminar el archivo '.$campo.' </p>','-danger'));  
        }catch (Exception $exc) {}        
        if(!$mysqli->update($campo,"''", $tabla, "where id = $id",false)):                        
                echo(alertaBoostrap('<p>Error al intentar desligar el archivo '.$campo.' del registro -> '.mysqli_error($mysqli->conn).'</p>','-danger'));  
        endif;
    }        
}
function imagecreatefromfile( $filename ) {
   if (base64_decode($filename, true)) {
        return imagecreatefromstring($filename);
    }else if (!file_exists($filename)) {
        throw new InvalidArgumentException('File "'.$filename.'" not found.');
    }
    switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
        case 'jpeg':
        case 'jpg': return imagecreatefromjpeg($filename);
        case 'png': return imagecreatefrompng($filename);
        case 'gif': return imagecreatefromgif($filename);
        default: throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');  
    }
}
function fixArrayOfCheckboxes( $checks ) {
    $newChecks = array();
    $max = count($checks);
    for( $i = 0; $i < $max; $i++ ) {
        if( $checks[ $i ] == 'no' && ($i+1 < $max) && $checks[ $i + 1 ] == 'si' ) {
            $newChecks[] = 'si';
            $i++;
        }else{
            $newChecks[] = 'no';
        }
    }
    return $newChecks;
}
function limpiarcadena($String){
    $String = str_replace(array('á','à','â','ã','ª','ä'),'a',$String);
    $String = str_replace(array('Á','À','Â','Ã','Ä'),'A',$String);
    $String = str_replace(array('Í','Ì','Î','Ï'),'I',$String);
    $String = str_replace(array('í','ì','î','ï'),'i',$String);
    $String = str_replace(array('é','è','ê','ë'),'e',$String);
    $String = str_replace(array('É','È','Ê','Ë'),'E',$String);
    $String = str_replace(array('ó','ò','ô','õ','ö','º'),'o',$String);
    $String = str_replace(array('Ó','Ò','Ô','Õ','Ö'),'O',$String);
    $String = str_replace(array('ú','ù','û','ü'),'u',$String);
    $String = str_replace(array('Ú','Ù','Û','Ü'),'U',$String);
    $String = str_replace(array('[','^','´','`','¨','~',']'),"",$String);
    $String = str_replace('ç','c',$String);
    $String = str_replace('Ç','C',$String);
    $String = str_replace('ñ','n',$String);
    $String = str_replace('Ñ','N',$String);
    $String = str_replace('Ý','Y',$String);
    $String = str_replace('ý','y',$String);
    return $String;
}
function endfunction($mysqli,$accion,$idr,$returnpageAdd,$returnpageList,$returnpageImg = ""){
    $mysqli->cerrar();
    if($accion == 'guardar' || $accion == 'modif'):               
        $returnpageModif =  $returnpageAdd."?idr=".encrypt($idr);
    if($returnpageImg)
        $returnpageImg = '| <a href="'.$returnpageImg."?idr=".$idr.'">Agregar Im&aacute;genes</a>';
        
        echo alertaBoostrap('<p><a href="'.$returnpageAdd.'">A&ntilde;adir otro</a> | <a href="'.$returnpageModif.'">Modificar Registro</a> '.$returnpageImg.' | <a href="'.$returnpageList.'">Listar Registros</a></p>','-info');
    endif;    
}
function encriptar_algo(){    
    $action = isset($_POST['action'])?$_POST['action']:'encrypt';
    $dato = isset($_POST['dato'])?$_POST['dato']:'';
    echo encrypt_decrypt($action,$dato);
}
function eliminar_lote(){    
    $tabla = $_POST['tabla'];
    $iditems = explode(',',$_POST['iditems']);
    foreach ($iditems as $id) {
        if($id){
            $mysqli = new _mysqli();
            $query = "update $tabla set status = 'inactivo' where id = $id";
            mysqli_query($mysqli->conn, $query);            
        }
    }
}
function condicion_lote(){    
    $tabla = $_POST['tabla'];
    $iditems = explode(',',$_POST['iditems']);
    $condicion = $_POST['condicion'];
    foreach ($iditems as $id) {
        if($id){
            $mysqli = new _mysqli();
            $query = "update $tabla set condicion = '$condicion' where id = $id";
            mysqli_query($mysqli->conn, $query);            
        }
    }
}
function aplicar_mascara($filename,$dirbase = 'img/base.jpg',$dirmascara='img/mascara.png'){            
    //-------------------------------------------//
    $dst_image = imagecreatefromfile($dirbase);
    $width_ = imagesx($dst_image);
    $height_= imagesy($dst_image);
    //-------------------------------------------//
    
    $img_src = imagecreatefromfile($filename);
    $img_src_w = imagesx($img_src);
    $img_src_h = imagesy($img_src);
    $diffh = $img_src_h - $height_ ;
    $diffw = $img_src_w - $width_;
    if($diffh < 0 && $diffw < 0 ){
        //la imagen es mas pequeña en ambos lados, por tanto solo se centra horizontal y verticamente
        $dst_image = $img_src;
    }else if($diffh > $diffw){
        //el alto es mas grande que el ancho por tanto el porcentaje se basa en el alto
        $percent = $height_ / $img_src_h;
        $tmpw = $img_src_w * $percent;
        imagecopyresampled($dst_image, $img_src ,($width_-$tmpw)/2,0,0,0, $tmpw, $height_, $img_src_w, $img_src_h);                                                                
    }else{    
        //el ancho es mas grande que el alto por tanto el porcentaje se basa en el ancho
        $percent = $width_ / $img_src_w;
        $tmph = $img_src_h * $percent;                                                                
        imagecopyresampled($dst_image, $img_src , 0,($height_-$tmph)/2, 0, 0, $width_, $tmph, $img_src_w, $img_src_h);                                                                  
    }                                                        
    $w = imagesx($dst_image);
    $h = imagesy($dst_image);
    $img_base = imagecreatetruecolor($width_,$height_);
    imagecopy($img_base,$dst_image,($width_-$w)/2,($height_-$h)/2,0,0,$width_,$height_);
    imagefill($img_base,0,0,imagecolorallocate($img_base, 0xff,0xff,0xff)); // fill the background with white                                                        
    $mascara = imagecreatefromfile($dirmascara);                           
    imagecopy($img_base,$mascara,0,0,0,0,$width_,$height_);                            
    imagejpeg($img_base,$filename);
    return $filename;
}
function savemultimedia($mysqli,$dirname,$tablafile,$idregistro,$idcolumname,$columname = 'imagen',$postname='img',$postdelimg = 'delimg'){
    $returnmsj = isset($_POST['returnmsj'])?$_POST['returnmsj']:false;
    //BORRAR
    if(isset($_POST[$postdelimg]) && $_POST[$postdelimg]){
        $img = $_POST[$postdelimg];
        if(file_exists("$dirname/$img"))
            unlink("$dirname/$img");
        mysqli_query($mysqli->conn, "update $tablafile set $columname = '' where $columname = '$img' ");        
    }
    /***/
    
    //INSERTAR
    if(isset($_POST[$postname])){
        $img = $_POST[$postname];
        if(file_exists("tmp_uploads/$img")){
            $filename = "$dirname/$img";            
            if($img && rename("tmp_uploads/$img", $filename)){
                aplicar_mascara($filename);
                 if(!$mysqli->update($columname,"'$img'",$tablaimg,"where $idcolumname = $idregistro"))
                    echo alertaBoostrap('<p>Hubo un error al intentar subir una imagen!</p>', '-warning');
            }else{
                if($returnmsj == 'mensajes' || $returnmsj == 'panelAdmin')
                    echo alertaBoostrap('<p>Hubo un error al intentar subir una imagen!</p>', '-warning');
            }            
        }
    }
}