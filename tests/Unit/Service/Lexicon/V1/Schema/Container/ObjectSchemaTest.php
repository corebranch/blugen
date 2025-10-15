<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Field;

use Blugen\Service\Lexicon\V1\Exceptions\MissingRequiredFieldException;
use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Concrete\StringSchema;
use Blugen\Service\Lexicon\V1\Schema\Container\ObjectSchema;
use Blugen\Tests\Unit\Traits\WithArrayableTestTrait;
use Blugen\Tests\Unit\Traits\WithGetTestTrait;
use Blugen\Tests\Unit\Traits\WithSchema;
use Blugen\Tests\Unit\Traits\WithSchemaTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ObjectSchemaTest extends TestCase
{
    use WithSchema;
    use WithGetTestTrait;
    use WithSchemaTestTrait;
    use WithArrayableTestTrait;

    #[DataProvider('propertyCaseProvider')]
    public function test_properties_returns_expected_value(array $properties, string $expected): void
    {
        $schema = $this->schema([
            'type' => 'object',
            'properties' => $properties
        ]);

        foreach ($schema->properties() as $property) {
            $this->assertInstanceOf($expected, $property);
        }
    }

    public static function propertyCaseProvider(): \Generator
    {
        yield 'string property' => [
            'properties' => ['field1' => ['type' => 'string', 'format' => 'did']],
            'expected' => StringSchema::class,
        ];
    }

    public function test_properties_is_required(): void
    {
        $schema = $this->schema([
            'type' => 'object',
            // missing properties
        ]);

        $this->expectException(MissingRequiredFieldException::class);
        $this->expectExceptionMessage("array property 'properties'");

        $schema->properties();
    }

    public function test_nullable_returns_expected_value(): void
    {
        $schema = $this->schema([
            'type' => 'object',
            'nullable' => ['field1', 'field2'],
        ]);

        $this->assertSame(['field1', 'field2'], $schema->nullable());
    }

    public function test_nullable_is_optional(): void
    {
        $schema = $this->schema([
            'type' => 'object',
            // missing nullable
        ]);

        $this->assertNull($schema->nullable());
    }

    public function test_required_returns_expected_value(): void
    {
        $schema = $this->schema([
            'type' => 'object',
            'required' => ['field1', 'field2'],
        ]);

        $this->assertSame(['field1', 'field2'], $schema->required());
    }

    public function test_required_is_optional(): void
    {
        $schema = $this->schema([
            'type' => 'object',
            // missing required
        ]);

        $this->assertNull($schema->required());
    }

    private function schema(array $content): ObjectSchema
    {
        return new ObjectSchema(new Schema($content));
    }
}
