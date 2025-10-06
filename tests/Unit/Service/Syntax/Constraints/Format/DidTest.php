<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\Did\Did;
use Blugen\Service\Syntax\Constraints\StringType\Format\Did\DidValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class DidTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): DidValidator
    {
        return new DidValidator();
    }

    public function test_did_requires_prefix(): void
    {
        $did = 'prefix:xlc:plc';
        $constraint = new Did();

        $this->validator->validate($did, $constraint);

        $this->buildViolation($constraint->requiresPrefix)
            ->setParameter('{{ prefix }}', $constraint::PREFIX)
            ->assertRaised();
    }

    #[DataProvider('disallowedDidCharProvider')]
    public function test_disallowed_chars_is_invalid(string $invalidDid): void
    {
        $constraint = new Did();
        $this->validator->validate($invalidDid, $constraint);

        $this->buildViolation($constraint->invalidCharsMessage)
            ->assertRaised();
    }

    public static function disallowedDidCharProvider(): \Generator
    {
        yield 'space not allowed' => ['did:method:val ue'];
        yield 'at-sign not allowed' => ['did:method:val@id'];
        yield 'hash not allowed' => ['did:method:val#frag'];
        yield 'question mark not allowed' => ['did:method:val?query'];
        yield 'plus not allowed' => ['did:method:val+id'];
        yield 'slash not allowed' => ['did:method:val/path'];
        yield 'asterisk not allowed' => ['did:method:val*id'];
        yield 'exclamation mark not allowed' => ['did:method:val!id'];
        yield 'ampersand not allowed' => ['did:method:val&id'];
        yield 'comma not allowed' => ['did:method:val,id'];
    }

    public function test_is_valid_when_did_part_count_greater_than_three(): void
    {
        $constraint = new Did();
        $did = 'did:plc:bar:baz';

        $this->validator->validate($did, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_did_part_count_at_exact_limit(): void
    {
        $constraint = new Did();
        $did = 'did:plc:bar';

        $this->validator->validate($did, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_invalid_when_did_part_count_less_than_three(): void
    {
        $constraint = new Did();
        $did = 'did:foo';

        $this->validator->validate($did, $constraint);

        $this->buildViolation($constraint->requiresMethodMessage)
            ->assertRaised();
    }

    public function test_is_invalid_when_did_not_lowercase(): void
    {
        $constraint = new Did();
        $did = 'did:PLC:xyz';

        $this->validator->validate($did, $constraint);

        $this->buildViolation($constraint->mustBeLowercaseMessage)
            ->assertRaised();
    }

    public function test_did_is_invalid_when_used_colon_at_end(): void
    {
        $did = 'did:plc:xyz:';
        $constraint = new Did();

        $this->validator->validate($did, $constraint);


        $this->buildViolation($constraint->cantEndWithColonOrPercent)
            ->assertRaised();
    }

    public function test_did_can_be_max_2048_chars(): void
    {
        $constraint = new Did();
        $did = $constraint::PREFIX . 'x:'. str_repeat(
            'x',
            $constraint::ALLOWED_LENGTH - strlen($constraint::PREFIX) - 2
        );

        $this->validator->validate($did, $constraint);

        $this->assertNoViolation();
    }

    public function test_did_is_invalid_when_length_greater_than_allowed(): void
    {
        $constraint = new Did();
        $did = $constraint::PREFIX . 'x:' . str_repeat(
            'x',
            $constraint::ALLOWED_LENGTH - strlen($constraint::PREFIX) - 2 + 1
        );

        $this->validator->validate($did, $constraint);

        $this->buildViolation($constraint->tooLongMessage)
            ->setParameter('{{ maxLength }}', $constraint::ALLOWED_LENGTH)
            ->assertRaised();
    }
}
