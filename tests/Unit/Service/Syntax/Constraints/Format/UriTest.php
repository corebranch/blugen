<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\Uri\Uri;
use Blugen\Service\Syntax\Constraints\StringType\Format\Uri\UriValidator;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UriTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): UriValidator
    {
        return new UriValidator();
    }

    #[DataProvider('invalidUriProvider')]
    public function test_is_invalid_for_invalid_rfc3986_uri(string $value): void
    {
        $constraint = new Uri();

        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->invalidMessage)
            ->setParameter('{{ value }}', "\"$value\"")
            ->assertRaised();
    }

    #[DataProvider('validUriProvider')]
    public function test_is_valid_for_valid_rfc3986_uri(string $value): void
    {
        $constraint = new Uri();

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public static function invalidUriProvider(): Generator
    {
        yield 'too many slashes' => ['http:///example.com'];
        yield 'missing scheme' => ['://example.com'];
        yield 'empty host' => ['http://'];
        yield 'space in host' => ['http://exa mple.com'];
        yield 'malformed ftp' => ['ftp:/example.com'];
        yield 'no scheme' => ['randomtext'];
        yield 'scheme only' => ['http:'];
        yield 'double colon in scheme' => ['http:://example.com'];
        yield 'invalid char in scheme' => ['ht*tp://example.com'];
        yield 'port not numeric' => ['http://example.com:abc'];
        yield 'port too large' => ['http://example.com:999999'];
        yield 'invalid ipv6 host' => ['http://[gggg::1]/'];
        yield 'host starts with dash' => ['http://-example.com'];
        yield 'unicode in host' => ['http://exámple.com']; // RFC3986 forbids non-ASCII directly
        yield 'control char in path' => ["http://example.com/\x01path"];
        yield 'trailing space' => ['http://example.com '];
        yield 'leading space' => [' http://example.com'];
    }

    public static function validUriProvider(): Generator
    {
        yield 'basic http' => ['http://example.com'];
        yield 'https with path query fragment' => ['https://example.com/path/to/page?foo=bar&baz=1#section2'];
        yield 'ftp with userinfo and port' => ['ftp://user:pass@example.com:21/dir/file.txt'];
        yield 'mailto scheme' => ['mailto:user@example.com'];
        yield 'ipv4 host' => ['http://127.0.0.1:8080/index.html'];
        yield 'ipv6 host' => ['http://[2001:db8::1]/index.html'];
        yield 'user info no password' => ['http://user@example.com'];
        yield 'user info with password' => ['http://user:password@example.com'];
        yield 'query with encoded chars' => ['http://example.com/search?q=hello%20world'];
        yield 'fragment only' => ['http://example.com/#top'];
        yield 'relative path with scheme/host' => ['http://example.com/a/b/../c'];
        yield 'dash in domain' => ['http://sub-domain.example.com'];
        yield 'long tld' => ['http://example.technology'];
    }

    public function test_uri_exceeds_8kb_limit_is_invalid(): void
    {
        $oversized = 'http://example.com/' . str_repeat('a', 8192 - strlen('http://example.com/') + 1);

        $constraint = new Uri();

        $this->validator->validate($oversized, $constraint);

        $this->buildViolation($constraint->tooLongMessage)
            ->setParameter('{{ maxKb }}', $constraint::MAX_KB)
            ->assertRaised();
    }

    public function test_uri_exactly_8kb_is_valid(): void
    {
        $value = 'http://example.com/' . str_repeat('a', 8192 - strlen('http://example.com/'));

        $constraint = new Uri();

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function test_uri_under_8kb_is_valid(): void
    {
        $value = 'http://example.com/' . str_repeat('a', 8191 - strlen('http://example.com/'));

        $constraint = new Uri();

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }
}
