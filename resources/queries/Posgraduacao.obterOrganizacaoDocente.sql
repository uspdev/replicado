SELECT TOP 1 O.sglorg, O.nomrazsoc
FROM HISTPES H
INNER JOIN ORGANIZACAO O ON (H.codorg = O.codorg)
WHERE H.codpes = convert(int, :codpes)
AND H.tipfnc = 'Docente'
ORDER BY H.dtainifnchst DESC
