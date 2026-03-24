<?php

namespace Uspdev\Replicado;

use \Memcached;

class Cache
{
    private static ?Memcached $memcached = null;
    private static int $expiry;
    private static int $small;
    private static string $prefix;

    private static function conn(): Memcached
    {
        if (self::$memcached === null) {
            self::$memcached = new Memcached();
            if (empty(self::$memcached->getServerList())) {
                self::$memcached->addServer('127.0.0.1', 11211);
            }
        }
        return self::$memcached;
    }

    public static function config($expiry = 14400, $small = -1, $prefix = 'replicado:')
    {
        // não deve ser maior que 30 dias (2592000 segundos)
        self::$expiry = ($expiry > 2592000) ? 2592000 : $expiry;
        self::$small = $small;
        self::$prefix = $prefix;
    }

    public static function get(string $key)
    {
        $result = self::conn()->get(self::$prefix . $key);

        if (self::conn()->getResultCode() != Memcached::RES_SUCCESS) {
            return null;
        }

        return unserialize($result);
    }

    public static function set(string $key, $value): bool
    {
        $serialized = serialize($value);

        // não cachear dados pequenos
        if (strlen($serialized) <= self::$small) {
            return false;
        }
        $r = self::conn()->set(self::$prefix . $key, $serialized, self::$expiry);
        return $r;
    }

    public static function delete(string $key): bool
    {
        return self::conn()->delete(self::$prefix . $key);
    }

    public static function clear(): bool
    {
        return self::conn()->flush();
    }

    public static function remember(string $key, callable $callback)
    {
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value);
        return $value;
    }
}
