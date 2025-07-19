<?php

namespace Blugen\Service\Lexicon\V1\DefGenerator\Field;

use Blugen\Service\Lexicon\DefinitionInterface;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Definition;
use Blugen\Service\Lexicon\V1\Nsid;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\V1\TypeSpecificDefinition\Field\StringTypeDefinition;
use InvalidArgumentException;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class StringGenerator implements GeneratorInterface
{
    private readonly PhpFile $file;
    private readonly PhpNamespace $namespace;
    private readonly EnumType $enum;

    public function __construct(private readonly StringTypeDefinition $definition)
    {
        $this->validateDefinition();
        [$namespaceStr, $enumName] = NamespaceResolver::namespace(
            $this->definition->lexicon(),
            $this->definition
        );

        $this->file = new PhpFile();
        $this->namespace = $this->file->addNamespace($namespaceStr);
        $this->enum = $this->namespace->addEnum($enumName);
    }

    public function generate(): string
    {
        $knownValues = $this->definition->knownValues();

        if (empty($knownValues)) {
            throw new InvalidArgumentException('Cannot generate enum without known values');
        }

        foreach ($knownValues as $value) {
            $this->addEnumCase($value);
        }

        return $this->file->__toString();
    }

    private function validateDefinition(): void
    {
        if (!$this->definition->knownValues()) {
            throw new InvalidArgumentException('StringTypeDefinition must have known values to generate enum');
        }
    }

    private function addEnumCase(string $value): void
    {
        $caseInfo = $this->processCaseValue($value);

        $this->enum
            ->addCase($caseInfo['name'], $caseInfo['value'])
            ->setComment($caseInfo['comment']);
    }

    /**
     * Processes a case value and returns normalized case information.
     *
     * @param string $value The raw case value from lexicon
     * @return array{name: string, value: string, comment: string|null}
     */
    private function processCaseValue(string $value): array
    {
        $originalValue = $value;
        $comment = null;

        // Handle reference-style cases
        if ($this->isReference($value)) {
            $definition = $this->resolveReference($value);
            $comment = $definition->description();
            $value = $definition->name();
        }

        // Normalize case name for PHP enum
        $caseName = $this->normalizeCaseName($value);

        return [
            'name' => $caseName,
            'value' => $originalValue,
            'comment' => $comment
        ];
    }

    private function isReference(string $value): bool
    {
        return str_starts_with($value, '#') || str_contains($value, '#');
    }

    private function resolveReference(string $value): DefinitionInterface
    {
        if (str_starts_with($value, '#')) {
            $nsid = $this->buildRelativeNsid($value);
        } else {
            $nsid = new Nsid($value);
        }

        return Definition::fromNsid($nsid);
    }

    private function buildRelativeNsid(string $fragment): Nsid
    {
        $currentNsid = new Nsid($this->definition->lexicon()->nsid());
        $currentId = $currentNsid->id();

        return new Nsid($currentId . $fragment);
    }

    private function normalizeCaseName(string $value): string
    {
        // Remove unwanted characters and normalize
        $normalized = str_replace(['!', '-'], ['', '_'], $value);

        // Extract meaningful part if it contains separators
        if (str_contains($normalized, '#')) {
            $parts = explode('#', $normalized);
            $normalized = end($parts);
        }

        return strtoupper($normalized);
    }
}
