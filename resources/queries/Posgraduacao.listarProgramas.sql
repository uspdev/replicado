SELECT C.codcur, NC.nomcur
FROM CURSO C
INNER JOIN NOMECURSO NC ON C.codcur = NC.codcur
WHERE (C.codclg IN (__unidades__))
    AND (C.tipcur = 'POS')
    AND (C.dtainiccp IS NOT NULL)
    AND (NC.dtafimcur IS NULL)
ORDER BY NC.nomcur ASC