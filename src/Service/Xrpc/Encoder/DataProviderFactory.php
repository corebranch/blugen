<?php

namespace Blugen\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Lexicon\QueryInterface;

class DataProviderFactory
{
    public static function create(ProcedureInterface|QueryInterface $callable): DataProvider
    {
        return match (true) {
            $callable instanceof QueryInterface => new QueryDataAdapter($callable),
            $callable instanceof ProcedureInterface => new ProcedureDataAdapter($callable),
        };
    }
}
