<?php

namespace Uspdev\Replicado;

class Connection
{
    public $db;
    private $dbHost;
    private $dbPort;
    private $dbName;
    private $dbUser;
    private $dbPassword;
    
    public function __construct($dbHost,$dbPort,$dbName,$dbUser,$dbPassword)
    {
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
    }
    
    public function sybase()
    {
        $this->db = new \PDO("dblib:tdsver=5.0;host=$this->dbHost:$this->dbPort", $this->dbUser,$this->dbPassword);
        $this->db->query("use $this->dbName");
        return $this->db;
    }
    
    public function mssql()
    {
        $dsn= "dblib:host=$this->dbHost:$this->dbPort;dbname=$this->dbName;";
        $dbusername=$this->dbUser;
        $dbpassword=$this->dbPassword;
        $this->db = new \PDO($dsn,$dbusername,$dbpassword);
        $this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return $this->db;
    }
    
}
