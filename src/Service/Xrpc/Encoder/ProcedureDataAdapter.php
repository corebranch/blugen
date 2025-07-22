<?php

namespace Blugen\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Lexicon\ProcedureInterface;

class ProcedureDataAdapter implements DataProvider
{
    public function __construct(private readonly ProcedureInterface $procedure)
    {
    }

    public function getData(): InputInterface
    {
        return $this->procedure->getSchema();
    }
}