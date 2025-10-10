<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints;

use Blugen\Service\Syntax\Constraints\ArrayType\ArrayType;
use Blugen\Service\Syntax\Constraints\ArrayType\ArrayTypeValidator;
use Blugen\Service\Syntax\Constraints\StringType\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ArrayTypeTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ArrayTypeValidator
    {
        return new ArrayTypeValidator();
    }

    public function test_throws_exception_when_value_is_not_array(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
        ]);

        $this->validator->validate('not-an-array', $constraint);
    }

    public function test_expects_array_type_constraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        // Using wrong constraint intentionally
        $this->validator->validate([], new StringType());
    }

    public function test_valid_when_items_are_all_valid(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
        ]);

        $this->validator->validate([1, 2, 3], $constraint);

        $this->assertNoViolation();
    }

    public function test_invalid_when_any_item_is_invalid_reports_index_and_type(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
        ]);

        $this->validator->validate([1, 'oops', 3], $constraint);

        $this->buildViolation($constraint->invalidMessage)
            ->setParameter('{{ type }}', '"integer"')
            ->setParameter('{{ index }}', 1)
            ->assertRaised();
    }

    public function test_is_invalid_when_array_too_short(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
            'minLength' => 3,
        ]);

        $this->validator->validate([1, 2], $constraint);

        $this->buildViolation($constraint->invalidMinLengthMessage)
            ->setParameter('{{ limit }}', 3)
            ->setParameter('{{ actualCount }}', 2)
            ->assertRaised();
    }

    public function test_is_valid_when_length_equals_min(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
            'minLength' => 2,
        ]);

        $this->validator->validate([1, 2], $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_length_greater_than_min(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
            'minLength' => 2,
        ]);

        $this->validator->validate([1, 2, 3], $constraint);

        $this->assertNoViolation();
    }

    public function test_is_invalid_when_array_too_long(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
            'maxLength' => 2,
        ]);

        $this->validator->validate([1, 2, 3], $constraint);

        $this->buildViolation($constraint->invalidMaxLengthMessage)
            ->setParameter('{{ limit }}', 2)
            ->setParameter('{{ actualCount }}', 3)
            ->assertRaised();
    }

    public function test_is_valid_when_length_equals_max(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
            'maxLength' => 3,
        ]);

        $this->validator->validate([1, 2, 3], $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_length_less_than_max(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
            'maxLength' => 4,
        ]);

        $this->validator->validate([1, 2, 3], $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('invalidValueProvider')]
    public function test_throws_type_exception_for_invalid_value_types(mixed $value): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = new ArrayType([
            'type' => 'array',
            'items' => ['type' => 'integer'],
        ]);

        $this->validator->validate($value, $constraint);
    }

    public static function invalidValueProvider(): array
    {
        return [
            ['string'],
            [1.5],
            [1],
            [new \stdClass()],
            [null],
            [true],
            [false],
        ];
    }

    public function test_can_validate_nested_arrays(): void
    {
        $constraint = new ArrayType([
            'type' => 'array',
            'items' => [
                'type' => 'array',
                'items' => ['type' => 'integer']
            ],
        ]);

        $this->validator->validate([[1], [2], 3], $constraint);

        $this->buildViolation($constraint->invalidMessage)
            ->setParameter('{{ type }}', '"array"')
            ->setParameter('{{ index }}', 2)
            ->assertRaised();
    }
}
