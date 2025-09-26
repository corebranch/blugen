<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\ArraySerialization\ArrayField;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContext;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContributor;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\StringSchema;
use Nette\PhpGenerator\ClassType;

class StringComponentGenerator implements GeneratorInterface, ArraySerializationContributor
{
    private readonly StringSchema $schema;

    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property,
        private readonly ?GeneratorInterface $context = null,
    ) {
        $this->schema = new StringSchema($this->property->schema());
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

        $method->addParameter($name)
            ->setType($this->type());

        $method->setBody($this->generateSetterBody($name));
        $method->setComment(implode("\n", $this->generateSetterDocBlock($name)));
    }

    private function generateDocBlock(): array
    {
        $lines = ["@var {$this->docType()}"];

        if ($this->schema->format()) {
            $lines[] = "@format {$this->schema->format()}";
        }

        if ($this->schema->enum()) {
            $lines[] = "@enum {" . implode(', ', $this->schema->enum()) . "}";
        }

        if ($this->schema->knownValues()) {
            $lines[] = "@knownValues {" . implode(', ', $this->schema->knownValues()) . "}";
        }

        if ($this->schema->minLength() !== null || $this->schema->maxLength() !== null) {
            $lines[] = "@length " .
                ($this->schema->minLength() ?? '0') . "-" . ($this->schema->maxLength() ?? '∞');
        }

        if ($this->schema->minGraphemes() !== null || $this->schema->maxGraphemes() !== null) {
            $lines[] = "@graphemes " .
                ($this->schema->minGraphemes() ?? '0') . "-" . ($this->schema->maxGraphemes() ?? '∞');
        }

        if ($this->schema->default()) {
            $lines[] = "@default {$this->schema->default()}";
        }

        if ($this->schema->const()) {
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

        if ($this->schema->const()) {
            $lines[] = "@note This value is constant and cannot be changed.";
        }

        $lines[] = "@return self";
        return $lines;
    }

    private function generateSetterBody(string $name): string
    {
        if ($this->schema->const()) {
            return "// Const field. This value is fixed.\nreturn \$this;";
        }

        $body = "";

        if ($this->schema->enum()) {
            $enumList = implode("', '", $this->schema->enum());
            $body .= "if (!in_array(\${$name}, ['{$enumList}'], true)) {\n";
            $body .= "    throw new \\InvalidArgumentException('Invalid value for {$name}');\n";
            $body .= "}\n";
        }

        if ($this->schema->default()) {
            $body .= "\$this->{$name} = \${$name} ?? '{$this->schema->default()}';\n";
        } else {
            $body .= "\$this->{$name} = \${$name};\n";
        }

        $body .= "return \$this;";
        return $body;
    }

    private function type(): string
    {
        return $this->property->isNullable() ? '?string' : 'string';
    }

    private function docType(): string
    {
        return $this->property->isNullable() ? 'string|null' : 'string';
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
