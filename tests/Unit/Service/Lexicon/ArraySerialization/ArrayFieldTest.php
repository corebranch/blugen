<?php

namespace Blugen\Tests\Unit\Service\Lexicon\ArraySerialization;

use Blugen\Service\Lexicon\ArraySerialization\ArrayField;
use Blugen\Tests\TestCase;

class ArrayFieldTest extends TestCase
{
    private ArrayField $field;

    protected function setUp(): void
    {
        parent::setUp();
        $this->field = new ArrayField('foo', 'bar');
    }

    public function test_constructor_can_initialize_the_instance(): void
    {
        $this->assertInstanceOf(ArrayField::class, $this->field);
    }

    public function test_key_returns_item_key(): void
    {
        $this->assertSame('foo', $this->field->key());
    }

    public function test_expression_returns_item_value(): void
    {
        $this->assertSame('bar', $this->field->expression());
    }

    public function test_fullExpression_returns_item(): void
    {
        $this->assertSame("'foo' => bar", $this->field->fullExpression());
    }

    public function test_toString_returns_expected_output(): void
    {
        $this->assertSame("'foo' => bar", $this->field->__toString());
        $this->assertSame("'foo' => bar", (string) $this->field);
    }
}
