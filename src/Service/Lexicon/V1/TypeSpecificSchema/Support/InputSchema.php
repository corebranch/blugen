<?php

namespace Blugen\Service\Lexicon\V1\TypeSpecificSchema\Support;

use Blugen\Enum\SupportTypeEnum;
use Blugen\Service\Lexicon\SchemaInterface;
use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ObjectSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\RefSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnionSchema;

class InputSchema implements SchemaInterface
{
    public function __construct(private SchemaInterface $schema)
    {
    }

    public function type(): string
    {
        return SupportTypeEnum::INPUT->value;
    }

    public function description(): ?string
    {
        return null;
    }

    public function __get(string $name): mixed
    {
        return $this->schema->__get($name);
    }

    public function encoding(): string
    {
        return $this->__get('encoding');
    }

    public function schema(): ObjectSchema|RefSchema|UnionSchema|null
    {
        $schema = $this->__get('schema');

        if ($schema) {
            $schema = new Schema($schema);
        } else {
            return new ObjectSchema(new Schema([]));
        }

        $class = match ($schema->type()) {
            'object' => ObjectSchema::class,
            'union' => UnionSchema::class,
            'ref' => RefSchema::class,
        };

        return new $class($schema);
    }
}
