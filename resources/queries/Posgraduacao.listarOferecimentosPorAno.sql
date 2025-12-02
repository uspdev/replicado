WITH ContadorMinistrantes AS (
    SELECT 
        sgldis, numseqdis, numofe, 
        COUNT(*) as total_ministrantes
    FROM R32TURMINDOC 
    GROUP BY sgldis, numseqdis, numofe
),
ContadorMatriculados AS (
    SELECT 
        sgldis, numseqdis, numofe,
        COUNT(*) as total_matriculados
    FROM R41PGMMATTUR
    WHERE stamtrpgmofe = 'D' -- matriculas deferidas
    GROUP BY sgldis, numseqdis, numofe
)
SELECT
    P_TODOS.nompesttd AS nome_ministrante,
    P_TODOS.codpes AS codpes,
    CASE 
        WHEN CM.total_ministrantes > 1 
        THEN (CAST(D.cgahorteodis AS DECIMAL(10,1)) --* D.durdis
        ) / CM.total_ministrantes
        ELSE CAST(D.cgahorteodis AS DECIMAL(10,1)) --* D.durdis

    END AS fracao_teo,

    CASE 
        WHEN CM.total_ministrantes > 1 
        THEN (CAST(D.cgahorpradis AS DECIMAL(10,1)) --* D.durdis
        ) / CM.total_ministrantes
        ELSE CAST(D.cgahorpradis AS DECIMAL(10,1)) --* D.durdis

    END AS fracao_pra,

    O.sgldis as coddis,
    O.numseqdis,
    O.numofe AS codtur,
    CONCAT(O.sgldis, ' (', O.numseqdis,') - ', D.nomdis) AS disciplina,
    E.horiniofe AS hora_inicio,
    E.horfimofe AS hora_fim,
    E.diasmnofe AS dia_semana,
    CM.total_ministrantes AS num_ministrantes,
    D.durdis AS duracao,
    D.cgahorteodis AS horas_teo, --* D.durdis
    D.cgahorpradis AS horas_pra, --* D.durdis
    D.cgahoresddis AS horas_estudo,
    D.nomdis, --novo
    O.numvagofetot as vagas, --novo
    CMAT.total_matriculados AS matriculados, --novo
    O.dtainiofe AS data_inicio,
    O.dtafimofe AS data_fim,
    O.fmtofe as formato,
    R35.tipatvaux AS atividade
    
FROM OFERECIMENTO O
INNER JOIN DISCIPLINA D ON D.sgldis = O.sgldis AND D.numseqdis = O.numseqdis
INNER JOIN ESPACOTURMA E ON E.sgldis = O.sgldis AND E.numseqdis = O.numseqdis AND E.numofe = O.numofe
INNER JOIN R32TURMINDOC M_TODOS ON O.sgldis = M_TODOS.sgldis 
                                AND O.numseqdis = M_TODOS.numseqdis 
                                AND O.numofe = M_TODOS.numofe
INNER JOIN PESSOA P_TODOS ON M_TODOS.codpes = P_TODOS.codpes
INNER JOIN ContadorMinistrantes CM ON O.sgldis = CM.sgldis 
                                   AND O.numseqdis = CM.numseqdis 
                                   AND O.numofe = CM.numofe
LEFT JOIN R35DOCCOLTUR R35 ON M_TODOS.codpes = R35.codpes
                           AND M_TODOS.sgldis = R35.sgldis
                           AND M_TODOS.numseqdis = R35.numseqdis
                           AND M_TODOS.numofe = R35.numofe
LEFT JOIN ContadorMatriculados CMAT ON O.sgldis = CMAT.sgldis
                                    AND O.numseqdis = CMAT.numseqdis
                                    AND O.numofe = CMAT.numofe

WHERE M_TODOS.codpes IN (:codpes)
AND O.numvagofe > 0
AND O.tipmotcantur IS NULL
AND (O.dtainiofe >= CONCAT(:ano, '-01-01') AND O.dtainiofe <= CONCAT(:ano, '-12-31'))

ORDER BY O.numofe, O.sgldis;
