<?php

namespace Blugen\Service\Syntax\Constraints\StringType;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\StringSchema;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StringTypeValidator extends ConstraintValidator
{
    private StringType $constraint;
    private StringSchema $schema;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof StringType) {
            throw new UnexpectedTypeException($constraint, StringType::class);
        }

        $this->constraint = $constraint;
        $this->schema = new StringSchema(new Schema($this->constraint->schema));

        if (! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $context = $this->context;
        $schema = $this->schema;

        if ($this->didBreakLock($value)) {
            $context->buildViolation($constraint->constValueMessage)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setParameter('{{ const }}', $this->formatValue($schema->const()))
                ->addViolation();

            return;
        }

        if ($this->notFollowsFormat($schema->format(), $value)) {

            $context->buildViolation($constraint->invalidFormatMessage)
                ->setParameter('{{ format }}', $this->formatValue($schema->format()))
                ->addViolation();

            return;
        }

        if ($this->notInEnum($value)) {
            $context->buildViolation($constraint->allowedValuesMessage)
                ->setParameter('{{ allowedValues }}', $this->formatValues($schema->enum()))
                ->addViolation();

            return;
        }

        if ($this->exceedsMaxLength($value)) {
            $context->buildViolation($constraint->maxLengthMessage)
                ->setParameter('{{ limit }}', $schema->maxLength())
                ->addViolation();
        }

        if ($this->exceedsMinLength($value)) {
            $context->buildViolation($constraint->minLengthMessage)
                ->setParameter('{{ limit }}', $schema->minLength())
                ->addViolation();
        }

        if ($this->exceedsMaxGraphemeLength($value)) {
            $context->buildViolation($constraint->maxGraphemeMessage)
                ->setParameter('{{ limit }}', $schema->maxGraphemes())
                ->addViolation();
        }

        if ($this->exceedsMinGraphemeLength($value)) {
            $context->buildViolation($constraint->minGraphemeMessage)
                ->setParameter('{{ limit }}', $schema->minGraphemes())
                ->addViolation();
        }
    }

    private function notFollowsFormat(?string $format, string $value): bool
    {
        try {
            if (null === $format) {
                return false;
            }

            $constraint = FormatValidatorFactory::create($format);

            $context = clone $this->context;
            $validator = new ($constraint->validatedBy());

            $validator->initialize($context);
            $validator->validate($value, $constraint);

            if ($context->getViolations()->count() > 0) {
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function exceedsMaxLength(string $value): bool
    {
        $maxLength = $this->schema->maxLength();

        if (null === $maxLength) {
            return false;
        }

        return $maxLength < strlen($value);
    }

    private function exceedsMinLength(string $value): bool
    {
        $minLength = $this->schema->minLength();

        if (null === $minLength) {
            return false;
        }

        return $minLength > strlen($value);
    }

    private function exceedsMaxGraphemeLength(string $value): bool
    {
        $maxLength = $this->schema->maxGraphemes();

        if (null === $maxLength) {
            return false;
        }

        return $maxLength < grapheme_strlen($value);
    }

    private function exceedsMinGraphemeLength(string $value): bool
    {
        $minLength = $this->schema->minGraphemes();

        if (null === $minLength) {
            return false;
        }

        return $minLength > grapheme_strlen($value);
    }

    private function notInEnum(string $value): bool
    {
        $enum = $this->schema->enum();

        if (null === $enum) {
            return false;
        }

        if (! in_array($value, $enum, true)) {
            return true;
        }

        return false;
    }

    private function didBreakLock(string $value): bool
    {
        $const = $this->schema->const();

        if (null === $const) {
            return false;
        }

        if ($const !== $value) {
            return true;
        }

        return false;
    }
}
