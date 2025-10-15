<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Support;

use ArrayIterator;
use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Support\ErrorSchema;
use Blugen\Service\Lexicon\V1\Schema\Support\ErrorsSchema;
use Blugen\Tests\TestCase;
use Blugen\Tests\Unit\Traits\WithArrayableTestTrait;
use Blugen\Tests\Unit\Traits\WithGetTestTrait;
use Blugen\Tests\Unit\Traits\WithSchema;
use Blugen\Tests\Unit\Traits\WithSupportSchemaTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class ErrorsSchemaTest extends TestCase
{
    use WithSchema;
    use WithGetTestTrait;
    use WithArrayableTestTrait;
    use WithSupportSchemaTestTrait;

    private function schema(array $content): ErrorsSchema
    {
        return new ErrorsSchema(new Schema($content));
    }

    public function test_offsetSet_throws_badMethodCall_exception(): void
    {
        $schema = $this->schema([]);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("Schema is read-only");

        $schema->offsetSet('foo', 'bar');
    }

    public function test_offsetUnset_throws_badMethodCall_exception(): void
    {
        $schema = $this->schema([]);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage("Schema is read-only");

        $schema->offsetUnset('foo');
    }

    #[DataProvider('offsetExistsCaseProvider')]
    public function test_offsetExists_returns_expected_boolean(array $content, int $index, bool $expected): void
    {
        $schema = $this->schema($content);

        $this->assertSame($expected, $schema->offsetExists($index));
    }

    public static function offsetExistsCaseProvider(): array
    {
        $schema = [
            [],
        ];

        return [
            [$schema, 0, true],
            [$schema, 1, false],
        ];
    }

    public function test_offsetGet_returns_ErrorSchema_instance(): void
    {
        $schema = $this->schema([[]]);

        $this->assertInstanceOf(ErrorSchema::class, $schema->offsetGet(0));
    }

    public function test_offsetGet_throws_OutOfBoundsException_when_index_not_found(): void
    {
        $schema = $this->schema([]);

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage("Error schema offset 'foo' not found");

        $schema->offsetGet('foo');
    }

    public static function countCaseProvider(): \Generator
    {
        $schema = [];

        foreach (range(1, 5) as $item) {
            $schema[] = ['name' => 'error name', 'description' => 'error description'];

            yield ['schema' => $schema, 'expected' => $item];
        }
    }

    #[DataProvider('countCaseProvider')]
    public function test_count_returns_actual_count(array $schema, int $expected)
    {
        $schema = $this->schema($schema);

        $this->assertSame($expected, $schema->count());
    }

    public function test_getIterator_returns_ArrayIterator_instance(): void
    {
        $schema = $this->schema([]);
        $this->assertInstanceOf(ArrayIterator::class, $schema->getIterator());
    }

    public function test_getIterator_yields_ErrorSchema_instances(): void
    {
        $schema = $this->schema([['name' => 'n']]);

        foreach ($schema as $item) {
            $this->assertInstanceOf(ErrorSchema::class, $item);
        }
    }
}
