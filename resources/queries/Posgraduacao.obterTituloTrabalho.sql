SELECT T.tittrb, T.tittrbigl
                FROM TRABALHOPROG T
                WHERE T.codpes = convert(int, :codpes) AND
                T.codare = convert(int, :codare) AND
                T.numseqpgm = convert(int, :numseqpgm)