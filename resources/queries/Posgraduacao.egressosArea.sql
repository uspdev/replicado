SELECT p.nompesttd AS nompes, p.codpes AS codpes, a.nivpgm, a.dtadfapgm --, t.tittrb
FROM HISTPROGRAMA AS h, PESSOA AS p, AGPROGRAMA AS a, TRABALHOPROG AS t
WHERE h.tiphstpgm = 'CON' -- concluidos
    AND t.codare = h.codare AND t.codpes = h.codpes AND t.numseqpgm = h.numseqpgm -- join trabalhoprog
    AND p.codpes = h.codpes -- join pessoa
    AND a.codpes = h.codpes AND a.codare = h.codare AND a.numseqpgm = h.numseqpgm -- join agprograma
    AND h.codare = convert(int,:codare)
ORDER BY h.dtaocopgm DESC, h.codpes ASC