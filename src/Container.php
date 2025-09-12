<?php

namespace Blugen;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container accessor
 */
class Container
{
    private static ?ContainerInterface $container = null;

    public static function set(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function reset(): void
    {
        self::$container = null;
    }

    public static function get(): ContainerInterface
    {
        if (null === self::$container) {
            self::$container = Bootstrap::createContainer();
        }

        return self::$container;
    }
}
