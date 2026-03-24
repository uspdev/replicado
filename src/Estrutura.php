<?php

namespace Uspdev\Replicado;

use Uspdev\Replicado\Interceptor;

/**
 * ATENÇÃO: Classe gerada automaticamente. 
 * Não edite este arquivo manualmente.
 */
class Estrutura extends \Uspdev\Replicado\Base\Estrutura
{

    public static function dump(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'dump', 
            $args, 
            fn(...$params) => parent::dump(...$params)
        );
    }
    public static function listarSetores(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'listarSetores', 
            $args, 
            fn(...$params) => parent::listarSetores(...$params)
        );
    }
    public static function getChefiaSetor(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'getChefiaSetor', 
            $args, 
            fn(...$params) => parent::getChefiaSetor(...$params)
        );
    }
    public static function listarUnidades(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'listarUnidades', 
            $args, 
            fn(...$params) => parent::listarUnidades(...$params)
        );
    }
    public static function obterUnidade(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'obterUnidade', 
            $args, 
            fn(...$params) => parent::obterUnidade(...$params)
        );
    }
    public static function obterLocal(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'obterLocal', 
            $args, 
            fn(...$params) => parent::obterLocal(...$params)
        );
    }
    public static function listarLocaisUnidade(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'listarLocaisUnidade', 
            $args, 
            fn(...$params) => parent::listarLocaisUnidade(...$params)
        );
    }
    public static function procurarLocal(...$args)
    {
        // 1. Lógica de Interceptação (Fake/Cache)
        // O método 'handle' centraliza a decisão
        return Interceptor::handle(
            parent::class, 
            'procurarLocal', 
            $args, 
            fn(...$params) => parent::procurarLocal(...$params)
        );
    }
}