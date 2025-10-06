<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Nsid;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Nsid extends Constraint
{
    // Generic
    public string $invalidMessage = 'The value "{{ value }}" is not a valid NSID.';

    // Length
    public string $tooLongMessage = 'The NSID is too long. Maximum {{ limit }} characters are allowed.';
    public string $partTooLongMessage = 'Each NSID part cannot exceed {{ maxLimit }} characters.';

    // Structure
    public string $tooFewPartsMessage = 'The NSID "{{ value }}" must contain at least {{ minLimit }} parts.';
    public string $emptyPartMessage = 'NSID parts cannot be empty.';

    // Hyphen rules
    public string $partStartsWithHyphenMessage = 'An NSID part cannot start with a hyphen.';
    public string $partEndsWithHyphenMessage = 'An NSID part cannot end with a hyphen.';

    // Number rules
    public string $startsWithNumberMessage = 'The NSID cannot start with a number.';
    public string $invalidNamePartMessage = 'The NSID name part must contain only letters and digits.';

    // Character set
    public string $disallowedCharsMessage = 'The NSID "{{ value }}" contains disallowed characters.';

    // Constants
    public const SEPARATOR = '.';
    public const MAX_CHAR_LIMIT = 253 + 1 + 63; // Full NSID max length incl. dots
    public const MIN_PARTS = 3;
    public const MAX_PART_LENGTH = 63;
    public const ALLOWED_CHARS_REGEX = '/^[a-zA-Z0-9.-]*$/';
}
