SELECT
t1.dtadfapgm,

t1.codpes AS discente,
nome_discente = (SELECT DISTINCT nompes FROM PESSOA WHERE codpes=t1.codpes),

t2.codpes AS docente,
nome_docente = (SELECT DISTINCT nompes FROM PESSOA WHERE codpes=t2.codpes),

t1.dtadfapgm, -- Data da Defesa
t1.nivpgm,    -- ME/DO
t4.nomcur,    -- Nome do programa de Pós-Graduação
t5.tittrb     -- Título da Dissertação / Tese

FROM AGPROGRAMA t1 

-- R39PGMORIDOC traz o/a orientador/a
INNER JOIN R39PGMORIDOC t2
ON (
  t1.numseqpgm = t2.numseqpgm
  AND t1.codpes = t2.codpespgm
  AND t1.codare = t2.codare
)
     
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

-- Filtros
WHERE
t1.codpes = convert(int,:codpes) 

-- Um aluno pode passar por diversos/as orietadores/as
-- estamos interessados apenas no que participou da banca
AND t2.dtafimort = t1.dtadfapgm