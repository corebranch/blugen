<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\BlobSchema;
use PHPUnit\Framework\TestCase;

class BlobSchemaTest extends TestCase
{
    private function make(array $schema): BlobSchema
    {
        return new BlobSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array(): void
    {
        $schema = [
            'type' => 'blob',
            'description' => 'Binary file',
            'accept' => ['image/png', 'image/jpeg'],
            'maxSize' => 1024,
            'nullable' => null,
        ];

        $blob = $this->make($schema);

        $this->assertSame('blob', $blob->type());
        $this->assertSame('Binary file', $blob->description());

        // toArray filters only top-level nulls
        $this->assertSame([
            'type' => 'blob',
            'description' => 'Binary file',
            'accept' => ['image/png', 'image/jpeg'],
            'maxSize' => 1024,
        ], $blob->toArray());
    }

    public function test_accept_and_max_size_accessors(): void
    {
        $schema = [
            'type' => 'blob',
            'accept' => ['application/pdf'],
            'maxSize' => 2048,
        ];

        $blob = $this->make($schema);

        $this->assertSame(['application/pdf'], $blob->accept());
        $this->assertSame(2048, $blob->maxSize());
        // dotted access via underlying Schema::__get
        $this->assertSame('application/pdf', $blob->__get('accept.0'));
    }

    public function test_accept_and_max_size_nulls(): void
    {
        $schema = [
            'type' => 'blob',
            'accept' => null,
            'maxSize' => null,
        ];

        $blob = $this->make($schema);

        $this->assertNull($blob->accept());
        $this->assertNull($blob->maxSize());
        $this->assertSame(['type' => 'blob'], $blob->toArray());
    }

    public function test_schema_returns_underlying_array(): void
    {
        $schema = [
            'type' => 'blob',
            'description' => 'X',
        ];

        $blob = $this->make($schema);
        $this->assertSame($schema, $blob->schema());
    }
}

