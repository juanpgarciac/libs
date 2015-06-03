<?php include_once '../_includes.php';?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <?php
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
        $lang = array('es','en','it','fr','de','pt');
        $file = fopen('_lang.ini','wb');
        $result = $mysqli->resultN('tbllang','order by indice');
        while ($arr = mysqli_fetch_assoc($result)) {
            $indice = $arr['indice'];
            foreach ($lang as $value) {
                $texto = addslashes(str_replace(array("\r\n", "\r", "\n"),"",$arr['texto_'.$value]));
                $index = mb_strtoupper("MSJ_$indice"."_$value");
                $line = "$index = \"$texto\";"."\r\n";
                fwrite($file,$line);
                echo "<small>$line</small><br>";
            }    
        }
        fclose($file);
        ?>
    </body>
</html>




