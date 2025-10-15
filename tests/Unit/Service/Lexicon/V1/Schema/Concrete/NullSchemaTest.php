<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Schema\Concrete;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\NullSchema;
use PHPUnit\Framework\TestCase;

class NullSchemaTest extends TestCase
{
    private function make(array $schema): NullSchema
    {
        return new NullSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array(): void
    {
        $schema = [
            'type' => 'null',
            'description' => 'Represents null',
            'extra' => null,
        ];

        $null = $this->make($schema);

        $this->assertSame('null', $null->type());
        $this->assertSame('Represents null', $null->description());

        // toArray filters only top-level nulls
        $this->assertSame([
            'type' => 'null',
            'description' => 'Represents null',
        ], $null->toArray());
    }

    public function test_description_nullable_and_schema_return(): void
    {
        $schema = [
            'type' => 'null',
        ];

        $null = $this->make($schema);
        $this->assertNull($null->description());
        $this->assertSame(['type' => 'null'], $null->toArray());
        $this->assertSame($schema, $null->schema());
    }

    public function test_magic_get_dotted_paths(): void
    {
        $schema = [
            'type' => 'null',
            'meta' => ['info' => ['tag' => 'none']],
        ];

        $null = $this->make($schema);
        $this->assertSame('none', $null->__get('meta.info.tag'));
        $this->assertNull($null->__get('meta.info.missing'));
    }
}

