SELECT
    DISTINCT PROF.nompes, PROF.codpes, O.codtur, T.tiptur, D.coddis, D.nomdis, D.verdis, H.rstfim
FROM
    ALUNOGR A INNER JOIN PROGRAMAGR PR ON A.codpes=PR.codpes
    INNER JOIN HISTESCOLARGR H ON PR.codpes=H.codpes AND PR.codpgm=H.codpgm
    INNER JOIN TURMAGR T ON H.coddis=T.coddis AND H.verdis=T.verdis AND H.codtur=T.codtur
    INNER JOIN DISCIPLINAGR D ON T.coddis=D.coddis AND T.verdis=D.verdis
    INNER JOIN OCUPTURMA O ON T.coddis=O.coddis AND T.verdis=O.verdis AND T.codtur=O.codtur
    INNER JOIN MINISTRANTE M ON O.coddis=M.coddis AND O.codtur=M.codtur AND O.verdis=M.verdis
    INNER JOIN PESSOA PROF ON M.codpes=PROF.codpes
WHERE
    A.codpes = :codpes
    AND T.codtur LIKE :anoSemestre
    AND H.stamtr = 'M'
    AND __rstfim__
ORDER BY
    D.nomdis;
