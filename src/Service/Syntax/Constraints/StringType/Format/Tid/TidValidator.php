<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Tid;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TidValidator extends ConstraintValidator
{
    private Tid $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof Tid) {
            throw new UnexpectedTypeException($constraint, Tid::class);
        }

        $this->constraint = $constraint;

        if (! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($this->hasInvalidLength($value)) {
            $this->context->buildViolation($this->constraint->invalidLengthMessage)
                ->setParameter('{{ length }}', $constraint::LENGTH)
                ->setParameter('{{ actualLength }}', strlen($value))
                ->addViolation();
        }

        if ($this->isPatternInvalid($value)) {
            $this->context->buildViolation($this->constraint->invalidRegexMessage)
                ->addViolation();
        }
    }

    private function hasInvalidLength(string $value): bool
    {
        return $this->constraint::LENGTH !== strlen($value);
    }

    private function isPatternInvalid(string $value): bool
    {
        return preg_match($this->constraint::REGEX, $value) !== 1;
    }
}
