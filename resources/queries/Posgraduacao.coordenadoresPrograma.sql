SELECT
    R.codcur,
    R.codpes,
    P.nompesttd AS nompes,
    R.dtainifnc,
    R.dtafimfnc,
    R.fncpescur
FROM R10DOCCOOCUR AS R
INNER JOIN PESSOA AS P ON P.codpes = R.codpes
WHERE R.codcur = CONVERT(int, :codcur)
AND R.fncpescur IN ('COO', 'VCO')
