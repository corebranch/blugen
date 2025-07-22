<?php

use Blugen\Config\ConfigManager;
use Blugen\Service\Lexicon\V1\Nsid;

function container(): \Symfony\Component\DependencyInjection\ContainerInterface
{
    return \Blugen\Container::get();
}

function config(): \Blugen\Config\ConfigManager
{
    return container()->get(ConfigManager::class);
}

function nsid(string $nsid): \Blugen\Service\Lexicon\V1\Nsid {
    return new \Blugen\Service\Lexicon\V1\Nsid($nsid);
}

function toPascalCase(string $input): string
{
    return implode('', array_map('ucfirst', explode(' ', strtolower(
        preg_replace('/[-_]|(?<=[a-z])(?=[A-Z])/', ' ', $input)
    ))));
}
