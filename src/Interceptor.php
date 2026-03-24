<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Replicado as Config;
use Uspdev\Replicado\Cache; 

class Interceptor {
    public static function handle(string $class, string $method, array $args, callable $fallback) {
        $config = Config::getInstance();
        
        if ($config->fake) {
            return $config->getFake($class . '.' . $method);
        }
        
        if ($config->usarCache) {
            $cacheKey = $class . ':' . $method . ':' . md5(json_encode($args));
            return Cache::remember($cacheKey, function () use ($fallback, $args) {
                return $fallback(...$args);
            });
        }

        return $fallback(...$args);
    }
}