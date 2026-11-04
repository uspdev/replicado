SELECT S.nomabvset, S.nomset, U.sglund, V.*
FROM VINCULOPESSOAUSP V
LEFT JOIN SETOR S ON S.codset = V.codset
LEFT JOIN UNIDADE U ON U.codund = V.codfusclgund
WHERE codpes = convert(INT, :codpes)
AND sitatl != 'A'
ORDER BY dtafimvin
