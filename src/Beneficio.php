<?php

namespace Uspdev\Replicado;

class Beneficio
{

    /**
     * Retorna a lista de benefícos concedidos e não encerrados
     * 
     * @return Array
     * @author Masaki K Neto, em 9/4/2021
     */
    public static function listarBeneficios() {
        $query = "SELECT B.tipbnfalu, B.nombnfloc, P.nompesttd, P.dtanas, P.sexpes, BC.*  FROM BENEFICIOALUCONCEDIDO BC
                  JOIN BENEFICIOALUNO B ON (BC.codbnfalu = B.codbnfalu)
                  JOIN PESSOA P on (BC.codpes = P.codpes)
                  WHERE BC.dtafimccd >= getdate()
        ";
        return DB::fetchAll($query);
    }
}