SELECT 
    v.codpes, 
    p.nompes
FROM VINCULOPESSOAUSP v 
JOIN PESSOA p ON v.codpes = p.codpes
WHERE v.tipfnc = 'Docente'
  AND v.codund IN (__unidades__)
  AND v.dtainidctati <= :dtafim 
  AND (v.dtafimdctati >= :dtaini OR v.dtafimdctati IS NULL) 
ORDER BY p.nompes