<?php

use Symfony\Component\Console\Input\ArgvInput;

return (function () {
    // Ensure autoloader is loaded for CLI context
    $possibleAutoloaders = [
        __DIR__ . '/../../../autoload.php',              // When installed in vendor/shahmal1yev/blugen
        __DIR__ . '/../vendor/autoload.php',             // When using the package directly  
        __DIR__ . '/../../../vendor/autoload.php',       // Alternative location
    ];

    foreach ($possibleAutoloaders as $file) {
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }

    // Try to get the container (will auto-bootstrap if needed)
    try {
        $container = \Blugen\Container::get();
    } catch (\LogicException $e) {
        fwrite(STDERR, "Could not initialize container. Please make sure you have run 'composer install'.\n");
        exit(1);
    }

    // Support for custom bootstrap override (CLI only)
    $input = new ArgvInput();
    $bootstrap = $input->getParameterOption('--bootstrap');
    if ($bootstrap && file_exists($bootstrap)) {
        /** @var \Composer\Autoload\ClassLoader $customClassLoader */
        $customClassLoader = require $bootstrap;
        $container->set('loader', $customClassLoader);
    }

    return $container;
})();
