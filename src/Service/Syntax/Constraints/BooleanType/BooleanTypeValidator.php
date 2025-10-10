<?php

namespace Blugen\Service\Syntax\Constraints\BooleanType;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BooleanSchema;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BooleanTypeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (! $constraint instanceof BooleanType) {
            throw new UnexpectedTypeException($constraint, BooleanType::class);
        }

        if (! is_bool($value)) {
            throw new UnexpectedTypeException($value, 'boolean');
        }

        $schema = new BooleanSchema(new Schema($constraint->schema()));

        if (! is_null($const = $schema->const()) && $value !== $const) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ actual }}', $this->formatValue($value))
                ->setParameter('{{ must }}', $this->formatValue($const))
                ->addViolation();
        }
    }
}
