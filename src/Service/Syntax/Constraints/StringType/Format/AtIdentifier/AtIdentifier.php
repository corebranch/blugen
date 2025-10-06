<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\AtIdentifier;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AtIdentifier extends Constraint
{
    public string $message = 'The value {{ value }} is not a valid at-identifier.';
}
