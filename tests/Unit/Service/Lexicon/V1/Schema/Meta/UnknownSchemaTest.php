<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Schema\Meta;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Meta\UnknownSchema;
use PHPUnit\Framework\TestCase;

class UnknownSchemaTest extends TestCase
{
    private function make(array $schema): UnknownSchema
    {
        return new UnknownSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array_filters_only_nulls(): void
    {
        $schema = [
            'type' => 'unknown',
            'description' => 'Unknown type',
            'extra' => null,
        ];

        $unknown = $this->make($schema);

        $this->assertSame('unknown', $unknown->type());
        $this->assertSame('Unknown type', $unknown->description());

        $this->assertSame([
            'type' => 'unknown',
            'description' => 'Unknown type',
        ], $unknown->toArray());
    }

    public function test_schema_and_magic_get_dotted_paths(): void
    {
        $schema = [
            'type' => 'unknown',
            'meta' => ['info' => ['hint' => 'u']],
        ];

        $unknown = $this->make($schema);
        $this->assertSame($schema, $unknown->schema());
        $this->assertSame('u', $unknown->__get('meta.info.hint'));
        $this->assertNull($unknown->__get('meta.info.missing'));
    }
}

