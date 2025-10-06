<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Handle;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Handle extends Constraint
{
    public string $invalidCharsMessage = 'Disallowed characters in handle (ASCII letters, digits, dashes, periods only)';
    public string $tooLongMessage = 'Handle is too long (max {{ maxLength }} chars)';
    public string $needsDomainMessage = 'Handle domain needs at least two parts';
    public string $emptyPartMessage = 'Handle parts can not be empty';
    public string $partTooLongMessage = 'Handle part too long (max {{ maxLength }} chars)';
    public string $hyphenEdgeMessage = 'Handle parts can not start or end with hyphens';
    public string $tldLetterMessage = 'Handle final component (TLD) must start with ASCII letter';

    public const ALLOWED_PATTERN = '/^[a-zA-Z0-9.-]*$/';
    public const MAX_LENGTH = 253;
    public const MAX_PART_LENGTH = 63;
}
