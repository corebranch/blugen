<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\V1\Resolver\NsidResolver;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnionSchema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;

class UnionComponentGenerator implements GeneratorInterface
{
    private readonly UnionSchema $schema;

    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property,
    ) {
        $this->schema = new UnionSchema($this->property->schema());
    }

    public function generate(): void
    {
        $this->generateProperty();
        $this->generateGetter();
        $this->generateSetter();
    }

    private function generateProperty(): void
    {
        foreach ($this->schema->refs() as $ref) {
            $resolved = NsidResolver::namespace("$ref");
            $prefixed = NamespaceResolver::prefixed(ltrim($resolved, "\\"));

            $this->class->getNamespace()?->addUse($prefixed);
        }

        $this->class->addProperty($this->property->name())
            ->setPrivate()
            ->setType($this->phpType())
            ->setComment("@var {$this->docType()}{$this->description()}");
    }

    private function generateGetter(): void
    {
        $name = $this->property->name();

        $this->class->addMethod('get' . ucfirst($name))
            ->setPublic()
            ->setReturnType($this->phpType())
            ->setBody("return \$this->{$name};")
            ->setComment("Get the value of \${$name}.\n\n@return {$this->docType()}");
    }

    private function generateSetter(): void
    {
        $name = $this->property->name();

        $method = $this->class->addMethod('set' . ucfirst($name))
            ->setPublic()
            ->setReturnType('self')
            ->setBody("\$this->{$name} = \${$name};\nreturn \$this;");

        $method->addParameter($name)
            ->setType($this->phpType());

        $doc = [
            "Set the value of \${$name}",
            "",
            "@param {$this->docType()} \${$name}",
        ];

        if ($this->schema->closed()) {
            $doc[] = "@note This union is closed. Only specific variants are allowed.";
        }

        $doc[] = "@return self";

        $method->setComment(implode("\n", $doc));
    }

    private function phpType(): string
    {
        $types = array_map(fn($ref) => NsidResolver::namespace($ref), $this->schema->refs());

        if (! $this->property->isRequired()) {
            $types[] = 'null';
        }

        if (! $this->schema->closed()) {
            $types = ['mixed'];
        }

        return implode('|', $types);
    }

    private function docType(): string
    {
        return $this->phpType();
    }

    private function description(): string
    {
        return $this->property->description() ? "\n\n" . $this->property->description() : '';
    }
}
