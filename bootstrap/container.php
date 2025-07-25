<?php

use Symfony\Component\Console\Input\ArgvInput;

return (function () {
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
