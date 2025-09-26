<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\ArraySerialization\ArrayField;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContext;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContributor;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BlobSchema;
use Nette\PhpGenerator\ClassType;

class BlobComponentGenerator implements GeneratorInterface, ArraySerializationContributor
{
    private readonly BlobSchema $schema;

    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property,
        private readonly ?GeneratorInterface $context = null,
    ) {
        $this->schema = new BlobSchema($this->property->schema());
    }

    public function generate(): void
    {
        $name = $this->property->name();

        $this->class->addProperty($name)
            ->setPrivate()
            ->setType($this->phpType())
            ->setComment("@var {$this->docType()}" . $this->description() . $this->annotations());

        $this->class->addMethod($this->getterName())
            ->setPublic()
            ->setReturnType($this->phpType())
            ->setBody("return \$this->{$name};")
            ->setComment("Get uploaded blob object.\n\n@return {$this->docType()}");

        $this->class->addMethod("set" . ucfirst($name))
            ->setPublic()
            ->setReturnType('self')
            ->setBody("\$this->{$name} = \${$name};\nreturn \$this;")
            ->setComment("@param {$this->docType()} \${$name}\n@return self")
            ->addParameter($name)
            ->setType($this->phpType());
    }

    private function phpType(): string
    {
        return $this->property->isRequired() ? 'object' : '?object';
    }

    private function docType(): string
    {
        return $this->property->isRequired() ? 'object' : 'object|null';
    }

    private function description(): string
    {
        return $this->property->description() ? "\n\n" . $this->property->description() : '';
    }

    private function annotations(): string
    {
        $notes = [];

        if ($accept = $this->schema->accept()) {
            $notes[] = "@accept " . implode(', ', $accept);
        }

        if ($maxSize = $this->schema->maxSize()) {
            $notes[] = "@maxSize {$maxSize} bytes";
        }

        return $notes ? "\n\n" . implode("\n", $notes) : '';
    }

    private function getterName(): string
    {
        return 'get' . ucfirst($this->property->name());
    }

    private function addToArrayFragment(): void
    {
        if ($this->context instanceof ArraySerializationContext) {
            $this->context->addField($this->toArrayField());
        }
    }

    public function toArrayField(): ArrayField
    {
        $key = $this->property->name();
        $expression = "{$this->getterName()}()";

        return new ArrayField($key, $expression);
    }
}
