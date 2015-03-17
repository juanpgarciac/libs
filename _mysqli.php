<?php
require_once 'config.php';
class _mysqli {
    //put your code here    
    private $host="localhost"; 
    //private $port=3306;
    //private $port="";
    //private $socket="";
    private $user="posadave_admin";
    private $password="nu54P<znjM";
    private $dbname="posadave_bd";
    public $conn;
    public $debug=false;
    
    
    
    
    
    public function __construct() {
        $this->getconfig();
        ini_set('mbstring.internal_encoding','UTF-8');
        date_default_timezone_set('America/Caracas');       
        $this->connect();
    }
    
    private function getconfig(){
        $config = db_config();
        $this->host = $config['host'];
        $this->user = $config['user'];
        $this->password= $config['password'];
        $this->dbname = $config['dbname'];        
    }
    
    function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname) or die ('No se pudo conectar con la BD ->' . mysqli_connect_error());
        $this->conn->query("SET NAMES 'UTF8'");        
        $this->conn->query("CHARACTER SET uft8 COLLATE utf8_general_ci;");    
        return $this->conn;
    }   
    //$con->close();
    
    function resultN($tabla,$condicion='',$campos = '*'){
        $this->connect();
        $query = "SELECT $campos FROM $tabla $condicion";
        if($this->debug)echo$query;
        $result = mysqli_query($this->conn,$query);        
        return $result;            
    }
    function result1($tabla,$condicion='',$campos = '*',$cerrar = true,$resultype=MYSQLI_ASSOC){
        $this->connect();
        $query = "select $campos from $tabla $condicion";
        if($this->debug)echo$query;
        $result = mysqli_query($this->conn,$query);
        if($result) $arr = mysqli_fetch_array($result,$resultype);            
        else $arr = null;
        if($cerrar) $this->cerrar();
        return $arr;
    }
    
    function insertar($tabla,$campos,$valores,$cerrar=true){
        try {
            $this->connect();
            $query = "insert into $tabla($campos) values($valores)";
            if($this->debug)echo$query;
            $e = mysqli_query($this->conn,$query);
            if($e)$e = ($this->conn->insert_id)?$this->conn->insert_id:true;//por si el registro no es por id autoincrement
            if($cerrar)
                $this->cerrar();            
            return $e;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
    }    
    function update($campo,$valor,$tabla,$condicion,$cerrar=true){
        $this->connect();
        $query = "update $tabla set $campo = $valor $condicion";
        if($this->debug)echo$query;
        $e = mysqli_query($this->conn,$query);
        if($cerrar)
            $this->cerrar();
        return $e;
    }
    function executeSQL($query,$cerrar=true){
        $this->connect();        
        if($this->debug)echo$query;
        $e = mysqli_query($this->conn,$query);
        if($cerrar)
            $this->cerrar();
        return $e;
    }
    public function cerrar(){
        $this->debug=false;
        if(is_resource($this->conn)){            // && get_resource_type($this->conn) === 'mysqli link'
            return mysqli_close($this->conn);
        }
    }
    
    
}
