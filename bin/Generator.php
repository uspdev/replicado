<?php

namespace Uspdev\Replicado\Generator;

use ReflectionClass;
use ReflectionMethod;

require_once __DIR__ . '/../../../vendor/autoload.php';


/**
 * Gerador de arquivo Proxy para as classes Base do Replicado.
 * 
 * Toda vez que um método é alterado devemos rodar o Generator.
 * 
 * Rodar composer update na biblioteca antes de usar.
 * Rodar `php Generator.php`
 * 
 * #### pro enquanto somente na classe Estrutura para testes.
 */
class ProxyGenerator
{
    public function generate(string $baseClassFull)
    {
        $reflection = new ReflectionClass($baseClassFull);
        $className = $reflection->getShortName();
        $methodsCode = "";

        // Pegamos apenas métodos públicos e estáticos da Base
        $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodsCode .= $this->buildMethodProxy($method);
        }

        // Template do arquivo gerado
        $template = <<<PHP
<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Interceptor;

/**
 * ATENÇÃO: Classe gerada automaticamente. 
 * Não edite este arquivo manualmente.
 */
class {$className} extends \\{$baseClassFull}
{
{$methodsCode}
}
PHP;

        // Salva na pasta pai de onde está a Base
        $outputPath = dirname($reflection->getFileName(), 2) . "/{$className}.php";
        file_put_contents($outputPath, $template);

        echo "✅ Classe Proxy para {$className} gerada em: {$outputPath}\n";
    }

    private function buildMethodProxy(ReflectionMethod $method): string
    {
        $name = $method->name;

        return <<<PHP

    public static function {$name}(...\$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            '{$name}', 
            \$args, 
            fn(...\$params) => parent::{$name}(...\$params)
        );
    }
PHP;
    }
}

// Execução simples para teste:
$gen = new ProxyGenerator();
$gen->generate(\Uspdev\Replicado\Base\Estrutura::class);