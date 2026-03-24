<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Replicado as Config;
use Uspdev\Replicado\Cache;

class ReplicadoBase
{
    /**
     * Intercepta chamadas estáticas para métodos não definidos
     * e redireciona para os métodos protegidos correspondentes.
     */
    public static function __callStatic($method, $args)
    {
        $class = static::class;
        $config = Config::getInstance();
        $realMethod = '_' . $method;
        
        if (!method_exists($class, $realMethod)) {
            throw new \BadMethodCallException(
                "Método {$class}::{$method} não existe"
            );
        }

        if ($config->fake) {
            return Config::getFake($class . '.' . $method);
        }
        
        if ($config->usarCache) {
            $cacheKey = $class . ':' . $method . ':' . md5(json_encode($args));
            return Cache::remember($cacheKey, function () use ($class, $realMethod, $args) {
                return call_user_func_array([$class, $realMethod], $args);
            });
        }

        return call_user_func_array([$class, $realMethod], $args);
    }
}
