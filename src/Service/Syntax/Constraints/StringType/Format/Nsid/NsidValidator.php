<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Nsid;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NsidValidator extends ConstraintValidator
{
    private Nsid $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Nsid) {
            throw new UnexpectedTypeException($this->constraint, Nsid::class);
        }

        $this->constraint = $constraint;

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($this->hasValidLengthSize($value)) {
            $this->context->buildViolation($this->constraint->tooLongMessage)
                ->setParameter('{{ limit }}', $this->constraint::MAX_CHAR_LIMIT)
                ->addViolation();
        }

        if ($this->hasDisallowedChars($value)) {
            $this->context->buildViolation($this->constraint->disallowedCharsMessage)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }

        if (!$this->hasValidPartsCount($value)) {
            $this->context->buildViolation($this->constraint->tooFewPartsMessage)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ minLimit }}', $this->constraint::MIN_PARTS)
                ->addViolation();
        }

        if ($this->hasEmptyPart($value)) {
            $this->context->buildViolation($this->constraint->emptyPartMessage)
                ->addViolation();
        }

        if ($this->isPartsTooLong($value)) {
            $this->context->buildViolation($this->constraint->partTooLongMessage)
                ->setParameter('{{ maxLimit }}', $this->constraint::MAX_PART_LENGTH)
                ->addViolation();
        }

        if ($this->isStartsWithHyphen($value)) {
            $this->context->buildViolation($this->constraint->partStartsWithHyphenMessage)
                ->addViolation();
        }

        if ($this->isEndsWithHyphen($value)) {
            $this->context->buildViolation($this->constraint->partEndsWithHyphenMessage)
                ->addViolation();
        }

        if ($this->startsWithNumber($value)) {
            $this->context->buildViolation($this->constraint->startsWithNumberMessage)
                ->addViolation();
        }

        if (! $this->isValidIdentifier($value)) {
            $this->context->buildViolation($constraint->invalidNamePartMessage)
                ->addViolation();
        }
    }

    private function hasValidLengthSize(string $value): bool
    {
        return $this->constraint::MAX_CHAR_LIMIT < strlen($value);
    }

    private function hasDisallowedChars(string $value): bool
    {
        return preg_match($this->constraint::ALLOWED_CHARS_REGEX, $value) !== 1;
    }

    private function hasValidPartsCount(string $value): bool
    {
        return 3 <= count(explode($this->constraint::SEPARATOR, $value));
    }

    private function hasEmptyPart(string $value): bool
    {
        foreach (explode($this->constraint::SEPARATOR, $value) as $part) {
            if ('' === trim($part)) {
                return true;
            }
        }

        return false;
    }

    private function isPartsTooLong(string $value): bool
    {
        foreach (explode($this->constraint::SEPARATOR, $value) as $part) {
            if ($this->constraint::MAX_PART_LENGTH < strlen($part)) {
                return true;
            }
        }

        return false;
    }

    private function isStartsWithHyphen(string $value): bool
    {
        return in_array(true, array_map(
            fn(string $part) => str_starts_with($part, chr(45)),
            explode($this->constraint::SEPARATOR, $value)),
            true
        );
    }

    private function isEndsWithHyphen(string $value): bool
    {
        return in_array(true, array_map(
            fn(string $part) => str_ends_with($part, chr(45)),
            explode($this->constraint::SEPARATOR, $value)),
            true
        );
    }

    private function startsWithNumber(string $value): bool
    {
        $char = str_split($value)[0] ?? null;

        if (null === $char) {
            return false;
        }

        $asciiCode = ord($char);

        return 48 <= $asciiCode && $asciiCode <= 57;
    }

    private function isValidIdentifier(string $value): bool
    {
        $valuePieces = explode($this->constraint::SEPARATOR, $value);
        $value = end($valuePieces);

        return ! $this->startsWithNumber($value) && ! in_array(chr(45), str_split($value), true);
    }
}
