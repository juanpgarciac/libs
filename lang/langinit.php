<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
include_once '../_includes.php';
$mysqli = new _mysqli;
checksession('../panelAdmin/');
/*
insertar
$file = 'es.ini';
$arreglo = parse_ini_file($file);
foreach ($arreglo as $key => $value) {
    echo $key.' '.$value.'<br>';
    $mysqli->insertar('tbllang', 'indice,texto_es',"'$key','$value'");
}
*/

/*actualizar con ingles
$file = 'en.ini';
$arreglo = parse_ini_file($file);
foreach ($arreglo as $key => $value) {
    echo $key.' '.$value.'<br>';
    $mysqli->update('texto_en',"'$value'",'tbllang',"where indice = '$key'");
}
/**/

/*escribir archivos*/
$lang = array('es','en','it','fr','de','po');
$file = fopen('_lang.ini','w');
$result = $mysqli->resultN('tbllang','order by indice');
while ($arr = mysqli_fetch_assoc($result)) {
    $indice = $arr['indice'];
    foreach ($lang as $value) {
        $texto = addslashes($arr['texto_'.$value]);
        fwrite($file,"MSJ_$indice"."_$value = \"$texto\";\n");
        echo "<small>$indice = $texto</small><br>";
    }    
}
fclose($file);
echo '<br>LISTO!';


