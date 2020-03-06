<?php

namespace Uspdev\Replicado;

class Lattes
{
    public static function idLattes($codpes)
	{
	    $query = "SELECT idfpescpq as idLattes from DIM_PESSOA_XMLUSP where codpes = convert(int,:codpes)";
		$param = [
            'codpes' => $codpes,
        ];
        $result = DB::fetch($query, $param);
        if(!empty($result)) {
            return $result;
        }
        return false;
	}
}