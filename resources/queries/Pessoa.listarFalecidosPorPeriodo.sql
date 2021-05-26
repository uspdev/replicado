--Consulta que retorna número USP, nome, data de nascimento, 
--data de falecimento e gênero (F, M) dos falecidos no período especificado.
--Apenas pessoas que possuíam vínculo com a FFLCH. 

SELECT DISTINCT c.codpes, p.nompes, p.dtanas, dtaflc, p.sexpes 
  FROM COMPLPESSOA c 
  JOIN PESSOA p ON c.codpes = p.codpes 
  JOIN VINCULOPESSOAUSP v on c.codpes = v.codpes
    WHERE c.dtaflc BETWEEN __dtaini__ AND __dtafim__
    AND v.codclg = 8
    ORDER BY c.dtaflc
