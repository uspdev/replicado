 ## Métodos da classe Bempatrimoniado

 - *dump($numpat, $cols)*: recebe numpat e retorna todos campos da tabela bempatrimoniado
 - *verifica($numpat)*: recebe numpat e retorna true se o bem está ativo

 - *bens(array $filtros = [], array $buscas = [], array $tipos = [], int $limite = 2000)*: retorna todos campos da tabela BEMPATRIMONIADO dos patrimônios. Utilizar $filtros para valores exatos, $buscas com o *LIKE* e $tipos para colunas que precisam de convert. Por padrão, retorna 2000 registros.

 - *ativos(array $filtros = [], array $buscas = [], array $tipos = [], int $limite = 2000)*: retorna todos campos da tabela BEMPATRIMONIADO dos patrimônios ATIVOS. Utilizar $filtros para valores exatos, $buscas com o *LIKE* e $tipos para colunas que precisam de convert. Em $filtros já é adicionado por padrão o 'stabem' = 'Ativo'. Por padrão, retorna 2000 registros.

  * Exemplo utilização: 
  ```php
        $filtros = [
            'codpes' => 11111111,
            'codlocusp' => 11111,
        ];
        $buscas = [
            'epfmarpat' => 'MARCA',
            'modpat' => 'CORE 2 DUO',
        ];
        $tipos = [
            'codpes' => 'int',
            'codlocusp' => 'numeric',
        ]
        $bens = Bempatrimoniado::bens($filtros, $buscas, $tipos); 
        $ativos = Bempatrimoniado::ativos($filtros, $buscas, $tipos);
  ```