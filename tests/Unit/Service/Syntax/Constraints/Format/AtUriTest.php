<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\AtUri\AtUri;
use Blugen\Service\Syntax\Constraints\StringType\Format\AtUri\AtUriValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class AtUriTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): AtUriValidator
    {
        return new AtUriValidator();
    }

    public static function validAtUriProvider(): \Generator
    {
        yield ['at://did:plc:44ybard66vv44zksje25o7dz/app.bsky.feed.post/3jwdwj2ctlk26'];
        yield ['at://bnewbold.bsky.team/app.bsky.feed.post/3jwdwj2ctlk26'];
        yield ['at://foo.com/com.example.foo/123'];
        yield ['at://alice.example.com/com.example.app/record123'];
        yield ['at://did:web:example.com/com.test.collection/abc-123'];
    }

    #[DataProvider('validAtUriProvider')]
    public function test_atUri_is_valid(string $atUri): void
    {
        $constraint = new AtUri();
        $this->validator->validate($atUri, $constraint);
        $this->assertNoViolation();
    }

    public static function invalidAtUriProvider(): \Generator
    {
        yield 'Trailing slash' => [
            'at://foo.com/',
            fn(AtUri $constraint): string => $constraint->invalidSlashAfterAuthorityMessage,
        ];
        yield 'Wrong scheme' => [
            'http://example.com',
            fn(AtUri $constraint): string => $constraint->mustStartWithPrefixMessage,
        ];
        yield 'Empty collection' => [
            'at://foo.com//record',
            fn(AtUri $constraint): string => $constraint->invalidSlashAfterAuthorityMessage,
        ];
        yield 'Empty rkey' => [
            'at://foo.com/com.example.foo/',
            fn(AtUri $constraint): string => $constraint->invalidSlashAfterCollectionMessage,
        ];
    }

    #[DataProvider('invalidAtUriProvider')]
    public function test_atUri_is_invalid(string $invalidAtUri, \Closure $message): void
    {
        $constraint = new AtUri();
        $this->validator->validate($invalidAtUri, $constraint);

        $this->buildViolation($message($constraint))
            ->assertRaised();
    }

    public function test_is_invalid_when_passed_user_info(): void
    {
        $constraint = new AtUri();
        $this->validator->validate('at://user:pass@foo.com', $constraint);

        $this->buildViolation($constraint->authorityMustBeHandleOrDidMessage)
            ->assertRaised();
    }

    public function test_is_invalid_when_used_invalid_nsid(): void
    {
        $constraint = new AtUri();
        $this->validator->validate('at://foo.com/example/123', $constraint);

        $this->buildViolation($constraint->firstPathMustBeNsidMessage)
            ->assertRaised();
    }

    #[DataProvider('invalidDidProvider')]
    public function test_is_invalid_when_used_invalid_did(string $invalidDid, \Closure $message): void
    {
        $constraint = new AtUri();
        $this->validator->validate("at://".$invalidDid, $constraint);

        $this->buildViolation($message($constraint))
            ->assertRaised();
    }

    public static function invalidDidProvider(): \Generator
    {
        yield 'Not valid DID or handle 1' => [
            'computer',
            fn(AtUri $constraint): string => $constraint->authorityMustBeHandleOrDidMessage,
        ];
        yield 'Not valid DID or handle 2' => [
            'example.com:3000',
            fn(AtUri $constraint): string => $constraint->authorityMustBeHandleOrDidMessage,
        ];
        yield 'Missing authority' => [
            '',
            fn(AtUri $constraint): string => $constraint->authorityMustBeHandleOrDidMessage,
        ];
    }

    public function test_is_invalid_when_not_string(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $constraint = new AtUri();
        $this->validator->validate(123, $constraint);
    }

    public function test_is_invalid_with_non_ascii_chars(): void
    {
        $constraint = new AtUri();
        $this->validator->validate('at://web.exämple.com', $constraint);

        $this->buildViolation($constraint->invalidCharsMessage)
            ->buildNextViolation($constraint->authorityMustBeHandleOrDidMessage)
            ->assertRaised();
    }

    public function test_is_invalid_with_too_many_path_segments(): void
    {
        $constraint = new AtUri();
        $this->validator->validate('at://foo.com/com.example.app/record/extra/too-many', $constraint);

        $this->buildViolation($constraint->tooManyPathSegmentsMessage)
            ->assertRaised();
    }

    public function test_is_invalid_with_empty_fragment(): void
    {
        $constraint = new AtUri();
        $this->validator->validate('at://foo.com/com.example.app/record#', $constraint);

        $this->buildViolation($constraint->emptyFragmentMessage)
            ->assertRaised();
    }

    public function test_is_invalid_with_invalid_fragment_chars(): void
    {
        $constraint = new AtUri();
        $this->validator->validate('at://foo.com/com.example.app/record#/bad|fragment', $constraint);

        $this->buildViolation($constraint->invalidFragmentCharsMessage)
            ->assertRaised();
    }

    public function test_is_invalid_with_fragment_without_leading_slash(): void
    {
        $constraint = new AtUri();
        $this->validator->validate('at://foo.com/com.example.app/record#fragment', $constraint);

        $this->buildViolation($constraint->emptyFragmentMessage)
            ->assertRaised();
    }

    public function test_is_invalid_with_too_long_uri(): void
    {
        $constraint = new AtUri();
        $prefix = 'at://foo.com/com.atproto.repo/';
        $tooLong = $prefix . str_repeat('a', ($constraint::MAX_SIZE * 1024) - strlen($prefix) + 1);

        $this->validator->validate($tooLong, $constraint);

        $this->buildViolation($constraint->tooLongMessage)
            ->setParameter('{{ actualSize }}', (string) (strlen($tooLong) * 1024))
            ->setParameter('{{ maxSize }}', (string) $constraint::MAX_SIZE)
            ->assertRaised();
    }

    public function test_is_valid_when_uri_at_exact_limit(): void
    {
        $constraint = new AtUri();
        $prefix = 'at://foo.com/com.atproto.repo/';
        $atExactLimit = $prefix . str_repeat('a', ($constraint::MAX_SIZE * 1024) - strlen($prefix));

        $this->validator->validate($atExactLimit, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_valid_when_uri_size_less_than_exact_limit(): void
    {
        $constraint = new AtUri();
        $prefix = 'at://foo.com/com.atproto.repo/';
        $atExactLimit = $prefix . str_repeat('a', ($constraint::MAX_SIZE * 1024) - strlen($prefix) - 1);

        $this->validator->validate($atExactLimit, $constraint);

        $this->assertNoViolation();
    }
}
