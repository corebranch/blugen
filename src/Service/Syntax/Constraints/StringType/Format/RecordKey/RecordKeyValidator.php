<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\RecordKey;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RecordKeyValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof RecordKey) {
            throw new UnexpectedTypeException($constraint, RecordKey::class);
        }

        if (! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $actualLength = strlen($value);
        $maxLength = $constraint::MAX_LENGTH;
        $minLength = $constraint::MIN_LENGTH;

        if ($maxLength < $actualLength || $actualLength < $minLength) {
            $this->context->buildViolation($constraint->invalidLengthMessage)
                ->setParameter('{{ max }}', $maxLength)
                ->setParameter('{{ min }}', $minLength)
                ->setParameter('{{ actualLength }}', $actualLength)
                ->addViolation();

            return;
        }

        $regex = $constraint::REGEX;

        if (1 !== preg_match($regex, $value)) {
            $this->context->buildViolation($constraint->regexFailedMessage)
                ->addViolation();

            return;
        }

        if (in_array($value, ['.', '..'], true)) {
            $this->context->buildViolation($constraint->cantBeDotMessage)
                ->addViolation();
        }
    }
}
