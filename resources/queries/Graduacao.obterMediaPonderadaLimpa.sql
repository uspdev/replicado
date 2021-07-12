SELECT t2.creaul, t1.notfim, t1.notfim2
    FROM HISTESCOLARGR t1
    INNER JOIN DISCIPLINAGR t2 ON t1.coddis = t2.coddis
    AND t1.verdis = t2.verdis
    WHERE t1.rstfim = 'A' --Aluno aprovado na disciplina
    AND t1.codpes = convert(int,:codpes)
    AND t1.codpgm = convert(int,:codpgm)