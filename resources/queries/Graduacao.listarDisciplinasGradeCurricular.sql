SELECT G.coddis, D.nomdis
FROM fflch.dbo.GRADECURRICULAR G 
INNER JOIN fflch.dbo.DISCIPLINAGR D ON (G.coddis = D.coddis AND G.verdis = D.verdis)
WHERE G.codcrl IN (SELECT C.codcrl
FROM fflch.dbo.CURRICULOGR C
WHERE C.codcur = convert(int, :codcur) AND C.codhab = convert(int, :codhab) 
AND C.dtafimcrl IS NULL) AND G.tipobg = :tipobg