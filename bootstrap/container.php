<?php

use Blugen\Config\ConfigManager;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

return (function () {
    // Find the appropriate autoloader
    $possibleAutoloaders = [
        __DIR__ . '/../../../autoload.php',              // When installed in vendor/shahmal1yev/blugen
        __DIR__ . '/../vendor/autoload.php',             // When using the package directly
        __DIR__ . '/../../../vendor/autoload.php',       // Alternative location
    ];

    $autoloader = null;
    foreach ($possibleAutoloaders as $file) {
        if (file_exists($file)) {
            $autoloader = $file;
            break;
        }
    }

    if ($autoloader === null) {
        fwrite(STDERR, "Could not find autoloader. Please make sure you have run 'composer install'.\n");
        exit(1);
    }

    /** @var \Composer\Autoload\ClassLoader $classLoader */
    $classLoader = require $autoloader;

    // TODO: needs refactoring
    // Allow custom bootstrap override
    $input = new ArgvInput();
    $bootstrap = $input->getParameterOption('--bootstrap');
    if ($bootstrap && file_exists($bootstrap)) {
        /** @var \Composer\Autoload\ClassLoader $classLoader */
        $classLoader = require $bootstrap;
    }

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

    // Set up the container singleton
    \Blugen\Container::set($container);
})();
