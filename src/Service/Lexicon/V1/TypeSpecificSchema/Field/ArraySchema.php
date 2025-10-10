<?php

namespace Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\SchemaInterface;

class ArraySchema implements SchemaInterface
{
    public function __construct(
        private readonly SchemaInterface $schema,
    )
    {}

    public function __get(string $name): mixed
    {
        return $this->schema->__get($name);
    }

    public function type(): string
    {
        return $this->schema->type();
    }

    public function description(): ?string
    {
        return $this->schema->description();
    }

    public function items(): array
    {
        return $this->__get('items');
    }

    public function minLength(): ?int
    {
        return $this->__get('minLength');
    }

    public function maxLength(): ?int
    {
        return $this->__get('maxLength');
    }
}
