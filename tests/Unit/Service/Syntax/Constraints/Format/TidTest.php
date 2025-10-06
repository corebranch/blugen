<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\Tid\Tid;
use Blugen\Service\Syntax\Constraints\StringType\Format\Tid\TidValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class TidTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): TidValidator
    {
        return new TidValidator();
    }

    public function test_is_invalid_when_tid_length_less_than_const(): void
    {
        $constraint = new Tid();
        $value = '234567abcdef';

        $this->assertTrue(strlen($value) < $constraint::LENGTH);
        $this->assertTrue(strlen($value) + 1 === $constraint::LENGTH);

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->invalidLengthMessage)
            ->setParameter('{{ length }}', $constraint::LENGTH)
            ->setParameter('{{ actualLength }}', strlen($value))
            ->buildNextViolation($constraint->invalidRegexMessage)
            ->assertRaised();
    }

    public function test_is_invalid_when_tid_length_greater_than_const(): void
    {
        $constraint = new Tid();
        $value = '234567abcdefqr';

        $this->assertTrue(strlen($value) > $constraint::LENGTH);
        $this->assertTrue(strlen($value) - 1 === $constraint::LENGTH);

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->invalidLengthMessage)
            ->setParameter('{{ length }}', $constraint::LENGTH)
            ->setParameter('{{ actualLength }}', strlen($value))
            ->buildNextViolation($constraint->invalidRegexMessage)
            ->assertRaised();
    }

    public function test_is_valid_when_tid_at_exactly_const(): void
    {
        $constraint = new Tid();
        $value = '234567abcdefq';

        $this->assertTrue(strlen($value) === $constraint::LENGTH);

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }
}
