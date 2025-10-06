<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Datetime;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Datetime extends Constraint
{
    public string $parseErrorMessage = 'Datetime did not parse';
    public string $negativeYearMessage = 'Datetime normalized to a negative time';
    public string $regexFailMessage = "Datetime didn't validate via regex";
    public string $tooLongMessage = 'Datetime is too long (max {{ maxLength }} characters)';
    public string $invalidUtcMessage = 'Datetime cannot use "-00:00" for UTC timezone';
    public string $tooCloseToZeroMessage = 'Datetime so close to year zero not allowed';

    public const ALLOWED_LENGTH = 64;

    public const RFC3339_PATTERN = '/^'
        .'[0-9]{4}' // year
        . '-[01][0-9]' // month
        . '-[0-3][0-9]' // day
        . 'T[0-2][0-9]' // hour
        . ':[0-6][0-9]' // minute
        . ':[0-6][0-9]' // seconds
        . '(.[0-9]{1,20})?' // fractional seconds
        . '(Z|([+-][0-2][0-9]:[0-5][0-9]))' // timezone
        . '$/';
}
