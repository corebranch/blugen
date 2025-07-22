<?php

namespace Blugen\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Xrpc\CallableInterface;
use Blugen\Service\Xrpc\Encoder\Encoders\EncoderInterface;
use Blugen\Service\Xrpc\Encoder\Encoders\InputEncoder;
use Blugen\Service\Xrpc\Encoder\Encoders\ParamsEncoder;

class Factory
{
    public function __construct(private readonly CallableInterface $callable)
    {
    }

    public function create(): EncoderInterface
    {
        return new (match(true) {
            $this->isInstanceOf(ProcedureInterface::class) => InputEncoder::class,
            $this->isInstanceOf(QueryInterface::class) => ParamsEncoder::class,
        })($this->callable);
    }

    private function isInstanceOf(string $fqcn): bool
    {
        return $this->callable instanceof $fqcn;
    }
}
