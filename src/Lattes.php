<?php

namespace Uspdev\Replicado;

class Lattes
{
    /**
     * Recebe o número USP e retorna o ID Lattes da pessoa.
     */
    public static function idLattes($codpes)
	{
	    $query = "SELECT idfpescpq as idLattes from DIM_PESSOA_XMLUSP where codpes = convert(int,:codpes)";
		$param = [
            'codpes' => $codpes,
        ];
        return DB::fetch($query, $param);
	}
}