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
    public static function listarBeneficios()
    {
        $query = "SELECT B.tipbnfalu, B.nombnfloc, P.nompesttd, P.dtanas, P.sexpes, BC.*  FROM BENEFICIOALUCONCEDIDO BC
                  JOIN BENEFICIOALUNO B ON (BC.codbnfalu = B.codbnfalu)
                  JOIN PESSOA P on (BC.codpes = P.codpes)
                  WHERE BC.dtafimccd >= getdate()
        ";
        return DB::fetchAll($query);
    }

    /**
     * Retorna a lista de monitores da sala Pró-Aluno
     *
     * @param Array $codigoSalaMonitor
     * @return Array
     * @author Leandro Ramos, em 28/4/2025
     */
    public static function listarMonitoresProAluno($codigoSalaMonitor)
    {
        $codigosSalaMonitor = array_map(
            "trim",
            explode(",", $codigoSalaMonitor)
        );
        $replaces["codslamon"] = implode(
            ",",
            array_map(fn($cod) => "'$cod'", $codigosSalaMonitor)
        );
        $query = DB::getQuery(
            "Beneficio.listarMonitoresProAluno.sql",
            $replaces
        );

        $param = [
            "codslamon" => $codigoSalaMonitor,
        ];

        return DB::fetchAll($query);
    }
}
