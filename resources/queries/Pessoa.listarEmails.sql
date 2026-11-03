SELECT E.codpes, E.codema
FROM EMAILPESSOA E
WHERE E.codpes IN(:codpes)
AND E.stamtr='S'
