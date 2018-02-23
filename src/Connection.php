<?php

namespace Uspdev\Replicado;

class Connection
{
    public $db;
    public function __construct($dbHost,$dbPort,$dbName,$dbUser,$dbPassword)
    {
        $this->db = new \PDO("dblib:tdsver=5.0;host=$dbHost:$dbPort", $dbUser,$dbPassword);
        $this->db->query("use $dbName");
    }
}