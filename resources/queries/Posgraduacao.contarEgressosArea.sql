--SELECT COUNT(h.codpes)

SELECT year(a.dtadfapgm) AS ano, count(h.codpes) as quantidade

FROM HISTPROGRAMA AS h

INNER JOIN PESSOA AS p ON p.codpes = h.codpes

INNER JOIN AGPROGRAMA AS a ON 
(
  a.codpes = h.codpes AND 
  a.codare = h.codare AND 
  a.numseqpgm = h.numseqpgm
)

INNER JOIN TRABALHOPROG AS t ON
(
    t.codare = h.codare AND
    t.codpes = h.codpes AND 
    t.numseqpgm = h.numseqpgm
)

WHERE h.tiphstpgm = 'CON' -- concluidos
AND h.codare = convert(int,:codare)

GROUP BY year(a.dtadfapgm)
ORDER BY year(a.dtadfapgm)