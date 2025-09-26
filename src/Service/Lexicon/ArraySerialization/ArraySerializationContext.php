<?php

namespace Blugen\Service\Lexicon\ArraySerialization;

interface ArraySerializationContext
{
    public function addField(ArrayField $field): void;

    /**
     * @return ArrayField[]
     */
    public function fields(): array;

    public function generateBody(): string;
}
