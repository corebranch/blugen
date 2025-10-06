<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints;

use Blugen\Service\Syntax\Constraints\StringType\StringType;
use Blugen\Service\Syntax\Constraints\StringType\StringTypeValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class StringTypeTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): StringTypeValidator
    {
        return new StringTypeValidator();
    }

    public function test_non_string_throws_exception(): void
    {
        $constraint = new StringType([]);

        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(12345, $constraint);
    }

    #[DataProvider('constValueProvider')]
    public function test_const_value(string $const, string $value, bool $isValid): void
    {
        $constraint = new StringType([
                'const' => $const,
        ]);

        $this->validator->validate($value, $constraint);

        if ($isValid) {
            $this->assertNoViolation();
        } else {
            $this
                ->buildViolation($constraint->constValueMessage)
                ->setParameter('{{ value }}', '"' . $value . '"')
                ->setParameter('{{ const }}', '"' . $const . '"')
                ->assertRaised();
        }
    }

    public static function constValueProvider(): \Generator
    {
        yield 'matches const' => ['fixed', 'fixed', true];
        yield 'different from const' => ['fixed', 'other', false];
    }

    #[DataProvider('lengthProvider')]
    public function test_length_constraints(?int $min, ?int $max, string $value, ?string $expectedMessage): void
    {
        $constraint = new StringType([
                'minLength' => $min,
                'maxLength' => $max,
        ]);

        $this->validator->validate($value, $constraint);

        if ($expectedMessage === null) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation($expectedMessage)
                ->setParameter('{{ limit }}', (string) ($min ?? $max))
                ->assertRaised();
        }
    }

    public static function lengthProvider(): \Generator
    {
        yield 'valid length' => [2, 5, 'hey', null];
        yield 'too short'    => [5, null, 'hi', 'String must be at least {{ limit }} characters long'];
        yield 'too long'     => [null, 3, 'hello', 'String must not exceed {{ limit }} characters'];
    }

    #[DataProvider('enumProvider')]
    public function test_enum(array $enum, string $value, bool $isValid): void
    {
        $constraint = new StringType([
                'enum' => $enum,
        ]);

        $this->validator->validate($value, $constraint);

        if ($isValid) {
            $this->assertNoViolation();
        } else {
            $this
                ->buildViolation($constraint->allowedValuesMessage)
                ->setParameter('{{ allowedValues }}', '"' . implode('", "', $enum) . '"')
                ->assertRaised();
        }
    }

    public static function enumProvider(): \Generator
    {
        yield 'in list' => [['foo', 'bar'], 'foo', true];
        yield 'not in list' => [['foo', 'bar'], 'baz', false];
    }

    #[DataProvider('graphemeProvider')]
    public function test_grapheme_length(?int $min, ?int $max, string $value, ?string $expectedMessage): void
    {
        $constraint = new StringType([
                'minGraphemes' => $min,
                'maxGraphemes' => $max,
        ]);

        $this->validator->validate($value, $constraint);

        if ($expectedMessage === null) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation($expectedMessage)
                ->setParameter('{{ limit }}', (string) ($min ?? $max))
                ->assertRaised();
        }
    }

    public static function graphemeProvider(): \Generator
    {
        yield 'valid grapheme count' => [2, 5, 'héy', null]; // 3 graphemes
        yield 'too short graphemes' => [5, null, 'hé', 'String must contain at least {{ limit }} grapheme characters'];
        yield 'too long graphemes'  => [null, 2, 'héllo', 'String must not exceed {{ limit }} grapheme characters'];
    }
}
