<?php

namespace Uspdev\Replicado;
use PDO;

class Connection
{
    public $conn;
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
    
    public function setSybase()
    {
        $this->conn = new PDO("dblib:tdsver=5.0;host=$this->dbHost:$this->dbPort", $this->dbUser,$this->dbPassword);
        $this->conn->query("use $this->dbName");
    }
    
    public function setMssql()
    {
        $dsn= "dblib:host=$this->dbHost:$this->dbPort;dbname=$this->dbName;";
        $dbusername=$this->dbUser;
        $dbpassword=$this->dbPassword;
        $this->conn = new PDO($dsn,$dbusername,$dbpassword);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }
    
}
