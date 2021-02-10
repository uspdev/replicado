SELECT COUNT(DISTINCT l.codpes) FROM LOCALIZAPESSOA l
JOIN PESSOA p ON p.codpes = l.codpes 
JOIN HISTPROGRAMA h ON h.codpes = l.codpes 
WHERE l.tipvin = 'ALUNOPOS' 
AND l.codundclg IN (__unidades__)