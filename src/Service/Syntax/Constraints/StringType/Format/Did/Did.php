<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Did;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Did extends Constraint
{
    public string $requiresPrefix = 'DID requires {{ prefix }} prefix';
    public string $invalidCharsMessage = "Disallowed characters in DID (ASCII letters, digits, and a couple other characters only)";
    public string $requiresMethodMessage = 'DID requires prefix, method, and method-specific content';
    public string $mustBeLowercaseMessage = 'DID method must be lower-case letters';
    public string $cantEndWithColonOrPercent = 'DID cannot end with colon (":") and percent ("%")';
    public string $tooLongMessage = 'DID is too long (max {{ maxLength }} characters)';

    public const SEPARATOR = ':';
    public const PREFIX = 'did:';
    public const ALLOWED_LENGTH = 2048;
    public const PARTS_COUNT = 3;

    public const ALLOWED_CHARS_PATTERN = '/^[a-zA-Z0-9._:%-]*$/';
}
