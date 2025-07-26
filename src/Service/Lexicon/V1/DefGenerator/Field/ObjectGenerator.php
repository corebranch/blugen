<?php

namespace Blugen\Service\Lexicon\V1\DefGenerator\Field;

use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Factory\ComponentGeneratorFactory;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\TypeSpecificDefinition\Field\ObjectTypeDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class ObjectGenerator implements GeneratorInterface
{
    private readonly PhpFile $file;
    private readonly PhpNamespace $namespace;
    private readonly ClassType $class;

    public function __construct(
        private readonly ObjectTypeDefinition $definition
    )
    {
        $fqcnParts = NamespaceResolver::namespace($this->definition->lexicon(), $this->definition);

        $this->file = new PhpFile();
        $this->namespace = $this->file->addNamespace(current($fqcnParts));
        $this->class = $this->namespace->addClass(next($fqcnParts));

        $this->file->setStrictTypes();
    }

    public function generate(): string
    {
        foreach ($this->properties() as $name => $property) {
            ComponentGeneratorFactory::create(
                $this->class,
                new Property(
                    $name,
                    new Schema($property),
                    in_array($name, $this->nullable(), true),
                    in_array($name, $this->required(), true),
                ),
                $this->definition->lexicon()
            )->generate();
        }

        return $this->file->__toString();
    }

    private function properties(): array
    {
        return $this->definition->properties();
    }

    private function nullable(): array
    {
        return $this->definition->nullable() ?? [];
    }

    private function required(): array
    {
        return $this->definition->required() ?? [];
    }
}
