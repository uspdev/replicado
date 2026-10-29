SELECT R.codpesdct, R.codpes, R.codare, R.numseqpgm
FROM R48PGMTRBDOC R
WHERE R.codpesdct = CONVERT(int, :codpesdct)
