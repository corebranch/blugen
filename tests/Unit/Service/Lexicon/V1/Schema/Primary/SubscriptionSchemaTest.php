<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Schema\Primary;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Container\ParamsSchema;
use Blugen\Service\Lexicon\V1\Schema\Primary\SubscriptionSchema;
use Blugen\Service\Lexicon\V1\Schema\Support\ErrorsSchema;
use Blugen\Service\Lexicon\V1\Schema\Support\MessageSchema;
use Blugen\Tests\TestCase;
use Blugen\Tests\Unit\Traits\WithArrayableTestTrait;
use Blugen\Tests\Unit\Traits\WithGetTestTrait;
use Blugen\Tests\Unit\Traits\WithSchema;
use Blugen\Tests\Unit\Traits\WithSchemaTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class SubscriptionSchemaTest extends TestCase
{
    use WithSchema;
    use WithGetTestTrait;
    use WithSchemaTestTrait;
    use WithArrayableTestTrait;

    public static function optionalFieldProvider(): array
    {
        return [
            ['parameters'],
            ['message'],
            ['errors'],
        ];
    }

    #[DataProvider('optionalFieldProvider')]
    public function test_field_is_optional(string $fieldName): void
    {
        $schema = $this->schema([
            // missing field
        ]);

        $this->assertNull($schema->{$fieldName}());
    }

    #[DataProvider('fieldExpectedValueProvider')]
    public function test_field_returns_expected_value(
        string $fieldName,
        string $expectedInstanceOfFQCN,
        array  $expectedToArrayResult
    ): void
    {
        $schema = $this->schema([
            $fieldName => $expectedToArrayResult
        ]);

        $this->assertInstanceOf($expectedInstanceOfFQCN, $instance = $schema->{$fieldName}());
        $this->assertSame($expectedToArrayResult, $instance->toArray());
    }

    public static function fieldExpectedValueProvider(): \Generator
    {
        yield 'parameters field' => [
            'fieldName' => 'parameters',
            'expectedInstanceOfFQCN' => ParamsSchema::class,
            'expectedToArrayResult' => ['foo', 'dag', 'cbor']
        ];

        yield 'message field' => [
            'fieldName' => 'message',
            'expectedInstanceOfFQCN' => MessageSchema::class,
            'expectedToArrayResult' => [false, 'string', 0, true]
        ];

        yield 'errors field' => [
            'fieldName' => 'errors',
            'expectedInstanceOfFQCN' => ErrorsSchema::class,
            'expectedToArrayResult' => [
                ['name' => 'error name #1', 'description' => 'error description #1'],
                ['name' => 'error name #2', 'description' => 'error description #2']
            ]
        ];
    }


    private function schema(array $content): SubscriptionSchema
    {
        return new SubscriptionSchema(new Schema($content));
    }
}
