SELECT V.*, C.nomcur, H.*, P.*, F.sglfusclgund AS sglund
FROM PESSOA P
INNER JOIN VINCULOPESSOAUSP V on V.codpes = P.codpes AND V.tipvin = 'ALUNOGR'
INNER JOIN VINCSATHABILITACAOGR VS ON VS.codpes = P.codpes
INNER JOIN CURSOGR C ON VS.codcur = C.codcur
INNER JOIN HABILITACAOGR H ON H.codhab = VS.codhab AND H.codcur = C.codcur
INNER JOIN FUSAOCOLEGIADOUNIDADE F ON (F.codfusclgund = V.codclg)
WHERE
    P.codpes = CONVERT(int, :codpes)
