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
     * @return Array
     * @author Leandro Ramos, em 28/4/2025
     */
    public static function listarMonitoresProAluno($codigoSalaMonitor)
    {
        $query = "SELECT DISTINCT t1.codpes, t2.tipbnfalu, t1.codslamon
                  FROM BENEFICIOALUCONCEDIDO t1
                  INNER JOIN BENEFICIOALUNO t2
                  ON t1.codbnfalu = t2.codbnfalu
                  AND t1.dtafimccd > GETDATE()
                  AND t1.dtacanccd IS NULL
                  AND t2.codbnfalu = 32
                  AND t1.codslamon IN ($codigoSalaMonitor)";
        return DB::fetchAll($query);
    }
}
