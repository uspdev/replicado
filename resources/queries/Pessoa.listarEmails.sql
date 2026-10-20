SELECT E.codpes, E.codema
FROM EMAILPESSOA E
WHERE E.codpes IN (__codpes__)
AND E.stamtr='S'
