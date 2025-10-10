<?php

namespace Blugen\Service\Syntax\Constraints\BooleanType;

use Attribute;
use Blugen\Service\Syntax\Constraints\LexConstraint;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BooleanType extends Constraint implements LexConstraint
{
    use \Blugen\Service\Syntax\Constraints\Constraint;

    public string $message = 'Expected boolean value {{ must }}, but got {{ actual }}';
}
