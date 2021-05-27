--Consulta que retorna número USP, nome, data de nascimento, 
--data de falecimento e gênero (F, M) dos falecidos no período especificado.

SELECT DISTINCT c.codpes, p.nompes, p.dtanas, dtaflc, p.sexpes 
  FROM COMPLPESSOA c 
  JOIN PESSOA p ON c.codpes = p.codpes 
  JOIN VINCULOPESSOAUSP v on c.codpes = v.codpes
    WHERE c.dtaflc BETWEEN :dtaini AND :dtafim
    AND v.codclg in (__unidades__)
    ORDER BY c.dtaflc
