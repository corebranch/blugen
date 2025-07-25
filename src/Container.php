<?php

namespace Blugen;

use Blugen\Config\ConfigManager;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Container accessor with auto-bootstrap capability
 */
class Container
{
    private static ?ContainerInterface $container = null;
    private static bool $autoBootstrapAttempted = false;

    public static function set(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function get(): ContainerInterface
    {
        if (null === self::$container && !self::$autoBootstrapAttempted) {
            self::autoBootstrap();
        }

        if (null === self::$container) {
            throw new \LogicException('Container could not be initialized automatically.');
        }

        return self::$container;
    }

    private static function autoBootstrap(): void
    {
        self::$autoBootstrapAttempted = true;

        try {
            // Find the appropriate autoloader
            $possibleAutoloaders = [
                __DIR__ . '/../../../../autoload.php',              // When installed in vendor/shahmal1yev/blugen
                __DIR__ . '/../vendor/autoload.php',               // When using the package directly
                __DIR__ . '/../../../vendor/autoload.php',         // Alternative location
            ];

            $autoloader = null;
            foreach ($possibleAutoloaders as $file) {
                if (file_exists($file)) {
                    $autoloader = $file;
                    break;
                }
            }

            if ($autoloader === null) {
                return; // Fail silently, let the LogicException be thrown
            }

            /** @var ClassLoader $classLoader */
            $classLoader = require $autoloader;

            // Load configurations
            $configManager = ConfigManager::load();

            // Set up the container
            $container = new ContainerBuilder();
            $container->set('loader', $classLoader);
            $container->set(ConfigManager::class, $configManager);

            // Add config as container parameter
            $container->setParameter('blugen.config', $configManager->all());

            // Load container services
            $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../config'));
            $loader->load('services.php');

            // Compile the container
            $container->compile();

            // Set the container
            self::$container = $container;
        } catch (\Throwable $e) {
            // Fail silently, let the original LogicException be thrown
            return;
        }
    }
}
