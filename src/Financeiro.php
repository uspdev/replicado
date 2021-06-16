<?php

namespace Uspdev\Replicado;

class Financeiro
{
     /**
     * Método que retorna os centros de despesas.
     * Utiliza o REPLICADO_CODUNDCLG do .env
     * 
     * @param empty
     * @return array
     * @author Victor de O. Marinho <victor.oliveira.marinho@usp.br>
     */
    public static function listarCentrosDespesas()
    {
        $unidades = getenv('REPLICADO_CODUNDCLG');
        $query = "SELECT etrhie FROM CENTRODESPHIERARQUIA
                    WHERE dtadtv IS NULL
                    AND codunddsp IN ({$unidades})";
                          
        return DB::fetchAll($query);
    }

}
