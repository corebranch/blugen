<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\RecordKey;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RecordKey extends Constraint
{
    public string $invalidLengthMessage = 'Record key length must be between {{ min }} - {{ max }}, actual {{ actualLength }}';
    public string $regexFailedMessage = 'Record key syntax is not valid (regex)';
    public string $cantBeDotMessage = 'Record key can not be "." or ".."';
    public const MAX_LENGTH = 512;
    public const MIN_LENGTH = 1;
    public const REGEX = '/^[a-zA-Z0-9_~.:-]{1,512}$/';
}
