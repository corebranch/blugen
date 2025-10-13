<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Schema\Meta;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Meta\RefSchema;
use PHPUnit\Framework\TestCase;

class RefSchemaTest extends TestCase
{
    private function make(array $schema): RefSchema
    {
        return new RefSchema(new Schema($schema));
    }

    public function test_type_description_ref_and_to_array(): void
    {
        $schema = [
            'type' => 'ref',
            'description' => 'Reference to another def',
            'ref' => 'com.example.def#item',
            'extra' => null,
        ];

        $ref = $this->make($schema);

        $this->assertSame('ref', $ref->type());
        $this->assertSame('Reference to another def', $ref->description());
        $this->assertSame('com.example.def#item', $ref->ref());

        $this->assertSame([
            'type' => 'ref',
            'description' => 'Reference to another def',
            'ref' => 'com.example.def#item',
        ], $ref->toArray());
    }

    public function test_schema_and_magic_get_dotted_paths(): void
    {
        $schema = [
            'type' => 'ref',
            'ref' => 'com.example#x',
            'meta' => ['info' => ['note' => 'ref']],
        ];

        $ref = $this->make($schema);
        $this->assertSame($schema, $ref->schema());
        $this->assertSame('ref', $ref->__get('meta.info.note'));
        $this->assertNull($ref->__get('meta.missing'));
    }
}

