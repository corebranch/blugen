<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Language;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Toobo\Bcp47;

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
