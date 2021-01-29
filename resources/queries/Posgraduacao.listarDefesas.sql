SELECT
t1.codpes,
nompes = (SELECT nompes FROM PESSOA WHERE codpes=t1.codpes),
t1.dtadfapgm, -- Data da Defesa
t1.nivpgm,    -- ME/DO
t3.codare,    -- Código da área
t6.nomare,    -- Nome da área, um curso pode ter muitas áreas
t4.codcur,    -- Nome do programa de Pós-Graduação
t4.nomcur,    -- Nome do programa de Pós-Graduação
t5.tittrb     -- Título da Dissertação / Tese

FROM AGPROGRAMA t1 
    
INNER JOIN AREA t3
ON (
  t1.codare = t3.codare
)

INNER JOIN NOMECURSO t4
ON (
  t3.codcur = t4.codcur
)

INNER JOIN TRABALHOPROG t5
ON (
  t1.numseqpgm = t5.numseqpgm
  AND t1.codpes = t5.codpes
  AND t1.codare = t5.codare
)

INNER JOIN NOMEAREA t6
ON (
  t3.codare = t6.codare
)

-- Filtros
WHERE (
  t1.dtadfapgm >= :inicio AND 
  t1.dtadfapgm <= :fim
)