<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\Uri;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UriValidator extends ConstraintValidator
{
    private Uri $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Uri) {
            throw new UnexpectedTypeException($constraint, Uri::class);
        }

        $this->constraint = $constraint;

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($this->isInvalidUri($value)) {
            $this->context->buildViolation($this->constraint->invalidMessage)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }

        if ($this->exceedsMaxSize($value)) {
            $this->context->buildViolation($this->constraint->tooLongMessage)
                ->setParameter('{{ maxKb }}', (string) $this->constraint::MAX_KB)
                ->addViolation();
        }
    }

    private function isInvalidUri(string $uri): bool
    {
        return false === filter_var($uri, FILTER_VALIDATE_URL);
    }

    private function exceedsMaxSize(string $uri): bool
    {
        $kbSize = strlen($uri) / 1024;
        return $kbSize > $this->constraint::MAX_KB;
    }
}
