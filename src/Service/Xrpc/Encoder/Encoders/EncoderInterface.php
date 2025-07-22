<?php

namespace Blugen\Service\Xrpc\Encoder\Encoders;

interface EncoderInterface
{
    public function encode(): string;
}
