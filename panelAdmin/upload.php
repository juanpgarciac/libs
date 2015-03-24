<?php
/**/
if(isset($_FILES['file'])) {         
    if($_FILES['file']['error'] === 0){
        $extension  = '.'.strtolower(pathinfo($_FILES['file']["name"], PATHINFO_EXTENSION));
        $newname =  sha1(microtime()).$extension;// md5($_FILES['file']['name'].date('Ymdhs'))
        if(move_uploaded_file($_FILES['file']['tmp_name'], "tmp_uploads/".$newname)){  
           echo $newname;
        }
    }else{
        die('error');        
    }
}
/**/