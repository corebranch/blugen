<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Handle;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HandleValidator extends ConstraintValidator
{
    private Handle $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Handle) {
            throw new UnexpectedTypeException($constraint, Handle::class);
        }

        $this->constraint = $constraint;

        if (! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($this->hasInvalidChars($value)) {
            $this->context->buildViolation($this->constraint->invalidCharsMessage)
                ->addViolation();

            return;
        }

        if ($this->isTooLong($value)) {
            $this->context->buildViolation($this->constraint->tooLongMessage)
                ->setParameter('{{ maxLength }}', (string) $this->constraint::MAX_LENGTH)
                ->addViolation();
        }

        $labels = explode('.', $value);
        if ($this->hasNotEnoughParts($labels)) {
            $this->context->buildViolation($this->constraint->needsDomainMessage)
                ->addViolation();

            return;
        }

        foreach ($labels as $i => $label) {
            if ($this->isEmptyPart($label)) {
                $this->context->buildViolation($this->constraint->emptyPartMessage)
                    ->addViolation();

                return;
            }

            if ($this->isPartTooLong($label)) {
                $this->context->buildViolation($this->constraint->partTooLongMessage)
                    ->setParameter('{{ maxLength }}', (string) $this->constraint::MAX_PART_LENGTH)
                    ->addViolation();
            }

            if ($this->hasHyphenEdges($label)) {
                $this->context->buildViolation($this->constraint->hyphenEdgeMessage)
                    ->addViolation();
            }

            if ($this->isFinalPart($i, $labels) && $this->tldDoesNotStartWithLetter($label)) {
                $this->context->buildViolation($this->constraint->tldLetterMessage)
                    ->addViolation();
            }
        }
    }

    private function hasInvalidChars(string $handle): bool
    {
        return 1 !== preg_match($this->constraint::ALLOWED_PATTERN, $handle);
    }

    private function isTooLong(string $handle): bool
    {
        return strlen($handle) > $this->constraint::MAX_LENGTH;
    }

    private function hasNotEnoughParts(array $labels): bool
    {
        return count($labels) < 2;
    }

    private function isEmptyPart(string $label): bool
    {
        return $label === '';
    }

    private function isPartTooLong(string $label): bool
    {
        return strlen($label) > $this->constraint::MAX_PART_LENGTH;
    }

    private function hasHyphenEdges(string $label): bool
    {
        return str_starts_with($label, '-') || str_ends_with($label, '-');
    }

    private function isFinalPart(int $i, array $labels): bool
    {
        return $i === array_key_last($labels);
    }

    private function tldDoesNotStartWithLetter(string $label): bool
    {
        return !preg_match('/^[a-zA-Z]/', $label);
    }
}
