<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\LexiconInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\V1\Resolver\NsidResolver;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\RefSchema;
use Nette\PhpGenerator\ClassType;

class RefComponentGenerator implements GeneratorInterface
{
    private readonly RefSchema $schema;

    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property,
        private readonly ?LexiconInterface $lexicon = null,
    ) {
        $this->schema = new RefSchema($this->property->schema());
    }

    public function generate(): void
    {
        $this->generateProperty();
        $this->generateGetter();
        $this->generateSetter();
    }

    private function generateProperty(): void
    {
        $type = $this->phpType();
        $doc = $this->docType();

        $this->class->getNamespace()->addUse(trim($type, '?'));

        $this->class->addProperty($this->property->name())
            ->setPrivate()
            ->setType($type)
            ->setComment("@var {$doc}" . $this->description());
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

        $this->class->addMethod('set' . ucfirst($name))
            ->setComment("Set the value of \${$name}.\n\n@param {$this->docType()} \${$name}\n@return self")
            ->setPublic()
            ->setReturnType('self')
            ->setBody("\$this->{$name} = \${$name};\nreturn \$this;")
            ->addParameter($name)
            ->setType($this->phpType());
    }

    private function phpType(): string
    {
        $resolved = $this->resolveNamespace();
        return $this->property->isRequired() ? $resolved : '?' . $resolved;
    }

    private function docType(): string
    {
        $resolved = $this->resolveNamespace();
        return $this->property->isRequired() ? "\\$resolved" : "\\{$resolved}|null";
    }

    private function resolveNamespace(): string
    {
        $ref = $this->schema->ref();
        
        // If ref starts with "#", it's a partial reference that needs the current lexicon's NSID
        if (str_starts_with($ref, '#') && $this->lexicon !== null) {
            $ref = $this->lexicon->nsid() . $ref;
        }
        
        $resolved = NsidResolver::namespace($ref);
        return NamespaceResolver::prefixed(ltrim($resolved, "\\"));
    }

    private function description(): string
    {
        return $this->property->description()
            ? "\n\n" . $this->property->description()
            : '';
    }
}
