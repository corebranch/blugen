<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\ArraySerialization\ArrayField;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContext;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContributor;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ArraySchema;
use Nette\PhpGenerator\ClassType;

class ArrayComponentGenerator implements GeneratorInterface, ArraySerializationContributor
{
    private readonly ArraySchema $schema;

    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property,
        private readonly ?GeneratorInterface $context = null
    ) {
        $this->schema = new ArraySchema($this->property->schema());
    }

    public function generate(): void
    {
        $this->generateProperty();
        $this->generateGetter();
        $this->generateSetter();
        $this->addToArrayFragment();
    }

    private function generateProperty(): void
    {
        $this->class->addProperty($this->property->name())
            ->setPrivate()
            ->setType($this->type())
            ->setComment(implode("\n", $this->generateDocBlock()) . $this->description());
    }

    private function generateGetter(): void
    {
        $name = $this->property->name();

        $this->class->addMethod('get' . ucfirst($name))
            ->setPublic()
            ->setReturnType($this->type())
            ->setBody("return \$this->{$name};")
            ->setComment("Get the value of \${$name}.\n\n@return {$this->docType()}");
    }

    private function generateSetter(): void
    {
        $name = $this->property->name();

        $method = $this->class->addMethod('set' . ucfirst($name))
            ->setPublic()
            ->setReturnType('self');

        $method->addParameter($name)->setType($this->type());

        $method->setBody("\$this->{$name} = \${$name};\nreturn \$this;");
        $method->setComment(implode("\n", [
            "Set the value of \${$name}",
            "",
            "@param {$this->docType()} \${$name}",
            "@return self"
        ]));
    }

    private function generateDocBlock(): array
    {
        $lines = ["@var {$this->docType()}"];

        $min = $this->schema->minLength();
        $max = $this->schema->maxLength();

        if ($min !== null || $max !== null) {
            $lines[] = "@length " . ($min ?? '0') . '-' . ($max ?? '∞');
        }

        if ($this->schema->items()) {
            $lines[] = "@items " . $this->schema->items();
        }

        return $lines;
    }

    private function type(): string
    {
        return $this->property->isNullable() ? '?array' : 'array';
    }

    private function docType(): string
    {
        $itemType = $this->schema->items();

        $base = $itemType ? $itemType . '[]' : 'array';

        return $this->property->isNullable() ? "{$base}|null" : $base;
    }

    private function description(): string
    {
        return $this->property->description()
            ? "\n\n" . $this->property->description()
            : '';
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
        $expression = "\$this->$key";

        return new ArrayField($key, $expression);
    }
}
