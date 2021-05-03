-- Orientando Ativos
SELECT DISTINCT (codpespgm) as codpes
    FROM R39PGMORIDOC 
    WHERE codpes = convert(int, :codpes)
    AND dtafimort = NULL