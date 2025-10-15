<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\CidLinkSchema;
use PHPUnit\Framework\TestCase;

class CidLinkSchemaTest extends TestCase
{
    private function make(array $schema): CidLinkSchema
    {
        return new CidLinkSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array(): void
    {
        $schema = [
            'type' => 'cid-link',
            'description' => 'CID link field',
            'optional' => null,
        ];

        $cid = $this->make($schema);

        $this->assertSame('cid-link', $cid->type());
        $this->assertSame('CID link field', $cid->description());

        // toArray filters only top-level nulls
        $this->assertSame([
            'type' => 'cid-link',
            'description' => 'CID link field',
        ], $cid->toArray());
    }

    public function test_description_nullable_and_schema_return(): void
    {
        $schema = [
            'type' => 'cid-link',
        ];

        $cid = $this->make($schema);
        $this->assertNull($cid->description());
        $this->assertSame(['type' => 'cid-link'], $cid->toArray());
        $this->assertSame($schema, $cid->schema());
    }

    public function test_magic_get_dotted_paths(): void
    {
        $schema = [
            'type' => 'cid-link',
            'meta' => [
                'codec' => [
                    'name' => 'dag-cbor',
                ],
            ],
        ];

        $cid = $this->make($schema);
        $this->assertSame('dag-cbor', $cid->__get('meta.codec.name'));
        $this->assertNull($cid->__get('meta.codec.unknown'));
    }
}

