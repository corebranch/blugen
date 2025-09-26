<?php

namespace Blugen\Service\Lexicon\V1\ComponentGenerator\Field;

use Blugen\Service\Lexicon\ArraySerialization\ArrayField;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContext;
use Blugen\Service\Lexicon\ArraySerialization\ArraySerializationContributor;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\V1\Property;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnknownSchema;
use Nette\PhpGenerator\ClassType;

class UnknownComponentGenerator implements GeneratorInterface, ArraySerializationContributor
{
    public function __construct(
        private readonly ClassType $class,
        private readonly Property $property,
        private readonly ?GeneratorInterface $context = null
    )
    {
    }

    public function generate(): void
    {
        $type = 'mixed';
        $name = $this->property->name();
        $description = $this->property->description();
        $docBlock = [
            $description,
            "",
            "@param $type \$$name",
        ];

        $this->class->addProperty($name)
            ->setPrivate()
            ->setType($type)
            ->setComment(implode("", $docBlock));

        $setterName = 'set' . ucfirst($name);
        $setterDocBlock = array_merge($docBlock, ["@return static"]);
        $this->class->addMethod($setterName)
            ->setComment(implode("\n", $setterDocBlock))
            ->setPublic()
            ->setReturnType("static")
            ->setBody("\$this->{$name} = \${$name};\nreturn \$this;")
            ->addParameter($name)
            ->setType($type);

        $getterName = $this->getterName();
        $getterDocBlock = ["Get the value of \${$name}", "", "@return $type",];
        $this->class->addMethod($getterName)
            ->setComment(implode("\n", $getterDocBlock))
            ->setPublic()
            ->setReturnType($type)
            ->setBody("return \$this->{$name};");
    }

    private function getterName(): string
    {
        return "get" . ucfirst($this->property->name());
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
