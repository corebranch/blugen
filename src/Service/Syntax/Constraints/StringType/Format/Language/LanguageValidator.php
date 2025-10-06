<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Language;

use Blugen\Service\Bcp47\Bcp47;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LanguageValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof Language) {
            throw new UnexpectedTypeException($value, Language::class);
        }

        if (! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (! Bcp47::isValidTag($value)) {
            $this->context->buildViolation($constraint->invalidMessage)
                ->addViolation();
        }
    }
}
