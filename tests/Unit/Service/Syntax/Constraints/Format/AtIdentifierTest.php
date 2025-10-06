<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\AtIdentifier\AtIdentifier;
use Blugen\Service\Syntax\Constraints\StringType\Format\AtIdentifier\AtIdentifierValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class AtIdentifierTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): AtIdentifierValidator
    {
        return new AtIdentifierValidator();
    }

    public static function validSupportedDidProvider(): \Generator
    {
        yield 'plc did' => ['did:plc:z72i7hdynmk6r22z27h6tvur'];
        yield 'web did' => ['did:web:blueskyweb.xyz'];
    }

    public static function validUnsupportedDidProvider(): \Generator
    {
        yield 'multiple colons in identifier' => ['did:method:val:two'];
        yield 'single char method and id' => ['did:m:v'];
        yield 'multiple empty segments' => ['did:method::::val'];
        yield 'special chars in identifier' => ['did:method:-:_:.'];
        yield 'did:key example' => ['did:key:zQ3shZc2QzApp2oymGvQbzP8eKheVshBHbU4ZYjeXqwSKEn6N'];
    }

    public static function invalidDidProvider(): \Generator
    {
        yield 'uppercase method' => ['did:METHOD:val'];
        yield 'method contains digits' => ['did:m123:val'];
        yield 'uppercase did prefix' => ['DID:method:val'];
        yield 'ends with colon' => ['did:method:'];
        yield 'contains slash' => ['did:method:val/two'];
        yield 'contains query' => ['did:method:val?two'];
        yield 'contains fragment' => ['did:method:val#two'];
        yield 'ends with percent' => ['did:method:val%'];
        yield 'missing method and id' => ['did:'];
        yield 'missing identifier' => ['did:method'];
        yield 'empty identifier' => ['did:method:'];
        yield 'mixed case method' => ['did:Method:val'];
        yield 'method with hyphen' => ['did:abc-def:val'];
        yield 'method with underscore' => ['did:abc_def:val'];
    }

    public static function validHandleProvider(): \Generator
    {
        yield 'simple handle' => ['alice.example.com'];
        yield 'social domain' => ['bob.bsky.social'];
        yield 'digits in subdomain' => ['user-123.test.example'];
        yield 'deep subdomains' => ['a.b.c.d.e.example.com'];
        yield 'short tld' => ['test.co'];
        yield 'multi-level domain' => ['my-handle.example.co.uk'];
        yield 'digit in non-tld segment' => ['123.example.com'];
        yield 'hyphenated subdomain' => ['a-b-c.example.org'];
        yield 'digits in second-level domain' => ['user.test123.com'];
        yield 'single char segment' => ['1.example.com'];
        yield 'mixed case domain' => ['ABC.Example.COM'];
    }

    public static function invalidHandleProvider(): \Generator
    {
        yield 'trailing period' => ['example.com.'];
        yield 'leading period' => ['.example.com'];
        yield 'single segment bare TLD' => ['example'];
        yield 'bare tld' => ['com'];
        yield 'segment starts with hyphen' => ['-user.example.com'];
        yield 'segment ends with hyphen 1' => ['user-.example.com'];
        yield 'empty segment' => ['user..example.com'];
        yield 'tld starts with digit 1' => ['user.example.123'];
        yield 'contains space' => ['user.exam ple.com'];
        yield 'contains at sign' => ['user@example.com'];
        yield 'handle contains slash' => ['user.example.com/path'];
        yield 'ipv4 address' => ['192.168.1.1'];
        yield 'segment ends with hyphen 2' => ['user.example-.com'];
        yield 'segment starts with hyphen 2' => ['user.-example.com'];
        yield 'too short' => ['a'];
        yield 'empty string' => [''];
        yield 'trailing period again' => ['user.example.'];
        yield 'leading period again' => ['.user.example.com'];

        yield ['did:thing.test'];
        yield ['did:thing'];
        yield ['john-.test'];
        yield ['john.0'];
        yield ['john.-'];
        yield ['xn--bcher-.tld'];
        yield ['john..test'];
        yield ['jo_hn.test'];

        yield ['did'];
        yield ['didmethodval'];
        yield ['method:did:val'];
        yield ['did:method:'];
        yield ['didmethod:val'];
        yield ['did:methodval)'];
        yield [':did:method:val'];
        yield ['did:method:val:'];
        yield ['did:method:val%'];
        yield ['DID:method:val'];

        yield ['email@example.com'];
        yield ['@handle@example.com'];
        yield ['@handle'];
        yield ['blah'];
    }

    #[DataProvider('validSupportedDidProvider')]
    #[DataProvider('validUnsupportedDidProvider')]
    public function test_valid_did_is_valid_as_atIdentifier(string $validDid): void
    {
        $constraint = new AtIdentifier();

        $this->validator->validate($validDid, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('invalidDidProvider')]
    public function test_invalid_did_is_not_valid_as_atIdentifier(string $invalidDid): void
    {
        $constraint = new AtIdentifier();

        $this->validator->validate($invalidDid, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '"' . $invalidDid . '"')
            ->assertRaised();
    }

    #[DataProvider('validHandleProvider')]
    public function test_valid_handle_is_valid_as_atIdentifier(string $validHandle): void
    {
        $constraint = new AtIdentifier();

        $this->validator->validate($validHandle, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('invalidHandleProvider')]
    public function test_invalid_handle_is_not_valid_as_atIdentifier(string $invalidHandle): void
    {
        $constraint = new AtIdentifier();

        $this->validator->validate($invalidHandle, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '"' . $invalidHandle . '"')
            ->assertRaised();
    }

    #[DataProvider('invalidDidProvider')]
    #[DataProvider('invalidHandleProvider')]
    public function test_invalid_handle_or_did_is_not_valid_as_atIdentifier(string $invalidValue): void
    {
        $constraint = new AtIdentifier();

        $this->validator->validate($invalidValue, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '"' . $invalidValue . '"')
            ->assertRaised();
    }

    public function test_did_identifier_passes_validation_even_if_handle_is_invalid(): void
    {
        // not a valid handle, but a valid DID
        $validDid = "did:plc:xyz";

        $constraint = new AtIdentifier();
        $this->validator->validate($validDid, $constraint);

        $this->assertNoViolation();
    }

    public function test_handle_passes_validation_even_if_did_is_invalid(): void
    {
        // not a valid DID, but a valid handle
        $validHandle = "alice.example.com";

        $constraint = new AtIdentifier();
        $this->validator->validate($validHandle, $constraint);

        $this->assertNoViolation();
    }

    public function test_is_invalid_if_value_is_not_a_valid_handle_or_did(): void
    {
        $invalidAtIdentifier = 'invalid at-identifier';

        $constraint = new AtIdentifier();

        $this->validator->validate($invalidAtIdentifier, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '"' . $invalidAtIdentifier . '"')
            ->assertRaised();
    }
}
