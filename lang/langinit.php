<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
include_once '../lib/_config.php';
include_once '../lib/_mysqli.php';
$mysqli = new _mysqli;

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
$files = array();
$files['js'] = fopen('_lang.js','w');
$result = $mysqli->resultN('tbllang','order by indice');
while ($arr = mysqli_fetch_assoc($result)) {
    $indice = $arr['indice'];
    foreach ($lang as $value) {
        $texto = addslashes ($arr['texto_'.$value]);
        //fwrite($files[$value],"MSJ_$indice = \"$texto\"\n");
        fwrite($files['js'],"MSJ_$indice"."_$value = \"$texto\";\n");
        echo "<small>$indice = $texto</small><br>";
    }
    
}
foreach ($files as $value) {
    fclose($value);
}
echo '<br>LISTO!';


