<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Replicado as Config;

class ReplicadoBase
{
    public static function __callStatic($method, $args)
    {
        $class = static::class;

        // interceptador fake
        if (Config::getConfig('fake')) {
            return Config::getFake($class . '.' . $method);
        }

        // método real com underscore
        $realMethod = '_' . $method;

        if (method_exists($class, $realMethod)) {
            return forward_static_call([$class, $realMethod], ...$args);
        }

        throw new \BadMethodCallException(
            "Método {$class}::{$method} não existe"
        );
    }
}
