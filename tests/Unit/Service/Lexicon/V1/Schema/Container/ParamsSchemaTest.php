<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Container\ParamsSchema;
use PHPUnit\Framework\TestCase;

class ParamsSchemaTest extends TestCase
{
    private function make(array $schema): ParamsSchema
    {
        return new ParamsSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array_filters_only_nulls(): void
    {
        $schema = [
            'type' => 'object',
            'description' => 'Query params',
            'properties' => [
                'q' => ['type' => 'string'],
            ],
            'required' => ['q'],
            'extra' => null,
        ];

        $params = $this->make($schema);

        $this->assertSame('object', $params->type());
        $this->assertSame('Query params', $params->description());

        $this->assertSame([
            'type' => 'object',
            'description' => 'Query params',
            'properties' => [
                'q' => ['type' => 'string'],
            ],
            'required' => ['q'],
        ], $params->toArray());
    }

    public function test_required_accessor_and_schema_return(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => ['a', 'b'],
        ];

        $params = $this->make($schema);
        $this->assertSame(['a', 'b'], $params->required());
        $this->assertSame($schema, $params->schema());
    }

    public function test_required_is_null_when_absent(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
        ];

        $params = $this->make($schema);
        $this->assertNull($params->required());
    }

    public function test_properties_map_to_property_objects_with_nullable_inverted_from_required(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'limit' => ['type' => 'integer'],
                'cursor' => ['type' => 'string'],
            ],
            'required' => ['limit'],
        ];

        $params = $this->make($schema);
        $props = $params->properties();

        $this->assertCount(2, $props);
        $this->assertContainsOnlyInstancesOf(Property::class, $props);

        // preserve order: limit, cursor
        $limit = $props[0];
        $cursor = $props[1];

        $this->assertSame('limit', $limit->name());
        $this->assertFalse($limit->isNullable(), 'required field should not be nullable');
        $this->assertTrue($limit->isRequired());

        $this->assertSame('cursor', $cursor->name());
        $this->assertTrue($cursor->isNullable(), 'non-required field should be nullable');
        $this->assertFalse($cursor->isRequired());
    }

    public function test_magic_get_dotted_paths(): void
    {
        $schema = [
            'type' => 'object',
            'meta' => ['info' => ['note' => 'params']],
        ];

        $params = $this->make($schema);
        $this->assertSame('params', $params->__get('meta.info.note'));
        $this->assertNull($params->__get('meta.info.missing'));
    }
}

