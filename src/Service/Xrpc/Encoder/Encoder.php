<?php

namespace Blugen\Service\Xrpc\Encoder;

use Blugen\Service\Xrpc\CallableInterface;
use Blugen\Service\Xrpc\Encoder\Exceptions\EncoderException;

class Encoder
{
    private readonly Factory $factory;

    public function __construct(private readonly CallableInterface $callable, ?Factory $factory = null)
    {
        $this->factory = $factory ?? new Factory($this->callable);
    }

    public function encode(): string
    {
        try {
            return $this->factory->create()->encode();
        } catch (\Throwable $exception) {
            throw new EncoderException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
