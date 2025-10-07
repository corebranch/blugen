<?php

namespace Blugen\Service\Syntax\Constraints\IntegerType;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\IntegerSchema;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IntegerTypeValidator extends ConstraintValidator
{
    private IntegerType $constraint;
    private IntegerSchema $schema;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof IntegerType) {
            throw new UnexpectedTypeException($constraint, IntegerType::class);
        }

        $this->constraint = $constraint;
        $this->schema = new IntegerSchema(new Schema($constraint->schema()));

        if (!is_int($value)) {
            throw new UnexpectedTypeException($value, 'integer');
        }

        if ($this->isConstMismatch($value)) {
            $this->context->buildViolation($constraint->constMessage)
                ->setParameter('{{ const }}', $this->schema->const())
                ->addViolation();

            return;
        }

        if ($this->notInEnum($value)) {
            $this->context->buildViolation($constraint->notInEnumMessage)
                ->setParameter('{{ enumValues }}', implode(', ', $this->schema->enum()))
                ->addViolation();

            return;
        }

        if ($this->isLessThanMin($value)) {
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('{{ value }}', (string)$value)
                ->setParameter('{{ min }}', (string)$this->schema->minimum())
                ->addViolation();
        }

        if ($this->isGreaterThanMax($value)) {
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('{{ value }}', (string)$value)
                ->setParameter('{{ max }}', (string)$this->schema->maximum())
                ->addViolation();
        }
    }

    private function isGreaterThanMax(int $value): bool
    {
        $maxLimit = $this->schema->maximum();

        if (is_null($maxLimit)) {
            return false;
        }

        return $value > $maxLimit;
    }

    private function isLessThanMin(int $value): bool
    {
        $minLimit = $this->schema->minimum();

        if (is_null($minLimit)) {
            return false;
        }

        return $value < $minLimit;
    }

    private function isConstMismatch(int $value): bool
    {
        $const = $this->schema->const();

        if (is_null($const)) {
            return false;
        }

        return $value !== $const;
    }

    private function notInEnum(int $value): bool
    {
        $enum = $this->schema->enum();


        if (is_null($enum)) {
            return false;
        }

        return !in_array($value, $enum, true);
    }
}
