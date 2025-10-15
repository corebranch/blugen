<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\BytesSchema;
use PHPUnit\Framework\TestCase;

class BytesSchemaTest extends TestCase
{
    private function make(array $schema): BytesSchema
    {
        return new BytesSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array(): void
    {
        $schema = [
            'type' => 'bytes',
            'description' => 'Opaque bytes',
            'minLength' => 2,
            'maxLength' => null,
        ];

        $bytes = $this->make($schema);

        $this->assertSame('bytes', $bytes->type());
        $this->assertSame('Opaque bytes', $bytes->description());

        // toArray filters only top-level nulls
        $this->assertSame([
            'type' => 'bytes',
            'description' => 'Opaque bytes',
            'minLength' => 2,
        ], $bytes->toArray());
    }

    public function test_min_max_length_accessors(): void
    {
        $schema = [
            'type' => 'bytes',
            'minLength' => 0,
            'maxLength' => 4096,
        ];

        $bytes = $this->make($schema);

        $this->assertSame(0, $bytes->minLength());
        $this->assertSame(4096, $bytes->maxLength());
    }

    public function test_min_max_length_nulls(): void
    {
        $schema = [
            'type' => 'bytes',
        ];

        $bytes = $this->make($schema);

        $this->assertNull($bytes->minLength());
        $this->assertNull($bytes->maxLength());
        $this->assertSame(['type' => 'bytes'], $bytes->toArray());
    }

    public function test_magic_get_dotted_path_and_schema(): void
    {
        $schema = [
            'type' => 'bytes',
            'meta' => ['encoding' => ['name' => 'base64url']],
        ];

        $bytes = $this->make($schema);
        $this->assertSame('base64url', $bytes->__get('meta.encoding.name'));
        $this->assertNull($bytes->__get('meta.encoding.unknown'));
        $this->assertSame($schema, $bytes->schema());
    }
}

