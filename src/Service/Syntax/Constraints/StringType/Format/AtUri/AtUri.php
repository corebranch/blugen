<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\AtUri;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AtUri extends Constraint
{
    public string $maxOneFragmentMessage = 'An AT URI can contain at most one "#" to separate the fragment';
    public string $mustStartWithPrefixMessage = 'An AT URI must start with "at://"';
    public string $requiresAuthorityMessage = 'An AT URI must include at least a method and an authority section';
    public string $authorityMustBeHandleOrDidMessage = 'The authority section of an AT URI must be a valid handle or DID';

    public string $invalidSlashAfterAuthorityMessage = 'An AT URI cannot have a slash immediately after the authority without a path segment';
    public string $firstPathMustBeNsidMessage = 'If a path is provided, the first path segment must be a valid NSID';
    public string $invalidSlashAfterCollectionMessage = 'An AT URI cannot have a slash after a collection unless a record key is provided';
    public string $tooManyPathSegmentsMessage = 'An AT URI path can have at most two parts, and must not end with a trailing slash';

    public string $emptyFragmentMessage = 'An AT URI fragment must be non-empty and must start with a slash';
    public string $invalidFragmentCharsMessage = 'An AT URI fragment contains disallowed characters (only ASCII is allowed)';

    public string $invalidCharsMessage = 'An AT URI contains disallowed characters';

    public string $tooLongMessage = 'The AT URI exceeds the maximum allowed size (kb): Actual {{ actualSize }}, allowed {{ maxSize }}';

    public const FRAGMENT_CHARS_REGEX = '/^\/[a-zA-Z0-9._~:@!$&\')(*+,;=%\[\]\/-]*$/';
    public const MAX_SIZE = 8; // as kb
}
