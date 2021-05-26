SELECT G.coddis, D.nomdis
FROM GRADECURRICULAR G 
INNER JOIN DISCIPLINAGR D ON (G.coddis = D.coddis AND G.verdis = D.verdis)
WHERE G.codcrl IN (SELECT C.codcrl
FROM CURRICULOGR C
WHERE C.codcur = convert(int, :codcur) AND C.codhab = convert(int, :codhab) 
AND C.dtafimcrl IS NULL) AND G.tipobg = :tipobg