<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Did;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DidValidator extends ConstraintValidator
{
    private Did $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Did) {
            throw new UnexpectedTypeException($constraint, Did::class);
        }

        $this->constraint = $constraint;

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($this->isPrefixMissing($value)) {
            $this->context->buildViolation($this->constraint->requiresPrefix)
                ->setParameter('{{ prefix }}', $constraint::PREFIX)
                ->addViolation();
        }

        if ($this->hasDisallowedChars($value)) {
            $this->context->buildViolation($this->constraint->invalidCharsMessage)
                ->addViolation();
        }

        if ($this->hasInvalidPartCount($value)) {
            $this->context->buildViolation($this->constraint->requiresMethodMessage)
                ->addViolation();
        }

        if ($this->isNotLowerCase($value)) {
            $this->context->buildViolation($this->constraint->mustBeLowercaseMessage)
                ->addViolation();
        }

        if ($this->hasInvalidCharAtEnd($value)) {
            $this->context->buildViolation($this->constraint->cantEndWithColonOrPercent)
                ->addViolation();
        }

        if ($this->isLengthInvalid($value)) {
            $this->context->buildViolation($this->constraint->tooLongMessage)
                ->setParameter('{{ maxLength }}', $this->constraint::ALLOWED_LENGTH)
                ->addViolation();
        }

    }

    private function isPrefixMissing(string $did): bool
    {
        return !str_starts_with($did, $this->constraint::PREFIX);
    }

    private function hasDisallowedChars(string $did): bool
    {
        return 1 !== preg_match($this->constraint::ALLOWED_CHARS_PATTERN, $did);
    }

    private function hasInvalidPartCount(string $did): bool
    {
        return $this->constraint::PARTS_COUNT
            > count(explode($this->constraint::SEPARATOR, $did));
    }

    private function isNotLowerCase(string $did): bool
    {
        $method = explode($this->constraint::SEPARATOR, $did)[1] ?? null;
        return ! (is_string($method) && ctype_lower($method));
    }

    private function hasInvalidCharAtEnd(string $did): bool
    {
        return str_ends_with($did, ':')
            || str_ends_with($did, '%');
    }

    public function isLengthInvalid(string $did): bool
    {
        return strlen($did) > $this->constraint::ALLOWED_LENGTH;
    }
}
