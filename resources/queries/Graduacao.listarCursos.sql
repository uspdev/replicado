SELECT codcur, nomcur
FROM CURSOGR
WHERE codclg IN (__codundclgs__)
  AND dtaatvcur IS NOT NULL
  AND dtadtvcur IS NULL
ORDER BY nomcur
