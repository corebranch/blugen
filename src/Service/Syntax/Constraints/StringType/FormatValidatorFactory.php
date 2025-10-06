<?php

namespace Blugen\Service\Syntax\Constraints\StringType;

use Blugen\Service\Syntax\Constraints\StringType\Format\AtIdentifier\AtIdentifier;
use Blugen\Service\Syntax\Constraints\StringType\Format\AtUri\AtUri;
use Blugen\Service\Syntax\Constraints\StringType\Format\Cid\Cid;
use Blugen\Service\Syntax\Constraints\StringType\Format\Datetime\Datetime;
use Blugen\Service\Syntax\Constraints\StringType\Format\Did\Did;
use Blugen\Service\Syntax\Constraints\StringType\Format\Handle\Handle;
use Blugen\Service\Syntax\Constraints\StringType\Format\Language\Language;
use Blugen\Service\Syntax\Constraints\StringType\Format\Nsid\Nsid;
use Blugen\Service\Syntax\Constraints\StringType\Format\RecordKey\RecordKey;
use Blugen\Service\Syntax\Constraints\StringType\Format\Tid\Tid;
use Blugen\Service\Syntax\Constraints\StringType\Format\Uri\Uri;
use Symfony\Component\Validator\Constraint;

class FormatValidatorFactory
{
    public static function create(string $name): Constraint
    {
        return new (self::map($name));
    }

    public static function map(string $name): string
    {
        return match ($name) {
            'at-identifier' => AtIdentifier::class,
            'at-uri' => AtUri::class,
            'datetime' => Datetime::class,
            'did' => Did::class,
            'handle' => Handle::class,
            'nsid' => Nsid::class,
            'tid' => Tid::class,
            'record-key' => RecordKey::class,
            'uri' => Uri::class,
            'language' => Language::class,
            'cid' => Cid::class,

            default => throw new \InvalidArgumentException("$name is not a valid format name."),
        };
    }
}
