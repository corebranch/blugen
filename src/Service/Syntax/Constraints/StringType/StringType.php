<?php

namespace Blugen\Service\Syntax\Constraints\StringType;

use Attribute;
use Blugen\Service\Syntax\Constraints\LexConstraint;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringType extends Constraint implements LexConstraint
{
    use \Blugen\Service\Syntax\Constraints\Constraint;

    public string $invalidFormatMessage = "String must follow the {{ format }} format";
    public string $maxLengthMessage = "String must not exceed {{ limit }} characters";
    public string $minLengthMessage = "String must be at least {{ limit }} characters long";
    public string $maxGraphemeMessage = "String must not exceed {{ limit }} grapheme characters";
    public string $minGraphemeMessage = "String must contain at least {{ limit }} grapheme characters";
    public string $allowedValuesMessage = "String must be one of the following values: {{ allowedValues }}";
    public string $constValueMessage = "String {{ value }} must always be the constant value {{ const }}";
}
