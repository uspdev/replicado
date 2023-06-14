SELECT m.codpes, p.nompes
FROM OFERECIMENTOATIVIDADECEU o
INNER JOIN MINISTRANTECEU m ON o.codofeatvceu = m.codofeatvceu
INNER JOIN PESSOA p ON m.codpes = p.codpes
WHERE o.codcurceu = convert(int,:codcurceu)
    AND o.codedicurceu = convert(int,:codedicurceu)
