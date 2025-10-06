<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Cid;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CidValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof Cid) {
            throw new UnexpectedTypeException($constraint, Cid::class);
        }

        if (! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        // TODO:
    }
}
