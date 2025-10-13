<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\IntegerSchema;
use PHPUnit\Framework\TestCase;

class IntegerSchemaTest extends TestCase
{
    private function make(array $schema): IntegerSchema
    {
        return new IntegerSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array_filters_only_nulls(): void
    {
        $schema = [
            'type' => 'integer',
            'description' => 'An integer field',
            'minimum' => 0,
            'maximum' => null,
            'enum' => [1, 2, 3],
            'default' => 0,
            'const' => null,
        ];

        $int = $this->make($schema);

        $this->assertSame('integer', $int->type());
        $this->assertSame('An integer field', $int->description());

        // toArray should keep 0 values and drop only nulls
        $this->assertSame([
            'type' => 'integer',
            'description' => 'An integer field',
            'minimum' => 0,
            'enum' => [1, 2, 3],
            'default' => 0,
        ], $int->toArray());
    }

    public function test_minimum_maximum_enum_default_const_accessors(): void
    {
        $schema = [
            'type' => 'integer',
            'minimum' => -5,
            'maximum' => 10,
            'enum' => [0, 5, 10],
            'default' => 5,
            'const' => 10,
        ];

        $int = $this->make($schema);

        $this->assertSame(-5, $int->minimum());
        $this->assertSame(10, $int->maximum());
        $this->assertSame([0, 5, 10], $int->enum());
        $this->assertSame(5, $int->default());
        $this->assertSame(10, $int->const());
    }

    public function test_accessors_return_null_when_not_set(): void
    {
        $schema = [
            'type' => 'integer',
        ];

        $int = $this->make($schema);

        $this->assertNull($int->minimum());
        $this->assertNull($int->maximum());
        $this->assertNull($int->enum());
        $this->assertNull($int->default());
        $this->assertNull($int->const());
        $this->assertSame(['type' => 'integer'], $int->toArray());
    }

    public function test_magic_get_dotted_path_and_schema(): void
    {
        $schema = [
            'type' => 'integer',
            'meta' => ['range' => ['inclusive' => true]],
        ];

        $int = $this->make($schema);
        $this->assertTrue($int->__get('meta.range.inclusive'));
        $this->assertNull($int->__get('meta.range.unknown'));
        $this->assertSame($schema, $int->schema());
    }
}

