<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\Language\Language;
use Blugen\Service\Syntax\Constraints\StringType\Format\Language\LanguageValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class LanguageTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): LanguageValidator
    {
        return new LanguageValidator();
    }

    #[DataProvider('validTagProvider')]
    public function test_is_valid(string $tag): void
    {
        $constraint = new Language();

        $this->validator->validate($tag, $constraint);

        $this->assertNoViolation();
    }

    public static function validTagProvider(): array
    {
        return [
            ['en'],
            ['az'],
            ['az-AZ'],
            ['en-US']
        ];
    }

    #[DataProvider('invalidTagProvider')]
    public function test_is_invalid(string $invalidTag): void
    {
        $constraint = new Language();

        $this->validator->validate($invalidTag, $constraint);

        $this->buildViolation($constraint->invalidMessage)
            ->assertRaised();
    }

    public static function invalidTagProvider(): array
    {
        return [
            'invalid foo!!!' => ['foo!!'],
            'invalid bar' => ['barr']
        ];
    }
}
