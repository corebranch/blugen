<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Schema\Meta;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Meta\UnionSchema;
use PHPUnit\Framework\TestCase;

class UnionSchemaTest extends TestCase
{
    private function make(array $schema): UnionSchema
    {
        return new UnionSchema(new Schema($schema));
    }

    public function test_type_description_refs_closed_and_to_array(): void
    {
        $schema = [
            'type' => 'union',
            'description' => 'Union of refs',
            'refs' => ['com.example#a', 'com.example#b'],
            'closed' => true,
            'optional' => null,
        ];

        $union = $this->make($schema);

        $this->assertSame('union', $union->type());
        $this->assertSame('Union of refs', $union->description());
        $this->assertSame(['com.example#a', 'com.example#b'], $union->refs());
        $this->assertTrue($union->closed());

        // toArray filters only top-level nulls
        $this->assertSame([
            'type' => 'union',
            'description' => 'Union of refs',
            'refs' => ['com.example#a', 'com.example#b'],
            'closed' => true,
        ], $union->toArray());
    }

    public function test_defaults_when_refs_absent_and_closed_not_set(): void
    {
        $schema = [
            'type' => 'union',
        ];

        $union = $this->make($schema);
        $this->assertSame([], $union->refs());
        $this->assertFalse($union->closed());
        $this->assertSame(['type' => 'union'], $union->toArray());
        $this->assertSame($schema, $union->schema());
    }

    public function test_magic_get_dotted_paths(): void
    {
        $schema = [
            'type' => 'union',
            'meta' => ['info' => ['note' => 'u']],
        ];

        $union = $this->make($schema);
        $this->assertSame('u', $union->__get('meta.info.note'));
        $this->assertNull($union->__get('meta.info.missing'));
    }
}

