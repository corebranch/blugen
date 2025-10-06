<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\AtIdentifier;

use Blugen\Service\Syntax\Constraints\StringType\Format\Did\Did;
use Blugen\Service\Syntax\Constraints\StringType\Format\Handle\Handle;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


class AtIdentifierValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AtIdentifier) {
            throw new UnexpectedTypeException($constraint, AtIdentifier::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        /*
            WORKAROUND: Normally I would use AtLeastOneOf, but it either doesn't propagate groups correctly
                        or I couldn't manage to make it work. For now this is a manual check.
                        If Symfony fixes this (or I figure out the right usage), I can replace it with AtLeastOneOf.
        */

        $subConstraints = [
            Did::class,
            Handle::class,
        ];

        $failedConstraints = [];

        foreach ($subConstraints as $subConstraintFQCN) {
            $subConstraint = new $subConstraintFQCN();
            $subValidator = new ($subConstraint->validatedBy());
            $subContext = clone $this->context;

            $subValidator->initialize($subContext);
            $subValidator->validate($value, $subConstraint);

            if (0 < count($subContext->getViolations())) {
                $failedConstraints[$subConstraintFQCN] = $subConstraintFQCN;
            }
        }

        if (count($failedConstraints) === count($subConstraints)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }
    }
}
