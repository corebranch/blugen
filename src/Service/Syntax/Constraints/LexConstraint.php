<?php

namespace Blugen\Service\Syntax\Constraints;

interface LexConstraint
{
    public function __construct(array $schema);

    public function getRequiredOptions(): array;

    public function schema(): array;
}
