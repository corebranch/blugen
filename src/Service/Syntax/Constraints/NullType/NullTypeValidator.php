<?php

namespace Blugen\Service\Syntax\Constraints\NullType;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NullTypeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof NullType) {
            throw new UnexpectedTypeException($constraint, NullType::class);
        }

        if (! is_null($value)) {
            throw new UnexpectedTypeException($value, 'null');
        }
    }
}
