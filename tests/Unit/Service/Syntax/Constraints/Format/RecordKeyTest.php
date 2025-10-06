<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\RecordKey\RecordKey;
use Blugen\Service\Syntax\Constraints\StringType\Format\RecordKey\RecordKeyValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class RecordKeyTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): RecordKeyValidator
    {
        return new RecordKeyValidator();
    }

    public function test_is_invalid_when_rkey_length_greater_than_max_limit(): void
    {
        $constraint = new RecordKey();
        $length = $constraint::MAX_LENGTH + 1;

        $rkey = str_repeat('a', $length);

        $this->validator->validate($rkey, $constraint);

        $this->buildViolation($constraint->invalidLengthMessage)
            ->setParameter('{{ min }}', $constraint::MIN_LENGTH)
            ->setParameter('{{ max }}', $constraint::MAX_LENGTH)
            ->setParameter('{{ actualLength }}', strlen($rkey))
            ->assertRaised();
    }

    public function test_is_invalid_when_rkey_length_less_than_min_limit(): void
    {
        $constraint = new RecordKey();
        $length = $constraint::MIN_LENGTH - 1;

        $rkey = str_repeat('a', $length);

        $this->validator->validate($rkey, $constraint);

        $this->buildViolation($constraint->invalidLengthMessage)
            ->setParameter('{{ min }}', $constraint::MIN_LENGTH)
            ->setParameter('{{ max }}', $constraint::MAX_LENGTH)
            ->setParameter('{{ actualLength }}', strlen($rkey))
            ->assertRaised();
    }

    public function test_is_valid_when_rkey_length_exact_at_max_limit(): void
    {
        $constraint = new RecordKey();
        $rkey = str_repeat('a', $constraint::MAX_LENGTH);

        $this->validator->validate($rkey, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_rkey_length_exact_at_min_limit(): void
    {
        $constraint = new RecordKey();
        $rkey = str_repeat('a', $constraint::MIN_LENGTH);

        $this->validator->validate($rkey, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('invalidCharContainedRkeyProvider')]
    public function test_is_invalid_when_rkey_has_disallowed_chars(string $invalidRkey): void
    {
        $constraint = new RecordKey();

        $this->validator->validate($invalidRkey, $constraint);

        $this->buildViolation($constraint->regexFailedMessage)
            ->assertRaised();
    }

    public static function invalidCharContainedRkeyProvider(): array
    {
        return [
            ['?'],
            ['/'],
        ];
    }

    public function test_rkey_is_invalid_when_value_is_dot(): void
    {
        $rkey = '.';

        $constraint = new RecordKey();

        $this->validator->validate($rkey, $constraint);

        $this->buildViolation($constraint->cantBeDotMessage)
            ->assertRaised();
    }

    public function test_rkey_is_invalid_when_value_is_twice_dot(): void
    {
        $rkey = '..';

        $constraint = new RecordKey();

        $this->validator->validate($rkey, $constraint);

        $this->buildViolation($constraint->cantBeDotMessage)
            ->assertRaised();
    }
}
