<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\ArraySerialization\ArrayField;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContext;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContributor;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\IntegerSchema;
use Nette\PhpGenerator\ClassType;

class IntegerComponentGenerator implements GeneratorInterface, ArraySerializationContributor
{
    private readonly IntegerSchema $schema;

    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property,
        private readonly ?GeneratorInterface $context = null,
    ) {
        $this->schema = new IntegerSchema($this->property->schema());
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
        $this->class->addProperty($this->property->name(), $this->schema->default())
            ->setPrivate()
            ->setType($this->phpType())
            ->setComment(implode("\n", $this->generateDocBlock()) . $this->description());
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
            ->setReturnType('self');

        $method->addParameter($name)
            ->setType($this->phpType());

        $method->setBody($this->generateSetterBody($name));
        $method->setComment(implode("\n", $this->generateSetterDocBlock($name)));
    }

    private function generateDocBlock(): array
    {
        $lines = ["@var {$this->docType()}"];

        if ($this->schema->minimum() !== null || $this->schema->maximum() !== null) {
            $lines[] = "@range " . ($this->schema->minimum() ?? '-∞') . " to " . ($this->schema->maximum() ?? '∞');
        }

        if ($this->schema->enum()) {
            $lines[] = "@enum {" . implode(', ', $this->schema->enum()) . "}";
        }

        if ($this->schema->default() !== null) {
            $lines[] = "@default {$this->schema->default()}";
        }

        if ($this->schema->const() !== null) {
            $lines[] = "@const {$this->schema->const()}";
        }

        return $lines;
    }

    private function generateSetterDocBlock(string $name): array
    {
        $lines = [
            "Set the value of \${$name}",
            "",
            "@param {$this->docType()} \${$name}",
        ];

        if ($this->schema->const() !== null) {
            $lines[] = "@note This value is constant and cannot be changed.";
        }

        $lines[] = "@return self";
        return $lines;
    }

    private function generateSetterBody(string $name): string
    {
        if ($this->schema->const() !== null) {
            return "// Const field. This value is fixed.\nreturn \$this;";
        }

        $body = "";

        if ($this->schema->enum()) {
            $enumList = implode(", ", $this->schema->enum());
            $body .= "if (!in_array(\${$name}, [{$enumList}], true)) {\n";
            $body .= "    throw new \\InvalidArgumentException('Invalid value for {$name}');\n";
            $body .= "}\n";
        }

        if ($this->schema->default() !== null) {
            $body .= "\$this->{$name} = \${$name} ?? {$this->schema->default()};\n";
        } else {
            $body .= "\$this->{$name} = \${$name};\n";
        }

        $body .= "return \$this;";
        return $body;
    }

    private function phpType(): string
    {
        return $this->property->isNullable() ? '?int' : 'int';
    }

    private function docType(): string
    {
        return $this->property->isNullable() ? 'int|null' : 'int';
    }

    private function description(): string
    {
        return $this->property->description() ? "\n\n" . $this->property->description() : '';
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
