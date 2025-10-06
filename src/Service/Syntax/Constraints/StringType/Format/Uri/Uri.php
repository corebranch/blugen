<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Uri;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Uri extends Constraint
{
    public string $invalidMessage = 'This value is not a valid RFC 3986 compliant URI.';
    public string $tooLongMessage = 'This URI exceeds the maximum allowed length of {{ maxKb }}KB.';

    public const MAX_KB = 8;
}
