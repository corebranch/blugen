<?php

namespace Blugen\Service\Syntax\Constraints\IntegerType;

use Attribute;
use Blugen\Service\Syntax\Constraints\LexConstraint;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerType extends Constraint implements LexConstraint
{
    use \Blugen\Service\Syntax\Constraints\Constraint;

    public string $minMessage = 'Integer {{ value }} must be greater than or equal to {{ min }}';
    public string $maxMessage = 'Integer {{ value }} must be less than or equal to {{ max }}';
    public string $constMessage = 'Integer value must be equal to the constant {{ const }}';
    public string $notInEnumMessage = 'Integer must be one of the allowed values: {{ enumValues }}';
}
