<?php

namespace Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\SchemaInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\Schema;

class ObjectSchema implements SchemaInterface
{
    public function __construct(private readonly SchemaInterface $schema)
    {
    }

    public function type(): string
    {
        return $this->schema->type();
    }

    public function description(): ?string
    {
        return $this->schema->description() ?? null;
    }

    public function __get(string $name): mixed
    {
        return $this->schema->__get($name);
    }

    public function properties(): array
    {
        $nullable = $this->nullable() ?? [];
        $required = $this->required() ?? [];
        $properties = $this->__get('properties') ?? [];

        return array_map(fn (string $name, array $rawSchema) => new Property(
            $name,
            new Schema($rawSchema),
            in_array($name, $nullable, true),
            in_array($name, $required, true),
        ), array_keys($properties), $properties);
    }

    public function required(): ?array
    {
        return $this->__get('required');
    }

    public function nullable(): ?array
    {
        return $this->__get('nullable');
    }
}
