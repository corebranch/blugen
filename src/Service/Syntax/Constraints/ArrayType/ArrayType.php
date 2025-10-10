<?php

namespace Blugen\Service\Syntax\Constraints\ArrayType;

use Attribute;
use Blugen\Service\Syntax\Constraints\LexConstraint;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayType extends Constraint implements LexConstraint
{
    use \Blugen\Service\Syntax\Constraints\Constraint;

    public string $invalidMessage = 'Array contains invalid items. Expected elements of type {{ type }}, but index {{ index }} has an invalid value.';
    public string $invalidMaxLengthMessage = 'Array is too long. The maximum allowed number of items is {{ limit }}, but {{ actualCount }} given';
    public string $invalidMinLengthMessage = 'Array is too short. The minimum required number of items is {{ limit }}, but {{ actualCount }} given';
}
