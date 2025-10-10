<?php

namespace Blugen\Service\Syntax\Constraints\NullType;

use Attribute;
use Blugen\Service\Syntax\Constraints\LexConstraint;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NullType extends Constraint implements LexConstraint
{
    use \Blugen\Service\Syntax\Constraints\Constraint;
}
