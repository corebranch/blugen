<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints;

use Blugen\Service\Syntax\Constraints\IntegerType\IntegerType;
use Blugen\Service\Syntax\Constraints\IntegerType\IntegerTypeValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class IntegerTypeTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): IntegerTypeValidator
    {
        return new IntegerTypeValidator();
    }

    public function test_is_invalid_when_value_is_not_same_with_const(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'const' => 12
        ]);

        $this->validator->validate(21, $constraint);

        $this->buildViolation($constraint->constMessage)
            ->setParameter('{{ const }}', 12)
            ->assertRaised();
    }

    public function test_is_valid_when_value_is_same_with_const(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'const' => 94
        ]);

        $this->validator->validate(94, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_invalid_when_value_is_less_than_min_limit(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'minimum' => 48
        ]);

        $this->validator->validate(47, $constraint);

        $this->buildViolation($constraint->minMessage)
            ->setParameter('{{ value }}', 47)
            ->setParameter('{{ min }}', 48)
            ->assertRaised();
    }

    public function test_is_valid_when_value_at_exact_min_limit(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'minimum' => 54
        ]);

        $this->validator->validate(54, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_value_greater_than_min_limit(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'minimum' => 65
        ]);

        $this->validator->validate(66, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_invalid_when_value_greater_than_max_limit(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'maximum' => 58
        ]);

        $this->validator->validate(59, $constraint);

        $this->buildViolation($constraint->maxMessage)
            ->setParameter('{{ value }}', 59)
            ->setParameter('{{ max }}', 58)
            ->assertRaised();
    }

    public function test_is_valid_when_value_at_exact_max_limit(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'maximum' => 58
        ]);

        $this->validator->validate(58, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_value_less_than_max_limit(): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'maximum' => 569
        ]);

        $this->validator->validate(568, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('invalidEnumProvider')]
    public function test_is_invalid_when_value_does_not_containing_by_enum(array $enum, int $value): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'enum' => $enum
        ]);

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->notInEnumMessage)
            ->setParameter('{{ enumValues }}', implode(', ', $enum))
            ->assertRaised();
    }

    public static function invalidEnumProvider(): array
    {
        return [
            [[3, 4, 5, 6], 9],
            [[9292], 9393]
        ];
    }

    public static function validEnumProvider(): array
    {
        return [
            [[1, 2, 3, 4], 1],
            [[9, 8, 7, 6,], 8]
        ];
    }

    #[DataProvider('validEnumProvider')]
    public function test_is_valid_when_enum_contains_it(array $enum, int $value): void
    {
        $constraint = new IntegerType([
            'type' => 'integer',
            'enum' => $enum
        ]);

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('invalidValueProvider')]
    public function test_throws_type_exception_when_used_(mixed $value): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = new IntegerType(['type' => 'integer',]);

        $this->validator->validate($value, $constraint);
    }

    public static function invalidValueProvider(): array
    {
        return [
            [true],
            [false],
            [null],
            [1.2],
            [new \stdClass()],
            ['2.2'],
            ['5']
        ];
    }
}
