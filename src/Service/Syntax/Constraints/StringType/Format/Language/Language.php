<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Language;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Language extends Constraint
{
    public string $invalidMessage = 'The given value is not a valid language code.';
}
