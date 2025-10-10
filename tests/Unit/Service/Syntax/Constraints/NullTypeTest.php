<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints;

use Blugen\Service\Syntax\Constraints\NullType\NullType;
use Blugen\Service\Syntax\Constraints\NullType\NullTypeValidator;
use Blugen\Service\Syntax\Constraints\StringType\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class NullTypeTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): NullTypeValidator
    {
        return new NullTypeValidator();
    }

    public function test_null_is_valid(): void
    {
        $constraint = new NullType([]);

        $this->validator->validate(null, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('nonNullValueProvider')]
    public function test_throws_exception_for_non_null_value(mixed $value): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = new NullType([]);

        $this->validator->validate($value, $constraint);
    }

    public static function nonNullValueProvider(): array
    {
        return [
            ['null'],
            [1.4],
            [1],
            [false],
            [true],
            [new \stdClass()],
        ];
    }

    public function test_expects_null_type_constraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(null, new StringType([]));
    }
}
