<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Container\ArraySchema;
use PHPUnit\Framework\TestCase;

class ArraySchemaTest extends TestCase
{
    private function make(array $schema): ArraySchema
    {
        return new ArraySchema(new Schema($schema));
    }

    public function test_type_description_and_to_array(): void
    {
        $schema = [
            'type' => 'array',
            'description' => 'A list of strings',
            'items' => ['type' => 'string'],
            'minLength' => null,
            'maxLength' => 10,
        ];

        $arraySchema = $this->make($schema);

        $this->assertSame('array', $arraySchema->type());
        $this->assertSame('A list of strings', $arraySchema->description());

        // toArray should filter out only top-level nulls (minLength)
        $this->assertSame([
            'type' => 'array',
            'description' => 'A list of strings',
            'items' => ['type' => 'string'],
            'maxLength' => 10,
        ], $arraySchema->toArray());
    }

    public function test_items_min_max_accessors(): void
    {
        $schema = [
            'type' => 'array',
            'items' => ['type' => 'integer'],
            'minLength' => 1,
            'maxLength' => 5,
        ];

        $arraySchema = $this->make($schema);

        $this->assertSame(['type' => 'integer'], $arraySchema->items());
        $this->assertSame(1, $arraySchema->minLength());
        $this->assertSame(5, $arraySchema->maxLength());
    }

    public function test_schema_returns_underlying_array(): void
    {
        $schema = [
            'type' => 'array',
            'items' => ['type' => 'ref', 'ref' => 'com.example#item'],
        ];

        $arraySchema = $this->make($schema);

        $this->assertSame($schema, $arraySchema->schema());
    }

    public function test_magic_get_supports_nested_paths(): void
    {
        $schema = [
            'type' => 'array',
            'items' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
        ];

        $arraySchema = $this->make($schema);

        // Underlying Schema supports dotted access in __get
        $this->assertSame('string', $arraySchema->__get('items.properties.name.type'));
        $this->assertNull($arraySchema->__get('items.properties.missing'));
    }
}
