<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\StringSchema;
use PHPUnit\Framework\TestCase;

class StringSchemaTest extends TestCase
{
    private function make(array $schema): StringSchema
    {
        return new StringSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array_filters_only_nulls(): void
    {
        $schema = [
            'type' => 'string',
            'description' => 'A string field',
            'format' => 'did',
            'minLength' => 0,
            'maxLength' => null,
            'minGraphemes' => null,
            'maxGraphemes' => 64,
            'knownValues' => ['a', 'b'],
            'enum' => ['x', 'y'],
            'default' => 'x',
            'const' => null,
        ];

        $str = $this->make($schema);

        $this->assertSame('string', $str->type());
        $this->assertSame('A string field', $str->description());

        $this->assertSame([
            'type' => 'string',
            'description' => 'A string field',
            'format' => 'did',
            'minLength' => 0,
            'maxGraphemes' => 64,
            'knownValues' => ['a', 'b'],
            'enum' => ['x', 'y'],
            'default' => 'x',
        ], $str->toArray());
    }

    public function test_all_accessors_and_null_cases(): void
    {
        $schema = [
            'type' => 'string',
            'format' => 'uri',
            'minLength' => 1,
            'maxLength' => 255,
            'minGraphemes' => 1,
            'maxGraphemes' => 255,
            'knownValues' => ['ok'],
            'enum' => ['ok', 'no'],
            'default' => 'ok',
            'const' => 'ok',
        ];

        $str = $this->make($schema);

        $this->assertSame('uri', $str->format());
        $this->assertSame(1, $str->minLength());
        $this->assertSame(255, $str->maxLength());
        $this->assertSame(1, $str->minGraphemes());
        $this->assertSame(255, $str->maxGraphemes());
        $this->assertSame(['ok'], $str->knownValues());
        $this->assertSame(['ok', 'no'], $str->enum());
        $this->assertSame('ok', $str->default());
        $this->assertSame('ok', $str->const());

        $empty = $this->make(['type' => 'string']);
        $this->assertNull($empty->format());
        $this->assertNull($empty->minLength());
        $this->assertNull($empty->maxLength());
        $this->assertNull($empty->minGraphemes());
        $this->assertNull($empty->maxGraphemes());
        $this->assertNull($empty->knownValues());
        $this->assertNull($empty->enum());
        $this->assertNull($empty->default());
        $this->assertNull($empty->const());
        $this->assertSame(['type' => 'string'], $empty->toArray());
    }

    public function test_magic_get_and_schema_return(): void
    {
        $schema = [
            'type' => 'string',
            'meta' => ['info' => ['hint' => 'str']],
        ];

        $str = $this->make($schema);
        $this->assertSame('str', $str->__get('meta.info.hint'));
        $this->assertNull($str->__get('meta.info.missing'));
        $this->assertSame($schema, $str->schema());
    }
}

