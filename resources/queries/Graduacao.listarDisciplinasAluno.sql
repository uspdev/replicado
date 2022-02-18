SELECT D.coddis, D.nomdis, D.creaul, D.cretrb
    , H.notfim, H.notfim2, H.rstfim, H.codtur
FROM HISTESCOLARGR H
INNER JOIN DISCIPLINAGR D ON H.coddis = D.coddis AND H.verdis = D.verdis
WHERE H.codpes = convert(int,:codpes)
    AND __rstfim__
    AND __codpgm__