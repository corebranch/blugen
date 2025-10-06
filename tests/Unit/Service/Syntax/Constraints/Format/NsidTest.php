<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\Nsid\Nsid;
use Blugen\Service\Syntax\Constraints\StringType\Format\Nsid\NsidValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class NsidTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): NsidValidator
    {
        return new NsidValidator();
    }

    public function test_is_invalid_when_nsid_length_greater_than_max_limit(): void
    {
        $constraint = new Nsid();

        $oversized = implode($constraint::SEPARATOR, array_map(
            fn(int $index) => str_repeat(chr(rand(97, 122)), $constraint::MAX_PART_LENGTH + 1),
            range(1, 3),
        ));

        $this->validator->validate($oversized, $constraint);

        $this->buildViolation($constraint->partTooLongMessage)
            ->setParameter('{{ maxLimit }}', $constraint::MAX_PART_LENGTH)
            ->assertRaised();
    }

    public static function disallowedCharProvider(): \Generator
    {
        yield ['äls.example.www'];
        yield ['org.dərs.web'];
        yield ['co.bus?.m'];
        yield ['net.posts.dağ'];
    }

    #[DataProvider('disallowedCharProvider')]
    public function test_is_invalid_disallowed_chars(string $char): void
    {
        $constraint = new Nsid();

        $this->validator->validate($char, $constraint);

        $this->buildViolation($constraint->disallowedCharsMessage)
            ->setParameter('{{ value }}', "\"$char\"")
            ->assertRaised();
    }

    public function test_is_invalid_when_segments_count_less_than_min_limit(): void
    {
        $constraint = new Nsid();
        $nsid = 'com.example';

        $this->validator->validate($nsid, $constraint);

        $this->buildViolation($constraint->tooFewPartsMessage)
            ->setParameter('{{ value }}', "\"$nsid\"")
            ->setParameter('{{ minLimit }}', Nsid::MIN_PARTS)
            ->assertRaised();
    }

    public function test_is_valid_when_segments_count_at_exact_min_limit(): void
    {
        $constraint = new Nsid();
        $nsid = 'com.example.www';

        $this->validator->validate($nsid, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_segments_count_greater_than_min_limit(): void
    {
        $constraint = new Nsid();
        $nsid = 'dev.shahmal1yev.blog.posts';

        $this->validator->validate($nsid, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('nsidWithEmptyPartProvider')]
    public function test_is_invalid_when_nsid_have_empty_part(string $invalidNsid): void
    {
        $constraint = new Nsid();

        $this->validator->validate($invalidNsid, $constraint);

        $this->buildViolation($constraint->emptyPartMessage)
            ->assertRaised();
    }

    public static function nsidWithEmptyPartProvider(): \Generator
    {
        yield ['com.example.'];
        yield ['dev..www'];
    }

    public function test_is_valid_when_nsid_part_count_less_than_max_limit(): void
    {
        $constraint = new Nsid();
        $prefix = 'com.example.';
        $nsid = $prefix . str_repeat('w', $constraint::MAX_PART_LENGTH - 1);

        $this->validator->validate($nsid, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_nsid_part_count_at_exact_limit(): void
    {
        $constraint = new Nsid();
        $prefix = 'com.example.';
        $nsid = $prefix . str_repeat('w', $constraint::MAX_PART_LENGTH);

        $this->validator->validate($nsid, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_invalid_when_nsid_part_count_greater_than_max_limit(): void
    {
        $constraint = new Nsid();
        $prefix = 'com.example.';
        $nsid = $prefix . str_repeat('w', $constraint::MAX_PART_LENGTH + 1);

        $this->validator->validate($nsid, $constraint);

        $this->buildViolation($constraint->partTooLongMessage)
            ->setParameter('{{ maxLimit }}', $constraint::MAX_PART_LENGTH)
            ->assertRaised();
    }

    public static function nsidStartedWithHyphenProvider(): \Generator
    {
        $hyphen = chr(45);

        yield 'first part' => ["{$hyphen}com.example.www"];
        yield 'second part' => ["com.{$hyphen}example.www"];
        yield 'third part' => ["com.example.{$hyphen}test.www"];

    }

    public static function nsidEndedWithHyphenProvider(): \Generator
    {
        $hyphen = chr(45);

        yield 'first part' => ["com{$hyphen}.example.www"];
        yield 'second part' => ["com.example{$hyphen}.www"];
        yield 'third part' => ["com.example.test{$hyphen}.www"];
    }

    #[DataProvider('nsidStartedWithHyphenProvider')]
    public function test_is_invalid_when_part_starts_with_hyphen(string $nsid): void
    {
        $constraint = new Nsid();

        $this->validator->validate($nsid, $constraint);

        $this->buildViolation($constraint->partStartsWithHyphenMessage)
            ->assertRaised();
    }

    #[DataProvider('nsidEndedWithHyphenProvider')]
    public function test_is_invalid_when_part_ends_with_hyphen(string $nsid): void
    {
        $constraint = new Nsid();

        $this->validator->validate($nsid, $constraint);

        $this->buildViolation($constraint->partEndsWithHyphenMessage)
            ->assertRaised();
    }

    public function test_is_invalid_when_starts_with_number(): void
    {
        $suffix = 'com.foo.bar';
        $nsid = "9" . $suffix;

        $constraint = new Nsid();

        $this->validator->validate($nsid, $constraint);

        $this->buildViolation($constraint->startsWithNumberMessage)
            ->assertRaised();
    }

    public function test_is_invalid_when_name_part_contains_hyphen(): void
    {
        $hyphen = chr(45);
        $nsid = "com.example.we{$hyphen}b";

        $constraint = new Nsid();

        $this->validator->validate($nsid, $constraint);

        $this->buildViolation($constraint->invalidNamePartMessage)
            ->assertRaised();
    }

    public function test_is_invalid_when_name_part_starts_with_number(): void
    {
        $nsid = "baz.foo.2b";

        $constraint = new Nsid();

        $this->validator->validate($nsid, $constraint);

        $this->buildViolation($constraint->invalidNamePartMessage)
            ->assertRaised();
    }
}
