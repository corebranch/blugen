<?php

namespace Blugen\Service\Syntax\Constraints\StringType\Format\AtUri;

use Blugen\Service\Syntax\Constraints\StringType\Format\AtIdentifier\AtIdentifier;
use Blugen\Service\Syntax\Constraints\StringType\Format\Nsid\Nsid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AtUriValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof AtUri) {
            throw new UnexpectedTypeException($constraint, AtUri::class);
        }

        if (! is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $parts = explode('#', $value);

        if (count($parts) > 2) {
            $this->context->buildViolation($constraint->maxOneFragmentMessage)
                ->addViolation();

            return;
        }

        $uri = current($parts);
        $fragment = next($parts) ?: null;

        if (! preg_match('//u', $uri) || preg_match('/[^\x00-\x7F]/', $uri)) {
            $this->context->buildViolation($constraint->invalidCharsMessage)
                ->addViolation();
        }

        $uriParts = explode('/', $uri);

        if (! str_starts_with($uri, 'at://')) {
            $this->context->buildViolation($constraint->mustStartWithPrefixMessage)
                ->addViolation();

            return;
        }

        if (count($uriParts) < 3) {
            $this->context->buildViolation($constraint->requiresAuthorityMessage)
                ->addViolation();

            return;
        }

        if ($this->validateVia(new AtIdentifier(), $uriParts[2])->getViolations()->count() > 0) {
            $this->context->buildViolation($constraint->authorityMustBeHandleOrDidMessage)
                ->addViolation();

            return;
        }

        if (4 <= count($uriParts)) {
            if (0 === strlen($uriParts[3])) {
                $this->context->buildViolation($constraint->invalidSlashAfterAuthorityMessage)
                    ->addViolation();

                return;
            }

            if ($this->validateVia(new Nsid(), $uriParts[3])->getViolations()->count() > 0) {
                $this->context->buildViolation($constraint->firstPathMustBeNsidMessage)
                    ->addViolation();

                return;
            }
        }

        if (5 <= count($uriParts)) {
            if (0 === strlen($uriParts[4])) {
                $this->context->buildViolation($constraint->invalidSlashAfterCollectionMessage)
                    ->addViolation();

                return;
            }
        }

        if (6 <= count($uriParts)) {
            $this->context->buildViolation($constraint->tooManyPathSegmentsMessage)
                ->addViolation();

            return;
        }

        if (2 <= count($parts) && $fragment === null) {
            $this->context->buildViolation($constraint->emptyFragmentMessage)
                ->addViolation();

            return;
        }

        if ($fragment !== null) {
            if (0 === strlen($fragment) || '/' !== $fragment[0]) {
                $this->context->buildViolation($constraint->emptyFragmentMessage)
                    ->addViolation();

                return;
            }

            if (!preg_match($constraint::FRAGMENT_CHARS_REGEX, $fragment)) {
                $this->context->buildViolation($constraint->invalidFragmentCharsMessage)
                    ->addViolation();

                return;
            }
        }

        if (strlen($parts[0] . ($fragment !== null ? '#' . $fragment : '')) > ($constraint::MAX_SIZE * 1024)) {
            $this->context->buildViolation($constraint->tooLongMessage)
                ->setParameter('{{ actualSize }}', $this->kbSize($parts[0]))
                ->setParameter('{{ maxSize }}', $constraint::MAX_SIZE)
                ->addViolation();
        }
    }

    private function validateVia(Constraint $constraint, string $value): ExecutionContextInterface
    {
        $context = clone $this->context;
        $validator = new ($constraint->validatedBy());

        $validator->initialize($context);
        $validator->validate($value, $constraint);

        return $context;
    }

    private function kbSize(string $value): int
    {
        return strlen($value) * 1024;
    }
}
