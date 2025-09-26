<?php

namespace Blugen\Service\Lexicon\ArraySerialization;

interface ArraySerializationContributor
{
    public function toArrayField(): ArrayField;
}
