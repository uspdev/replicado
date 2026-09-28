SELECT TOP 1 P.codpes, P.nompesttd 
FROM R39PGMORIDOC R
INNER JOIN PESSOA P ON (R.codpes = P.codpes)
WHERE R.codpespgm = convert(int, :codpespgm) 
  AND R.codare = convert(int, :codare) 
  AND R.numseqpgm = convert(int, :numseqpgm) 
  AND R.tiport IN ('ORI','EXC')
ORDER BY R.dtainiort DESC