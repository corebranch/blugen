<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Tid;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Tid extends Constraint
{
    public string $invalidLengthMessage = 'Invalid TID length, must be {{ length }} characters. Actual: {{ actualLength }}';
    public string $invalidRegexMessage = 'TID syntax not valid (regex)';

    public const LENGTH = 13;
    public const REGEX = '/^[234567abcdefghij][234567abcdefghijklmnopqrstuvwxyz]{12}$/';
}
