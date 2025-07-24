<?php

namespace Blugen\Service\Lexicon\V1\DefGenerator\Primary;

use Blugen\Enum\ClassNameSuffix;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Factory\ComponentGeneratorFactory;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\V1\TypeSpecificDefinition\Primary\RecordTypeDefinition;
use Nette\InvalidArgumentException;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class RecordGenerator implements GeneratorInterface
{
    private readonly PhpFile $file;
    private readonly PhpNamespace $namespace;
    private readonly ClassType $class;

    public function __construct(
        private readonly RecordTypeDefinition $definition,
    )
    {
        [$namespaceString, $className] = NamespaceResolver::namespace($this->definition->lexicon(), $this->definition);

        $this->file = new PhpFile();
        $this->namespace = $this->file->addNamespace($namespaceString);
        try {
            $this->class = $this->namespace->addClass($className);
        } catch (InvalidArgumentException $e) {
            if (! str_contains($e->getMessage(), "is not valid class name.")) {
                throw $e;
            }

            $this->class = $this->namespace->addClass("{$className}" . ClassNameSuffix::DEFINITION->value);
        }
        $this->file->setStrictTypes();
    }

    public function generate(): string
    {
        foreach ($this->definition->record()->properties() as $name => $property) {
            ComponentGeneratorFactory::create($this->class, $property)->generate();
        }

        return $this->file->__toString();
    }
}
