<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Uteis;

class Pessoa 
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function pessoa($codpes)
    {
        $uteis = new Uteis();
        $query = file_get_contents('../replicado_queries/pessoa_where_codpes.sql', true);
        $query = str_replace('__codpes__',$codpes,$query); 
        $q = $this->db->query($query);
        $result = $q->fetchAll()[0];
        $result = $uteis->utf8_converter($result);
        return $result;
    }
}
