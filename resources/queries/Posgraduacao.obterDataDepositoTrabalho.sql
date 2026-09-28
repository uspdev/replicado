SELECT A.dtadpopgm
                  FROM AGPROGRAMA AS A
                  WHERE A.codpes = convert(int, :codpes) AND
                  A.codare = convert(int, :codare) AND
                  A.nivpgm = :nivpgm AND
                  A.numseqpgm = convert(int, :numseqpgm)