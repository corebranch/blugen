<?php

namespace Blugen\Service\Xrpc;

interface CallableInterface
{
    public function method(): string;
    public function path(): string;
    public function options(): array;
}
