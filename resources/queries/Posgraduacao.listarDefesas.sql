SELECT
  P.codpes,
	nompes = (SELECT DISTINCT nompes FROM PESSOA WHERE codpes=P.codpes),
	P.dtadfapgm, -- Data da Defesa
	P.nivpgm,    -- ME/DO
	P.codare,    -- Código da área
	NA.nomare,    -- Nome da área, um curso pode ter muitas áreas
	NC.codcur,    -- Nome do programa de Pós-Graduação
	NC.nomcur,    -- Nome do programa de Pós-Graduação
	T.tittrb     -- Título da Dissertação / Tese

FROM 
			       AGPROGRAMA P
	INNER JOIN AREA A ON P.codare = A.codare
	INNER JOIN NOMEAREA NA ON P.codare = NA.codare
	INNER JOIN CURSO AS C ON A.codcur = C.codcur 
	INNER JOIN NOMECURSO NC ON A.codcur = NC.codcur
	INNER JOIN TRABALHOPROG T ON (P.numseqpgm = T.numseqpgm AND P.codpes = T.codpes AND P.codare = T.codare)
-- Filtros
WHERE 
  C.codclg IN (__unidades__) AND
  (
  P.dtadfapgm >= :inicio AND 
  P.dtadfapgm <= :fim
)