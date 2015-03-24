<?php
define("PROJECTNAME",'');
define("PROJECTSALTKEY",PROJECTNAME.'KEY');
define("SESSIONACTIVE", sha1(PROJECTNAME.'-'.date('Ymd')));//la sesion debe estar controlada por al menos un día
define("DOMAINURL", 'http://');//CAMBIAR AL SUBIR ONLINE
define("SERVERHOST", '/');//CAMBIAR AL SUBIR ONLINE
define("DOCUMENTROOT", $_SERVER['DOCUMENT_ROOT']);
define("DEBUGME", false);
define("TMPUPLOADS", DOCUMENTROOT.SERVERHOST.'panelAdmin/tmp_uploads');
define("GLOBAL_LANGUAGE",'es');
define("DBUSER", 'root');
define("DBPASSWORD", '1234');
define("DBNAME", '');

