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
    $idusuario_session = $idusuario_session?$idusuario_session:0;    
    $i = 0;
    $valores = null;
    $campos = null;
    foreach ($_POST as $campo => $valor){       
        $valor = ($valor)?addslashes(trim($valor)):null;
        if(in_array($campo,$arrEnteros)&&!$valor)$valor = 0;
        if($campo === 'funcion' || $campo === 'accion')
            break;  
        if(!in_array ($campo, $arrIgnorar)):
            if($i):
                $campos .= ','.$campo;            
                if(in_array ($campo, $arrFechas))://==============esta en fechas
                     $valores .= ",'".date('Y-m-d',strtotime($valor))."'";  
                else:
                    $valores .= ",'$valor'";
                endif;
            else://primer valor                    
                if(in_array ($campo, $arrFechas))://==============esta en fechas
                    $valores = "'".date('Y-m-d',strtotime($valor))."'";  
                else:
                    $valores .= "'$valor'";
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
    if(DEBUGME)echo $campos.'<br>'.$valores;;//verificar el query
}
function prepareUpdate($tabla,$idr,$arrIgnorar,$arrFechas,$arrEnteros,$avoidcontrol = false){
    global $idusuario_session;
    $idusuario_session = $idusuario_session?$idusuario_session:0; 
    $i = 0;
    foreach ($_POST as $campo => $valor){
        $valor = ($valor)?addslashes(trim($valor)):null;
        if(in_array($campo,$arrEnteros)&&!$valor)$valor = 0;
        if(!in_array ($campo, $arrIgnorar))://=======================ignorar 
            if($campo === 'funcion' || $campo === 'accion')
                break;   
            if($i):                                    
                if(in_array ($campo, $arrFechas))://==============esta en fechas
                    $query .= ",$campo = '".date('Y-m-d',strtotime($valor))."'"; 
                else:
                    $query .= ",$campo = '$valor'";
                endif;
            else:
               if(in_array ($campo, $arrFechas))://==============esta en fechas
                    $query = "$campo = '".date('Y-m-d',strtotime($valor))."'"; 
                else:
                    $query = "$campo = '$valor'";
                endif; 
            endif;
            ++$i;
        endif;
    }
    $query = "update  $tabla set $query";
     if(!$avoidcontrol){
        $query .= ",idusuariomodif = $idusuario_session, fmodif = now()";
     }
    $query .=  " where id = $idr";  
    if(DEBUGME)echo $query;//verificar el query
    return $query;  
}
function prepareInsert_v2($config){
    $tabla = prepareVar_v2($config, 'tabla');
    $condicion= prepareVar_v2($config, 'condicion');
    $accion = prepareVar_v2($config, 'accion');
    $arrignorar = prepareVar_v2($config,'arrignorar',array());
    $avoidcontrol = prepareVar_v2($config,'avoidcontrol');
    $glue = prepareVar_v2($config,'glue',',');    
    $valores = $campos = array();
    $contentArray = prepareVar_v2($config,'contentArray',$_POST);
    if($tabla){
        $mysqli = new _mysqli;
        $result = mysqli_query($mysqli->conn, "SHOW COLUMNS FROM $tabla;");
        while ($arr = mysqli_fetch_assoc($result)) {            
            $tmpcampo = isset($contentArray[$arr['Field']])?$arr['Field']:null; //&&$contentArray[$arr['Field']]
            if($tmpcampo&&!in_array($tmpcampo,$arrignorar)){
                $tmpvalor = isset($contentArray[$tmpcampo])&&$contentArray[$tmpcampo]?$contentArray[$tmpcampo]:$arr['Default'];
                if(strpos($arr['Type'],'int')!== false){
                    $tmpvalor = ($tmpvalor)?$tmpvalor:0;
                }else if(strpos($arr['Type'],'varchar')!== false || strpos($arr['Type'],'text')!==false || strpos($arr['Type'],'enum')!==false){
                    if (is_array($tmpvalor))//si es un arreglo debo desplegarlo
                        $tmpvalor = implode ($glue, $tmpvalor);   
                    $tmpvalor = ($tmpvalor)?"'".addslashes(trim($tmpvalor))."'":'NULL';
                }else if(strpos($arr['Type'],'date')!==false){
                    if($tmpvalor !== 'now()')
                        $tmpvalor = "'".date('Ymd',strtotime($tmpvalor))."'";
                }else if(strpos($arr['Type'],'decimal')!==false){
                    $tmpvalor = ($tmpvalor)?"'".($tmpvalor)."'":0;
                }
                if($accion == 'insert'||$accion == 'guardar'){
                    array_push($valores, $tmpvalor);    
                    array_push($campos, $tmpcampo);        
                }else{
                    array_push($campos, "$tmpcampo = $tmpvalor"); 
                }
            }            
        }        
    }
    $query = null;
    global $idusuario_session;
    $idusuario_session = $idusuario_session?$idusuario_session:0; 
    if($accion == 'insert'||$accion == 'guardar'){        
        if(!$avoidcontrol)
            $query = "insert into $tabla(".implode(',', $campos).",idusuariocreador,fcreacion) values(".implode(',', $valores).",$idusuario_session,now())";
        else 
            $query = "insert into $tabla(".implode(',', $campos).") value(".implode(',', $valores).")";        
    }else{//si es update
       if($condicion){//evitemos una catastrofe
            if(!$avoidcontrol)
                $query = "update  $tabla set ".implode(',', $campos).",idusuariomodif = $idusuario_session,fmodif=now() $condicion";
            else $query = "update  $tabla set ".implode(',', $campos)." $condicion";   
       }
    }
    return $query;
}
function listar_tabla($columnas,$config){
    $campotitulo = prepareVar_v2($config,'campotitulo');
    $condicion = prepareVar_v2($config,'condicion');
    $campos = prepareVar_v2($config,'campos','*');
    $tabla =  prepareVar_v2($config,'tabla');
    $returnpageAdd = prepareVar_v2($config,'returnpageAdd');
    $beforeupdate= prepareVar_v2($config,'beforeupdate');
    $fancyimg = prepareVar_v2($config,'fancyimg');
    $fancyimgdir = prepareVar_v2($config,'fancyimgdir');
    $fancyimgtitle = prepareVar_v2($config,'fancyimgtitle');
    $mysqli = new _mysqli();
    $result = $mysqli->resultN($tabla,$condicion,$campos);
    $titulo = 'selected record';
    echo '<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered dataTable no-footer" id="table-list" style="font-size:smaller;">';
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
            if($fancyimg == $value)
                echo '<td class="'.$tdclass.'">'
                    . '<a href="'.$fancyimgdir.'/'.$arr[$value].'" class="fancybox" title="'.$arr[$fancyimgtitle].'">'
                    . '<img src="'.$fancyimgdir.'/'.$arr[$value].'"  style="max-width:80px;max-height:80px;"/>'
                    . '</a>'
                    . '</td>';     
            else{
                echo '<td class="'.$tdclass.'">'.($arr[$value]).'</td>';     
            } 
            if($campotitulo == $value)    
                $titulo = $arr[$value];  
        }        
        echo '<td class="'.$tdclass.'">'.$status.'</td>';
        echo '<td style="text-align:center;width:100px;">
                '.editbuttons($status, $idencrypted,$titulo,$beforeupdate).'
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
   $id = decrypt($_POST['id']);
   $tabla = ($_POST['tabla']);    
   if(!$id || !$tabla)die('Error de DATA POST');
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
function savemultimedia($config){    
    $mysqli = new _mysqli;
    
    $idpadre = prepareVar_v2($config, 'idpadre');
    $idr = prepareVar_v2($config, 'idr',$idpadre);

    $dirname = prepareVar_v2($config, 'dirname');
    $tabla = prepareVar_v2($config, 'tabla');
    
    $multimedia_id = prepareVar_v2($config,'multimedia_id');
    $multimedia_archivo = prepareVar_v2($config,'multimedia_archivo');
    $multimedia_borrar = prepareVar_v2($config,'multimedia_borrar');
    $multimedia_titulo = addslashes(trim(prepareVar_v2($config,'multimedia_titulo')));
    $multimedia_descripcion = addslashes(trim(prepareVar_v2($config,'multimedia_descripcion')));

    $columa_id      = prepareVar_v2($config, 'columna_id','id');
    $columa_idpadre      = prepareVar_v2($config, 'columna_idpadre');
    $columna_nombre = prepareVar_v2($config, 'columna_nombre');
    $columna_titulo = prepareVar_v2($config, 'columna_titulo');
    $columna_descripcion = prepareVar_v2($config, 'columna_descripcion');
    $columna_extension = prepareVar_v2($config, 'columna_extension');

    $es_archivo= prepareVar_v2($config, 'es_archivo',true);
    $forzar_tipo =prepareVar_v2($config, 'forzar_tipo');

    $mascara = prepareVar_v2( $config,'mascara',false);
    $limpiar = prepareVar_v2( $config,'limpiar',false);
    
    if($multimedia_borrar){
        if($es_archivo&&file_exists("$dirname/$multimedia_borrar"))unlink("$dirname/$multimedia_borrar");
        if(!$mysqli->executeSQL("update $tabla set $columna_nombre = NULL where $columa_id = $multimedia_borrar "))echo mysqli_error($mysqli->conn);       
    }else{
        if(!$multimedia_archivo){
            echo 'no file indicated';
            return;
        } 
        $filename = null;
        $extension = null;
        if($es_archivo){
            if(!file_exists("$dirname/$multimedia_archivo")){
                $tmpfile =file_exists(TMPUPLOADS."/$multimedia_archivo")?TMPUPLOADS."/$multimedia_archivo":null;
                if($tmpfile){//si existe en los temporales es un insert 
                    $extension = pathinfo($tmpfile,PATHINFO_EXTENSION);
                    $filename = "$columna_nombre-$idpadre-".sha1(microtime()).".$extension";
                    $filepath  = "$dirname/$filename";
                    if(rename($tmpfile, $filepath)){ 
                        if($mascara)aplicar_mascara($filepath); 
                    }else $filename = null;
                }
            }else{
                $filename = $multimedia_archivo;                 
            }            
        }else{            
            $extension = $forzar_tipo;
            $filename = $multimedia_archivo; 
        }
        if($columa_idpadre&&$idpadre){//es una tabla exclusiva de multimedia
            if($multimedia_id){//es un update
                $columna_extension = $extension&&$columna_extension?",$columna_extension = '$extension' ":'';
                $mysqli->executeSQL("update $tabla set $columna_nombre = '$filename'$columna_extension where $columa_id = $multimedia_id ",true,true);       
            }else{//es un insert
                if($columna_extension)$multimedia_id =  $mysqli->insertar($tabla,"$columa_idpadre,$columna_nombre,$columna_extension", "$idpadre,'$filename','$extension'");
                else $multimedia_id = $mysqli->insertar($tabla,"$columa_idpadre,$columna_nombre", "$idpadre,'$filename'");                        
            }
        }else{//es una table mixta con multimedia inmerso en un campo
           $columna_extension = $extension&&$columna_extension?",$columna_extension = '$extension' ":'';
           $multimedia_id = $mysqli->executeSQL("update $tabla set $columna_nombre = '$filename'$columna_extension where $columa_idpadre = $idpadre ",true,true);                  
        }        
        if(!$multimedia_id){
            echo(mysqli_error($mysqli->conn));
            return;
        }
        if($columna_titulo&&$columna_descripcion){
            if(!$mysqli->executeSQL("update $tabla set $columna_titulo = '$multimedia_titulo',$columna_descripcion = '$multimedia_descripcion' where $columa_id = $multimedia_id "))echo mysqli_error($mysqli->conn);        
        }else{
            if($columna_titulo)if(!$mysqli->executeSQL("update $tabla set $columna_titulo = '$multimedia_titulo' where $columa_id = $multimedia_id"))echo mysqli_error($mysqli->conn);    
            if($columna_descripcion)if(!$mysqli->executeSQL("update $tabla set $columna_descripcion = '$multimedia_descripcion' where $columa_id = $multimedia_id"))echo mysqli_error($mysqli->conn);
        }           
    }
    //si limpiar entonces 
    if($limpiar)if(!$mysqli->executeSQL("delete from $tabla where $columna_nombre = '' or $columna_nombre is NULL"))echo mysqli_error($mysqli->conn);        
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
    /*
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
    /* */
}
function saveimg_v2($config){    
    //$mysqli = prepareVar_v2( $config,'mysqli');
    $mysqli = new _mysqli();
    $dirname = prepareVar_v2( $config,'dirname');
    $tablaimg = prepareVar_v2( $config,'tablaimg');
    $idr = prepareVar_v2( $config,'idr');
    $idcolumname=prepareVar_v2( $config,'idcolumname','id');
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
function saveimgs($mysqli,$dirname,$tablaimg,$idr,$idcolumname,$columname = 'imagen',$postname='imgs',$postdelimgs = 'delimgs',$mascara = false){
    $config = array(
          'mysqli'=>$mysqli,
          'dirname' => $dirname,
          'tablaimg' =>$tablaimg,
          'idr'=>$idr,
          'idcolumname'=>$idcolumname,
          'columname' => $columname,
          'postname' => $postname,
          'postdelimg' =>$postdelimgs,
          'mascara' => $mascara,
          'limpiar' => true
    );
    saveimgs_v2($config);
    
    /*
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
                    $mysqli->insertar($tablaimg,"$idcolumname,$columname", "$idr,'$img'",false);   
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
    /* *
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
function limpiarcadena($s){
    $replace = array(
        '?'=>'-', '?'=>'-', '?'=>'-', '?'=>'-',
        '?'=>'A', '?'=>'A', 'À'=>'A', 'Ã'=>'A', 'Á'=>'A', 'Æ'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ä'=>'Ae',
        'Þ'=>'B',
        '?'=>'C', '?'=>'C', 'Ç'=>'C',
        'È'=>'E', '?'=>'E', 'É'=>'E', 'Ë'=>'E', 'Ê'=>'E',
        '?'=>'G',
        '?'=>'I', 'Ï'=>'I', 'Î'=>'I', 'Í'=>'I', 'Ì'=>'I',
        '?'=>'L',
        'Ñ'=>'N', '?'=>'N',
        'Ø'=>'O', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe',
        '?'=>'S', '?'=>'S', '?'=>'S', 'Š'=>'S',
        '?'=>'T',
        'Ù'=>'U', 'Û'=>'U', 'Ú'=>'U', 'Ü'=>'Ue',
        'Ý'=>'Y',
        '?'=>'Z', 'Ž'=>'Z', '?'=>'Z',
        'â'=>'a', '?'=>'a', '?'=>'a', 'á'=>'a', '?'=>'a', 'ã'=>'a', '?'=>'a', '?'=>'a', '?'=>'a', 'å'=>'a', 'à'=>'a', '?'=>'a', '?'=>'a', '?'=>'a', '?'=>'a', '?'=>'a', 'ä'=>'ae', 'æ'=>'ae', '?'=>'ae', '?'=>'ae',
        '?'=>'b', '?'=>'b', '?'=>'b', 'þ'=>'b',
        '?'=>'c', '?'=>'c', '?'=>'c', '?'=>'c', 'ç'=>'c', '?'=>'c', '?'=>'c', '?'=>'c', '?'=>'c', '?'=>'c', '?'=>'c', '?'=>'ch', '?'=>'ch',
        '?'=>'d', '?'=>'d', '?'=>'d', '?'=>'d', '?'=>'d', '?'=>'d', '?'=>'d', 'ð'=>'d',
        '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', '?'=>'e', 'ê'=>'e', '?'=>'e', 'è'=>'e', 'ë'=>'e', 'é'=>'e',
        '?'=>'f', 'ƒ'=>'f', '?'=>'f',
        '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g', '?'=>'g',
        '?'=>'h', '?'=>'h', '?'=>'h', '?'=>'h', '?'=>'h', '?'=>'h', '?'=>'h', '?'=>'h',
        'î'=>'i', 'ï'=>'i', 'í'=>'i', 'ì'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'i', '?'=>'ij', '?'=>'ij',
        '?'=>'j', '?'=>'j', '?'=>'j', '?'=>'j', '?'=>'ja', '?'=>'ja', '?'=>'je', '?'=>'je', '?'=>'jo', '?'=>'jo', '?'=>'ju', '?'=>'ju',
        '?'=>'k', '?'=>'k', '?'=>'k', '?'=>'k', '?'=>'k', '?'=>'k', '?'=>'k',
        '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l', '?'=>'l',
        '?'=>'m', '?'=>'m', '?'=>'m', '?'=>'m',
        'ñ'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n', '?'=>'n',
        '?'=>'o', '?'=>'o', '?'=>'o', 'õ'=>'o', 'ô'=>'o', '?'=>'o', '?'=>'o', '?'=>'o', '?'=>'o', '?'=>'o', 'ø'=>'o', '?'=>'o', '?'=>'o', 'ò'=>'o', '?'=>'o', '?'=>'o', '?'=>'o', 'ó'=>'o', '?'=>'o', 'œ'=>'oe', 'Œ'=>'oe', 'ö'=>'oe',
        '?'=>'p', '?'=>'p', '?'=>'p', '?'=>'p',
        '?'=>'q',
        '?'=>'r', '?'=>'r', '?'=>'r', '?'=>'r', '?'=>'r', '?'=>'r', '?'=>'r', '?'=>'r', '?'=>'r',
        '?'=>'s', '?'=>'s', '?'=>'s', 'š'=>'s', '?'=>'s', '?'=>'s', '?'=>'s', '?'=>'s', '?'=>'s', '?'=>'sch', '?'=>'sch', '?'=>'sh', '?'=>'sh', 'ß'=>'ss',
        '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '?'=>'t', '™'=>'tm',
        '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', '?'=>'u', 'ü'=>'ue',
        '?'=>'v', '?'=>'v', '?'=>'v',
        '?'=>'w', '?'=>'w', '?'=>'w',
        '?'=>'y', '?'=>'y', 'ý'=>'y', 'ÿ'=>'y', 'Ÿ'=>'y', '?'=>'y',
        '?'=>'y', 'ž'=>'z', '?'=>'z', '?'=>'z', '?'=>'z', '?'=>'z', '?'=>'z', '?'=>'z', '?'=>'zh', '?'=>'zh'
    );
    return strtr($s, $replace);
    /**
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
    /* */
}
function endfunction($mysqli,$accion,$idr,$returnpageAdd,$returnpageList,$returnpageImg = ""){
    $mysqli->cerrar();
    if($accion == 'guardar' || $accion == 'modif'):
        $returnpageModif =  $returnpageAdd."?idr=".encrypt($idr);
        if($returnpageImg)$returnpageImg = '| <a href="'.$returnpageImg."?idr=".$idr.'">Agregar Im&aacute;genes</a>';
        echo alertaBoostrap('<p><a href="'.$returnpageAdd.'">A&ntilde;adir otro</a> | <a href="'.$returnpageModif.'">Modificar Registro</a> '.$returnpageImg.' | <a href="'.$returnpageList.'">Listar Registros</a></p>','-default');
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
function cleantmp(){
    $files = glob(TMPUPLOADS.'/*'); // get all file names
    foreach($files as $file){ // iterate files
      if(is_file($file))
        unlink($file); // delete file
    }
    echo 'done!';
}
function gettablecolumns($tabla){
    if($tabla){
        $mysqli = new _mysqli;
        $result = mysqli_query($mysqli->conn, "SHOW COLUMNS FROM $tabla;");
        while ($arr = mysqli_fetch_assoc($result)) {
            $GLOBALS[$arr['Field']] = null;
        }        
    }
}
function gettablerow($tabla,$idr,$idcolumn = 'id'){
    if($idr&&$tabla){
        $mysqli = new _mysqli;
        $arr = $mysqli->result1($tabla,"where $idcolumn = $idr");
        return $arr&&getarrvars($arr);
    }
    return null;
}
function gettablerow_condicion($tabla,$condicion=null,$campos="*"){
    if($tabla){
        $mysqli = new _mysqli;
        $arr = $mysqli->result1($tabla,$condicion,$campos);
        return $arr&&getarrvars($arr);
    }
    return null;
}
function getarrvars($arr = array()){
    if(is_array($arr)){
        foreach ($arr as $campo => $valor):/*Aquí asigno todas las variables con su respectivo par en la tabla */
            $GLOBALS[$campo] = $valor;
        endforeach; 
        return true;
    }else return null;
}
///==========================_funciones=========================================
function encrypt_decrypt($action, $string) {
    if(empty($string))return null;
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
function encrypt($string) {return encrypt_decrypt('encrypt',$string);}
function decrypt($string) { return encrypt_decrypt('decrypt',$string);}
/*
* tipo: -info,-success,-danger, vacio es warning
*/
function alertaBoostrap($mensaje,$tipo="-success",$container=false){

    $class = '';
    if($container){
        include_once 'bootstrap.html';
        $class = 'class="container" style="margin-top:20px;"';
    }
    $alerta = '<div '.$class.'><div class="alert alert'.$tipo.'" style="text-align:center;">'.$mensaje.'</div></div>';
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
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function displayMenu($modulo){
    global $gestion_session;
    global $nivel_session;
    $gestion = $gestion_session;
    if(!is_array($gestion))
        $gestion = explode (',',$gestion_session);
    
    if($nivel_session === 'admin' || ($gestion&&in_array($modulo,$gestion))) 
        return true;
    return false;
}
function displayModulo($modulos){
    foreach ($modulos as $modulo) {            
        if(displayMenu($modulo['gestion'])){
            echo '<li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$modulo['titulo'].'<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="'.$modulo['add'].'">Agregar '.$modulo['titulo'].'</a></li>
                        <li><a href="list.php?ta='.encrypt($modulo['tabla']).'&ti='.encrypt($modulo['titulo']).'">Listar '.$modulo['titulo'].' </a></li>
                    </ul>
                  </li>';
        }
    }
}
function init_time(){
    $mtime = microtime(); 
    $mtime = explode(" ",$mtime); 
    $mtime = $mtime[1] + $mtime[0]; 
    $starttime = $mtime; 
    return $starttime;
}
function end_time($starttime,$txttolog){
    $mtime = microtime(); 
    $mtime = explode(" ",$mtime); 
    $mtime = $mtime[1] + $mtime[0]; 
    $endtime = $mtime; 
    $totaltime = ($endtime - $starttime);
    $logfile = "execution_time_log";
    $fh = fopen($logfile, 'a');
    $ipclient = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
    $url = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
    $url .= isset($_SERVER['SERVER_PORT'])?":".$_SERVER['SERVER_PORT']:'';
    $url .= isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'';
    if($fh){
        fwrite($fh, 
                date('Y-m-d h:i:s:u').'; EXECUTION TIME: '.$totaltime.'; PROCESS: '.$txttolog.'; IP CLIENT: '.$ipclient.'; URL: '.$url
                ."\r\n");
        fclose($fh);        
    }

}
//============================================================================//