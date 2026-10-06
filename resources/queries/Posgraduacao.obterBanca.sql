SELECT 
    R.codpesdct, 
    R.vinptpbantrb, 
    R.staptp, 
    P.nompesttd 
FROM R48PGMTRBDOC R
INNER JOIN PESSOA P ON (R.codpesdct = P.codpes)
WHERE R.codpes = convert(int, :codpes) 
  AND R.codare = convert(int, :codare) 
  AND R.numseqpgm = convert(int, :numseqpgm)
ORDER BY 
    CASE WHEN R.vinptpbantrb = 'SUP' THEN 1 ELSE 0 END, 
    P.nompesttd
