<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\BooleanSchema;
use PHPUnit\Framework\TestCase;

class BooleanSchemaTest extends TestCase
{
    private function make(array $schema): BooleanSchema
    {
        return new BooleanSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array(): void
    {
        $schema = [
            'type' => 'boolean',
            'description' => 'A boolean flag',
            'default' => true,
            'const' => null,
        ];

        $bool = $this->make($schema);

        $this->assertSame('boolean', $bool->type());
        $this->assertSame('A boolean flag', $bool->description());

        // toArray filters only top-level nulls (const)
        $this->assertSame([
            'type' => 'boolean',
            'description' => 'A boolean flag',
            'default' => true,
        ], $bool->toArray());
    }

    public function test_default_and_const_accessors(): void
    {
        $schema = [
            'type' => 'boolean',
            'default' => false,
            'const' => true,
        ];

        $bool = $this->make($schema);

        $this->assertFalse($bool->default());
        $this->assertTrue($bool->const());
    }

    public function test_accessors_return_null_when_not_set(): void
    {
        $schema = [
            'type' => 'boolean',
        ];

        $bool = $this->make($schema);

        $this->assertNull($bool->default());
        $this->assertNull($bool->const());
        $this->assertSame(['type' => 'boolean'], $bool->toArray());
    }

    public function test_magic_get_supports_unknown_paths(): void
    {
        $schema = [
            'type' => 'boolean',
            'meta' => ['note' => 'x'],
        ];

        $bool = $this->make($schema);
        $this->assertSame('x', $bool->__get('meta.note'));
        $this->assertNull($bool->__get('meta.missing'));
    }
}

