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
 * Application bootstrap
 */
class Bootstrap
{
    public static function createContainer(): ContainerInterface
    {
        try {
            $classLoader = self::loadClassLoader();
            $configManager = ConfigManager::load();

            return self::buildContainer($classLoader, $configManager);
        } catch (\Throwable $e) {
            self::handleBootstrapError($e);
            exit(1);
        }
    }

    private static function loadClassLoader(): ClassLoader
    {
        // Check for custom bootstrap
        $customBootstrap = self::getCustomBootstrap();
        if ($customBootstrap) {
            return require $customBootstrap;
        }

        // Try to get existing ClassLoader
        $classLoader = self::findExistingClassLoader();
        if ($classLoader) {
            return $classLoader;
        }

        // Try to load from common paths
        $autoloaderPath = self::findAutoloaderPath();
        if ($autoloaderPath) {
            return require $autoloaderPath;
        }

        throw new \LogicException("Unable to load autoloader.");
    }

    private static function getCustomBootstrap(): ?string
    {
        $input = new ArgvInput();
        $bootstrap = $input->getParameterOption('--bootstrap');

        if ($bootstrap) {
            if (file_exists($bootstrap)) {
                return $bootstrap;
            }

            throw new \LogicException("Unable to load autoloader.");
        }

        return null;
    }

    private static function findExistingClassLoader(): ?ClassLoader
    {
        foreach (spl_autoload_functions() as $autoloader) {
            if (is_array($autoloader) && $autoloader[0] instanceof ClassLoader) {
                return $autoloader[0];
            }
        }

        return null;
    }

    private static function findAutoloaderPath(): ?string
    {
        $possiblePaths = [
            __DIR__ . '/../../../../autoload.php',  // vendor/shahmal1yev/blugen
            __DIR__ . '/../vendor/autoload.php',    // package root
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function buildContainer(ClassLoader $classLoader, ConfigManager $configManager): ContainerInterface
    {
        $container = new ContainerBuilder();

        // Register core services
        $container->set('loader', $classLoader);
        $container->set(ConfigManager::class, $configManager);
        $container->setParameter('blugen.config', $configManager->all());

        // Load service definitions
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.php');

        $container->compile();

        return $container;
    }

    private static function handleBootstrapError(\Throwable $e): void
    {
        fwrite(
            STDERR,
            "Bootstrap error: " . $e->getMessage() . PHP_EOL . $e->getTraceAsString()
        );
        exit(1);
    }
}
