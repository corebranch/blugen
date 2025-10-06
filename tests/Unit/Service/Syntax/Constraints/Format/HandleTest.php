<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\Handle\Handle;
use Blugen\Service\Syntax\Constraints\StringType\Format\Handle\HandleValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class HandleTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): HandleValidator
    {
        return new HandleValidator();
    }

    #[DataProvider('validHandleProvider')]
    public function test_handle_is_valid_for(string $value): void
    {
        $constraint = new Handle();

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    #[DataProvider('invalidHandleProvider')]
    public function test_handle_is_invalid_for(string $value, string $expectedMessage, array $parameters = []): void
    {
        $constraint = new Handle();

        $this->validator->validate($value, $constraint);


        $this->buildViolation($expectedMessage)
            ->setParameters($parameters)
            ->assertRaised();
    }

    public function test_non_string_throws_exception(): void
    {
        $constraint = new Handle();

        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate(12345, $constraint);
    }

    public static function validHandleProvider(): \Generator
    {
        yield 'simple handle'                 => ['alice.example.com'];
        yield 'social domain'                 => ['bob.bsky.social'];
        yield 'digits in subdomain'           => ['user-123.test.example'];
        yield 'deep subdomains'               => ['a.b.c.d.e.example.com'];
        yield 'short tld'                     => ['test.co'];
        yield 'multi-level domain'            => ['my-handle.example.co.uk'];
        yield 'digit in non-tld segment'      => ['123.example.com'];
        yield 'hyphenated subdomain'          => ['a-b-c.example.org'];
        yield 'digits in second-level domain' => ['user.test123.com'];
        yield 'single char segment'           => ['1.example.com'];
        yield 'mixed case domain'             => ['ABC.Example.COM'];
    }

    public static function invalidHandleProvider(): \Generator
    {
        $c = new Handle();

        yield 'trailing period'              => ['example.com.', $c->emptyPartMessage];
        yield 'leading period'               => ['.example.com', $c->emptyPartMessage];
        yield 'single segment bare TLD'      => ['example', $c->needsDomainMessage];
        yield 'bare tld'                     => ['com', $c->needsDomainMessage];
        yield 'segment starts with hyphen'   => ['-user.example.com', $c->hyphenEdgeMessage];
        yield 'segment ends with hyphen 1'   => ['user-.example.com', $c->hyphenEdgeMessage];
        yield 'empty segment'                => ['user..example.com', $c->emptyPartMessage];
        yield 'tld starts with digit 1'      => ['user.example.123', $c->tldLetterMessage];
        yield 'contains space'               => ['user.exam ple.com', $c->invalidCharsMessage];
        yield 'contains at sign'             => ['user@example.com', $c->invalidCharsMessage];
        yield 'contains slash'               => ['user.example.com/path', $c->invalidCharsMessage];
        yield 'ipv4 address'                 => ['192.168.1.1', $c->tldLetterMessage];
        yield 'segment ends with hyphen 2'   => ['user.example-.com', $c->hyphenEdgeMessage];
        yield 'segment starts with hyphen 2' => ['user.-example.com', $c->hyphenEdgeMessage];
        yield 'too short'                    => ['a', $c->needsDomainMessage];
        yield 'empty string'                 => ['', $c->needsDomainMessage];
        yield 'trailing period again'        => ['user.example.', $c->emptyPartMessage];
        yield 'leading period again'         => ['.user.example.com', $c->emptyPartMessage];
        yield 'valid did'                    => ['did:plc:xyz', $c->invalidCharsMessage];
    }
}
