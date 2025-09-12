<?php

namespace Blugen;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class App extends Application
{
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        $inputDefinition->addOption(new InputOption(
            '--bootstrap',
            '',
            InputOption::VALUE_OPTIONAL,
            'Custom autoloader path'
        ));

        return $inputDefinition;
    }
}
