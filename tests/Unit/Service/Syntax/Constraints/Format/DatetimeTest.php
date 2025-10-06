<?php

namespace Blugen\Tests\Unit\Service\Syntax\Constraints\Format;

use Blugen\Service\Syntax\Constraints\StringType\Format\Datetime\Datetime;
use Blugen\Service\Syntax\Constraints\StringType\Format\Datetime\DatetimeValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class DatetimeTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): DatetimeValidator
    {
        return new DatetimeValidator();
    }

    #[DataProvider('invalidDatetimeProvider')]
    public function test_datetime_is_invalid_for(string $value, string $expectedMessage): void
    {
        $constraint = new Datetime();

        $this->validator->validate($value, $constraint);

        $this->buildViolation($expectedMessage)
            ->assertRaised();
    }

    public static function invalidDatetimeProvider(): \Generator
    {
        $c = new Datetime();

        // subtle changes
        yield 'Lowercase Z' => ['1985-04-12T23:20:50.123z', $c->regexFailMessage];
        yield '5-digit year with leading zero' => ['01985-04-12T23:20:50.123Z', $c->parseErrorMessage];
        yield '3-digit year' => ['985-04-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Missing fractional digits' => ['1985-04-12T23:20:50.Z', $c->regexFailMessage];
        yield 'Invalid separator ;' => ['1985-04-32T23;20:50.123Z', $c->parseErrorMessage];

        // dashes
        yield 'En-dash' => ['1985—04-32T23;20:50.123Z', $c->parseErrorMessage];
        yield 'Em-dash' => ['1985–04-32T23;20:50.123Z', $c->parseErrorMessage];

        // whitespace
        yield 'Leading space' => [' 1985-04-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Trailing space' => ['1985-04-12T23:20:50.123Z ', $c->regexFailMessage];
        yield 'Space between date and time' => ['1985-04-12T 23:20:50.123Z', $c->parseErrorMessage];

        // not enough zero padding
        yield 'Month 1 digit' => ['1985-4-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Day 1 digit' => ['1985-04-2T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Hour 1 digit' => ['1985-04-12T3:20:50.123Z', $c->parseErrorMessage];
        yield 'Minute 1 digit' => ['1985-04-12T23:0:50.123Z', $c->parseErrorMessage];
        yield 'Second 1 digit' => ['1985-04-12T23:20:5.123Z', $c->parseErrorMessage];

        // too much zero padding
        yield '5-digit year with extra 0' => ['01985-04-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Month with 3 digits' => ['1985-004-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Day with 3 digits' => ['1985-04-012T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Hour with 3 digits' => ['1985-04-12T023:20:50.123Z', $c->parseErrorMessage];
        yield 'Minute with 3 digits' => ['1985-04-12T23:020:50.123Z', $c->parseErrorMessage];
        yield 'Second with 3 digits' => ['1985-04-12T23:20:050.123Z', $c->parseErrorMessage];

        // strict capitalization
        yield 'Lowercase t' => ['1985-04-12t23:20:50.123Z', $c->parseErrorMessage];
        yield 'Lowercase z again' => ['1985-04-12T23:20:50.123z', $c->regexFailMessage];

        // RFC-3339 but not ISO-8601
        yield 'Negative zero offset' => ['1985-04-12T23:20:50.123-00:00', $c->invalidUtcMessage];
        yield 'Underscore separator' => ['1985-04-12_23:20:50.123Z', $c->parseErrorMessage];
        yield 'Space separator' => ['1985-04-12 23:20:50.123Z', $c->parseErrorMessage];

        // ISO but weird
        yield 'Day 274' => ['1985-04-274T23:20:50.123Z', $c->parseErrorMessage];

        // timezone required
        yield 'Missing timezone (fractional)' => ['1985-04-12T23:20:50.123', $c->regexFailMessage];
        yield 'Missing timezone (no fraction)' => ['1985-04-12T23:20:50', $c->regexFailMessage];
        yield 'Date only' => ['1985-04-12', $c->parseErrorMessage];
        yield 'Missing seconds in time' => ['1985-04-12T23:20Z', $c->parseErrorMessage];
        yield 'One-digit second' => ['1985-04-12T23:20:5Z', $c->parseErrorMessage];
        yield 'Plus sign year' => ['+001985-04-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Time only' => ['23:20:50.123Z', $c->parseErrorMessage];

        // bad offsets
        yield 'Incomplete offset +00' => ['1985-04-12T23:20:50.123+00', $c->regexFailMessage];
        yield 'Incomplete offset +00:0' => ['1985-04-12T23:20:50.123+00:0', $c->regexFailMessage];
        yield 'Incomplete offset +0:00' => ['1985-04-12T23:20:50.123+0:00', $c->regexFailMessage];
        yield 'Offset missing colon' => ['1985-04-12T23:20:50.123+0000', $c->regexFailMessage];
        yield 'Offset only +' => ['1985-04-12T23:20:50.123+', $c->parseErrorMessage];
        yield 'Offset only -' => ['1985-04-12T23:20:50.123-', $c->parseErrorMessage];

        // special negative/zero year cases
        yield 'Year 0000' => ['0000-01-01T00:00:00+01:00', $c->tooCloseToZeroMessage];
        yield 'Negative year -000001' => ['-000001-12-31T23:00:00.000Z', $c->parseErrorMessage];

        // superficial syntax ok, but semantically invalid
        yield 'Month zero' => ['1985-00-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Day zero' => ['1985-04-00T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Month 13' => ['1985-13-12T23:20:50.123Z', $c->parseErrorMessage];
        yield 'Hour 25' => ['1985-04-12T25:20:50.123Z', $c->parseErrorMessage];
        yield 'Minute 99' => ['1985-04-12T23:99:50.123Z', $c->parseErrorMessage];
        yield 'Second 61' => ['1985-04-12T23:20:61.123Z', $c->parseErrorMessage];
    }

    public static function validDatetimeProvider(): \Generator
    {
        // preferred
        yield 'Preferred - milliseconds' => ['1985-04-12T23:20:50.123Z'];
        yield 'Preferred - explicit zeros' => ['1985-04-12T23:20:50.000Z'];
        yield 'Preferred - midnight Y2K' => ['2000-01-01T00:00:00.000Z'];
        yield 'Preferred - 6 digit fractional' => ['1985-04-12T23:20:50.123456Z'];
        yield 'Preferred - 3 digit fractional variant' => ['1985-04-12T23:20:50.120Z'];
        yield 'Preferred - 6 digit fractional with trailing zeros' => ['1985-04-12T23:20:50.120000Z'];

        // supported
        yield 'Supported - long fractional' => ['1985-04-12T23:20:50.1235678912345Z'];
        yield 'Supported - fractional .100' => ['1985-04-12T23:20:50.100Z'];
        yield 'Supported - no fractional' => ['1985-04-12T23:20:50Z'];
        yield 'Supported - single fractional digit' => ['1985-04-12T23:20:50.0Z'];
        yield 'Supported - UTC offset +00:00' => ['1985-04-12T23:20:50.123+00:00'];
        yield 'Supported - UTC offset -07:00' => ['1985-04-12T23:20:50.123-07:00'];
        yield 'Supported - UTC offset +07:00' => ['1985-04-12T23:20:50.123+07:00'];
        yield 'Supported - UTC offset +01:45' => ['1985-04-12T23:20:50.123+01:45'];
        yield 'Supported - 4-digit year 0985' => ['0985-04-12T23:20:50.123-07:00'];
        yield 'Supported - 4-digit year 1985' => ['1985-04-12T23:20:50.123-07:00'];
        yield 'Supported - 4-digit year 0123' => ['0123-01-01T00:00:00.000Z'];

        // various precisions
        yield 'Precision 1' => ['1985-04-12T23:20:50.1Z'];
        yield 'Precision 2' => ['1985-04-12T23:20:50.12Z'];
        yield 'Precision 3' => ['1985-04-12T23:20:50.123Z'];
        yield 'Precision 4' => ['1985-04-12T23:20:50.1234Z'];
        yield 'Precision 5' => ['1985-04-12T23:20:50.12345Z'];
        yield 'Precision 6' => ['1985-04-12T23:20:50.123456Z'];
        yield 'Precision 7' => ['1985-04-12T23:20:50.1234567Z'];
        yield 'Precision 8' => ['1985-04-12T23:20:50.12345678Z'];
        yield 'Precision 9' => ['1985-04-12T23:20:50.123456789Z'];
        yield 'Precision 10' => ['1985-04-12T23:20:50.1234567890Z'];
        yield 'Precision 11' => ['1985-04-12T23:20:50.12345678901Z'];
        yield 'Precision 12' => ['1985-04-12T23:20:50.123456789012Z'];

        // extreme but currently allowed
        yield 'Extreme year 0010' => ['0010-12-31T23:00:00.000Z'];
        yield 'Extreme year 1000' => ['1000-12-31T23:00:00.000Z'];
        yield 'Extreme year 1900' => ['1900-12-31T23:00:00.000Z'];
        yield 'Extreme year 3001' => ['3001-12-31T23:00:00.000Z'];
    }

    #[DataProvider('validDatetimeProvider')]
    public function test_datetime_is_valid_for(string $value): void
    {
        $constraint = new Datetime();

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }
}
