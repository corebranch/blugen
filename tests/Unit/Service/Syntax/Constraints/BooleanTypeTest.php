<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints;

use Blugen\Service\Syntax\Constraints\BooleanType\BooleanType;
use Blugen\Service\Syntax\Constraints\BooleanType\BooleanTypeValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class BooleanTypeTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): BooleanTypeValidator
    {
        return new BooleanTypeValidator();
    }

    #[DataProvider('validBooleanProvider')]
    public function test_valid_boolean_values(bool $value): void
    {
        $constraint = new BooleanType();

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public static function validBooleanProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    #[DataProvider('invalidBooleanProvider')]
    public function test_invalid_boolean_values(mixed $invalidValue): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = new BooleanType();
        $this->validator->validate($invalidValue, $constraint);
    }

    public static function invalidBooleanProvider(): array
    {
        return [
            ['string'],
            [1.4],
            [1],
            [new stdClass()],
            [[]],
            [null],
        ];
    }

    public function test_violation_when_const_not_matching_value(): void
    {
        $constraint = new BooleanType([
            'type' => 'boolean',
            'const' => false,
        ]);

        $this->validator->validate(true, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ actual }}', 'true')
            ->setParameter('{{ must }}', 'false')
            ->assertRaised();
    }

    public function test_valid_when_value_matches_const(): void
    {
        $constraint = new BooleanType([
            'type' => 'boolean',
            'const' => false,
        ]);

        $this->validator->validate(false, $constraint);

        $this->assertNoViolation();
    }

    public function test_false_value_is_valid(): void
    {
        $constraint = new BooleanType();

        $this->validator->validate(false, $constraint);

        $this->assertNoViolation();
    }

    public function test_true_value_is_valid(): void
    {
        $constraint = new BooleanType();

        $this->validator->validate(true, $constraint);

        $this->assertNoViolation();
    }
}
