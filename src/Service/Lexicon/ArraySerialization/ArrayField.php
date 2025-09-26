<?php

namespace Blugen\Service\Lexicon\ArraySerialization;

final class ArrayField implements \Stringable
{
    public function __construct(
        private readonly string $key,
        private readonly string $expression,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function expression(): string
    {
        return $this->expression;
    }

    public function fullExpression(): string
    {
        return "'{$this->key}' => {$this->expression}";
    }

    public function __toString(): string
    {
        return $this->fullExpression();
    }
}
