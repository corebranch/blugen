<?php

namespace Blugen\Service\Xrpc\Encoder;

use ReflectionClass;
use ReflectionProperty;

class PropertyCollector
{
    public function __construct(private readonly DataProvider $dataProvider)
    {
    }

    public function collect(): array
    {
        return array_filter(array_merge(...array_map(
            fn($property) => $this->pair($property),
            $this->properties()
        )), fn ($value) => $value !== null);
    }

    private function pair(\ReflectionProperty $property): array
    {
        return [$this->name($property) => $this->value($property)];
    }

    private function name(ReflectionProperty $property): string
    {
        return $property->getName();
    }

    private function value(ReflectionProperty $property)
    {
        $data = $this->dataProvider->getData();

        return $property->isInitialized($data)
            ? $property->getValue($data) ?? null
            : null;
    }

    private function properties(): array
    {
        return (new ReflectionClass($this->dataProvider->getData()))
            ->getProperties();
    }
}
