<?php

namespace Blugen\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Lexicon\ParamsInterface;

interface DataProvider
{
    public function getData(): ParamsInterface|InputInterface;
}