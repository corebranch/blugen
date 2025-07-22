<?php

namespace Blugen\Service\Xrpc\Encoder\Encoders;

use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Xrpc\Encoder\DataProviderFactory;
use Blugen\Service\Xrpc\Encoder\PropertyCollector;

class ParamsEncoder implements EncoderInterface
{
    private readonly PropertyCollector $collector;

    public function __construct(private readonly QueryInterface $params, ?PropertyCollector $collector = null)
    {
        $this->collector = $collector ?? new PropertyCollector(DataProviderFactory::create($this->params));
    }

    public function encode(): string
    {
        return http_build_query($this->normalize($this->collector->collect()));
    }

    private function normalize(array $properties): array
    {
        foreach ($properties as &$property) {
            if (is_bool($property)) {
                $property = ($property) ? 'true' : 'false';
                continue;
            }

            if (is_array($property)) {
                $property = $this->normalize($property);
            }
        }

        return $properties;
    }
}
