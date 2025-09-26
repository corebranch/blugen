<?php

namespace Blugen\Service\Lexicon\ArraySerialization;

trait BuildsArraySerialization
{
    /** @var ArrayField[] */
    private array $fields = [];

    public function addField(ArrayField $field): void
    {
        $this->fields[] = $field;
    }

    /**
     * @return ArrayField[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function generateBody(): string
    {
        $array = array_map(
            fn (ArrayField $field) => $field->fullExpression(),
            $this->fields
        );

        return sprintf("return [%s];", implode(', ', $array));
    }
}
