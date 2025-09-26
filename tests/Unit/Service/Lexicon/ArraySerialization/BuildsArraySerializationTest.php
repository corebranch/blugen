<?php

namespace Blugen\Tests\Unit\Service\Lexicon\ArraySerialization;

use Blugen\Service\Lexicon\ArraySerialization\ArrayField;
use Blugen\Service\Lexicon\ArraySerialization\BuildsArraySerialization;
use Blugen\Tests\TestCase;
use ReflectionClass;

class BuildsArraySerializationTest extends TestCase
{
    public function test_addField_adds_field_to_fields_array(): void
    {
        $field = $this->createMock(ArrayField::class);
        $instance = new Foo();
        $instance->addField($field);

        $this->assertSame([$field], $instance->fields());
    }

    public function test_fields_returns_fields_array(): void
    {
        $instance = new Foo();
        $actual = $instance->fields();

        $this->assertIsArray($actual);
        $this->assertSame([], $actual);
    }

    public function test_generateBody_returns_expected_output(): void
    {
        $actual = new Foo();

        foreach(['foo' => 'bar', 'bar' => 'baz'] as $key => $value) {
            $actual->addField(new ArrayField($key, $value));
        }

        $this->assertSame("return ['foo' => bar, 'bar' => baz];", $actual->generateBody());
    }
}

final class Foo
{
    use BuildsArraySerialization;
}
