<?php

namespace Blugen\Service\Xrpc\Encoder\Encoders;

use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Xrpc\Encoder\DataProviderFactory;
use Blugen\Service\Xrpc\Encoder\PropertyCollector;

class InputEncoder implements EncoderInterface
{
    private readonly PropertyCollector $collector;

    public function __construct(private readonly ProcedureInterface $procedure, ?PropertyCollector $collector = null)
    {
        $this->collector = $collector ?? new PropertyCollector(DataProviderFactory::create($this->procedure));
    }

    public function encode(): string
    {
        return json_encode(
            $this->collector->collect(),
            JSON_THROW_ON_ERROR
        );
    }
}
