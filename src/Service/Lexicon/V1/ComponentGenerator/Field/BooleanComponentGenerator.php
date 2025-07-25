<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BooleanSchema;
use Nette\PhpGenerator\ClassType;

class BooleanComponentGenerator implements GeneratorInterface
{
    private readonly BooleanSchema $schema;

    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property
    ) {
        $this->schema = new BooleanSchema($this->property->schema());
    }

    public function generate(): void
    {
        $this->generateProperty();
        $this->generateGetter();
        $this->generateSetter();
    }

    private function generateProperty(): void
    {
        $this->class->addProperty($this->property->name(), $this->schema->default())
            ->setPrivate()
            ->setType($this->phpType())
            ->setComment("@var {$this->docType()}" . $this->description() . $this->extraDocBlock());
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

        $method->addParameter($name)->setType($this->phpType());

        $method->setBody($this->generateSetterBody($name));
        $method->setComment(implode("\n", $this->generateSetterDocBlock($name)));
    }

    private function generateSetterBody(string $name): string
    {
        if ($this->schema->const() !== null) {
            return "// Const field. This value is fixed.\nreturn \$this;";
        }

        $body = '';
        if ($this->schema->default() !== null) {
            $default = $this->schema->default() ? 'true' : 'false';
            $body .= "\$this->{$name} = \${$name} ?? {$default};\n";
        } else {
            $body .= "\$this->{$name} = \${$name};\n";
        }

        $body .= "return \$this;";
        return $body;
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

    private function phpType(): string
    {
        return $this->property->isNullable() ? '?bool' : 'bool';
    }

    private function docType(): string
    {
        return $this->property->isNullable() ? 'bool|null' : 'bool';
    }

    private function description(): string
    {
        return $this->property->description() ? "\n\n" . $this->property->description() : '';
    }

    private function extraDocBlock(): string
    {
        $lines = [];

        if ($this->schema->default() !== null) {
            $lines[] = "@default " . ($this->schema->default() ? 'true' : 'false');
        }

        if ($this->schema->const() !== null) {
            $lines[] = "@const " . ($this->schema->const() ? 'true' : 'false');
        }

        return $lines ? "\n" . implode("\n", $lines) : '';
    }
}
