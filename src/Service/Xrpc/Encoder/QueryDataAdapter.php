<?php

namespace Blugen\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\ParamsInterface;
use Blugen\Service\Lexicon\QueryInterface;

class QueryDataAdapter implements DataProvider
{
    public function __construct(private readonly QueryInterface $query)
    {
    }

    public function getData(): ParamsInterface
    {
        return $this->query->getParams();
    }
}