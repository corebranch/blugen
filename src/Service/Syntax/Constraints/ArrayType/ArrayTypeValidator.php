<?php

namespace Blugen\Service\Syntax\Constraints\ArrayType;

use Blugen\Service\Lexicon\SchemaInterface;
use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ArraySchema;
use Blugen\Service\Syntax\Constraints\LexConstraint;
use Blugen\Service\Syntax\Factory\ConstraintFactory;
use Blugen\Service\Syntax\Factory\SchemaFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ArrayTypeValidator extends ConstraintValidator
{
    private ArraySchema $schema;

    public function validate(mixed $value, Constraint $constraint): void
    {
        $subContext = clone $this->context;

        if (! $constraint instanceof ArrayType) {
            throw new UnexpectedTypeException($constraint, ArrayType::class);
        }

        $this->schema = new ArraySchema(new Schema($constraint->schema()));

        if (! is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if ($this->hasInvalidMinLength($value)) {
            $this->context->buildViolation($constraint->invalidMinLengthMessage)
                ->setParameter('{{ limit }}', $this->schema->minLength())
                ->setParameter('{{ actualCount }}', count($value))
                ->addViolation();
        }

        if ($this->hasInvalidMaxLength($value)) {
            $this->context->buildViolation($constraint->invalidMaxLengthMessage)
                ->setParameter('{{ limit }}', $this->schema->maxLength())
                ->setParameter('{{ actualCount }}', count($value))
                ->addViolation();
        }

        $itemsSchemaArr = $this->schema->items();
        $itemsSchemaArr['type'] ??= null;

        /** @var SchemaInterface $itemsSchema */
        $itemsSchema = container()->get(SchemaFactory::class)::create($itemsSchemaArr['type'], $itemsSchemaArr);

        /** @var LexConstraint&Constraint $subConstraint */
        $subConstraint = container()->get(ConstraintFactory::class)::create($itemsSchema->type(), $itemsSchemaArr);
        /** @var ConstraintValidator $subValidator */
        $subValidator = new ($subConstraint->validatedBy());

        foreach($value as $index => $item) {
            $subValidator->initialize($subContext);

            try {
                $subValidator->validate($item, $subConstraint);
            } catch (UnexpectedTypeException $e) {
                $subContext->addViolation($e->getMessage());
            }

            if (0 < $subContext->getViolations()->count()) {
                $this->context->buildViolation($constraint->invalidMessage)
                    ->setParameter('{{ type }}', $this->formatValue($itemsSchema->type()))
                    ->setParameter('{{ index }}', $index)
                    ->addViolation();

                break;
            }
        }
    }

    private function hasInvalidMaxLength(array $value): bool
    {
        if (is_null($maxLength = $this->schema->maxLength())) {
            return false;
        }

        if (count($value) <= $maxLength) {
            return false;
        }

        return true; // is invalid
    }

    private function hasInvalidMinLength(array $value): bool
    {
        if (is_null($minLength = $this->schema->minLength())) {
            return false;
        }

        if (count($value) >= $minLength) {
            return false;
        }

        return true; // is invalid
    }
}
