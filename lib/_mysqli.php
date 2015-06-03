<?php
class _mysqli {
    private $host="localhost";     
    //private $port=null;
    //private $socket=null;
    private $user=null;
    private $password=null;
    private $dbname=null;
    public $conn=null;
    public $debug=false;
    public $logtime=false;
    public $logerror=false;
    public function __construct() {
        $this->getconfig();        
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
        if(defined('DEBUGME'))$this->debug = DEBUGME;
        if(defined('LOGEXECUTIONTIME'))$this->logtime = LOGEXECUTIONTIME;
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname) or die (mysqli_connect_error());
        $this->conn->query("USE $this->dbname;");
        $this->conn->query("SET NAMES 'UTF8';");        
        //$this->conn->query("CHARACTER SET uft8 COLLATE utf8_general_ci;");    
        return $this->conn;
    }   
    function resultN($tabla,$condicion='',$campos = '*'){
        $this->connect();
        $query = "SELECT $campos FROM $tabla $condicion";
        if($this->debug)echo$query;
        if($this->logtime)$st = init_time();
        $result = mysqli_query($this->conn,$query);
        if($this->debug)echo mysqli_error($this->conn);
        if($this->logtime)end_time($st, $query);
        return $result;            
    }
    function result1($tabla,$condicion='',$campos = '*',$cerrar = true){
        $this->connect();
        $query = "select $campos from $tabla $condicion";
        if($this->debug)echo $query;
        if($this->logtime)$st = init_time();
        $result = mysqli_query($this->conn,$query);
        if($this->debug)echo mysqli_error($this->conn);
        if($this->logtime)end_time($st, $query);
        if($result) $arr = mysqli_fetch_assoc($result);            
        else $arr = null;
        if($cerrar) $this->cerrar();
        return $arr;
    }
    function insertar($tabla,$campos,$valores,$cerrar=true){
        try {
            $this->connect();
            $query = "insert into $tabla($campos) values($valores)";
            if($this->debug)echo $query;
            if($this->logtime)$st = init_time();
            $e = mysqli_query($this->conn,$query);
            if($this->debug)echo mysqli_error($this->conn);
            if($e)$e = ($this->conn->insert_id)?$this->conn->insert_id:true;//por si el registro no es por id autoincrement
            if($this->logtime)end_time($st, $query);
            if($cerrar)
                $this->cerrar();            
            return $e;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
    }    
    function update($campo,$valor,$tabla,$condicion,$cerrar=true){
        $e = 0;
        $this->connect();
        $query = "update $tabla set $campo = $valor $condicion";
        if($this->debug)echo$query;
        if($this->logtime)$st = init_time();
        if(mysqli_query($this->conn,$query))
            $e = $this->conn->affected_rows;
        if($this->debug)echo mysqli_error($this->conn);
        if($this->logtime)end_time($st, $query);
        if($cerrar)
            $this->cerrar();
        return $e;
    }
    function executeSQL($query,$cerrar=true){ 
        $this->connect();        
        if($this->debug)echo$query;
        if($this->logtime)$st = init_time();
        $e = mysqli_query($this->conn,$query);
        if($this->debug)echo mysqli_error($this->conn);
        if($this->logtime)end_time($st, $query);
        if($cerrar)$this->cerrar();
        return $e;
    }
    public function cerrar(){
        $this->debug=false;
        if(is_resource($this->conn)){            // && get_resource_type($this->conn) === 'mysqli link'
            return mysqli_close($this->conn);
        }
    }
}
