<?php

namespace Blugen\Service\Syntax\Constraints;

trait Constraint
{
    public array $schema;

    public function __construct(array $schema)
    {
        $options = ['schema' => $schema];
        parent::__construct($options);
    }

    public function getRequiredOptions(): array
    {
        return ['schema'];
    }

    public function schema(): array
    {
        return $this->schema;
    }
}
