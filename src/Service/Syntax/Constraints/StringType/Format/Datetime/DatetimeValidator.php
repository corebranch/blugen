<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Datetime;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DatetimeValidator extends ConstraintValidator
{
    private Datetime $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Datetime) {
            throw new UnexpectedTypeException($constraint, Datetime::class);
        }

        $this->constraint = $constraint;

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($this->isInvalidIso($value)) {
            $this->context->buildViolation($this->constraint->parseErrorMessage)
                ->addViolation();
            return;
        }

        if ($this->failsRegex($value)) {
            $this->context->buildViolation($this->constraint->regexFailMessage)
                ->addViolation();

            return;
        }

        if ($this->isNegativeYear($value)) {
            $this->context->buildViolation($this->constraint->negativeYearMessage)
                ->addViolation();
        }

        if ($this->isTooLong($value)) {
            $this->context->buildViolation($this->constraint->tooLongMessage)
                ->setParameter('{{ maxLength }}', (string) $this->constraint::ALLOWED_LENGTH)
                ->addViolation();
        }

        if ($this->hasInvalidUtc($value)) {
            $this->context->buildViolation($this->constraint->invalidUtcMessage)
                ->addViolation();
        }

        if ($this->isTooCloseToZero($value)) {
            $this->context->buildViolation($this->constraint->tooCloseToZeroMessage)
                ->addViolation();
        }
    }

    private function isInvalidIso(string $dt): bool
    {
        try {
            $parsed = new \DateTime($dt);
        } catch (\Exception) {
            return true;
        }

        $normalized = $parsed->format('Y-m-d\TH:i:s');
        if (!str_starts_with($dt, $normalized)) {
            return true;
        }

        return false;
    }


    private function isNegativeYear(string $dt): bool
    {
        try {
            $parsed = new \DateTime($dt);
            return str_starts_with($parsed->format('Y'), '-');
        } catch (\Exception) {
            return false;
        }
    }

    private function failsRegex(string $dt): bool
    {
        return 1 !== preg_match($this->constraint::RFC3339_PATTERN, $dt);
    }

    private function isTooLong(string $dt): bool
    {
        return strlen($dt) > $this->constraint::ALLOWED_LENGTH;
    }

    private function hasInvalidUtc(string $dt): bool
    {
        return str_ends_with($dt, '-00:00');
    }

    private function isTooCloseToZero(string $dt): bool
    {
        return str_starts_with($dt, '000');
    }
}
