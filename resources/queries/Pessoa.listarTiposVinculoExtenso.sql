SELECT DISTINCT tipvinext FROM LOCALIZAPESSOA
WHERE codundclg IN (__codundclgs__)
    AND (tipvin IN ('ALUNOGR', 'ALUNOPOS', 'ALUNOCEU', 'ALUNOEAD', 'ALUNOPD', 'ALUNOCONVENIOINT', 'SERVIDOR', 'ESTAGIARIORH'))
    AND (tipvinext NOT IN ('Servidor Designado', 'Servidor Aposentado'))
ORDER BY tipvinext
