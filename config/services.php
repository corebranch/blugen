<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Blugen\Config\ConfigManager;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\ReaderInterface;
use Blugen\Service\Lexicon\V1\Generator as LexiconGenerator;
use Blugen\Service\Lexicon\V1\Nsid;
use Blugen\Service\Lexicon\V1\Reader;
use Blugen\Service\Xrpc\Client;
use Blugen\Service\Xrpc\ClientInterface;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Filesystem\Filesystem;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->set(ClassLoader::class)->public();
    $services->set(ConfigManager::class)->public();

    $services->set(Reader::class)
        ->alias(ReaderInterface::class, Reader::class)
        ->public();

    $services->set(LexiconGenerator::class)
        ->alias(GeneratorInterface::class, LexiconGenerator::class)
        ->public();

    $services->set(Client::class)
        ->alias(ClientInterface::class, Client::class)
        ->public();
};
