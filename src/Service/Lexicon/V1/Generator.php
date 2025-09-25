<?php

namespace Blugen\Service\Lexicon\V1;

use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\ReaderInterface;
use Blugen\Service\Lexicon\V1\Factory\DefGeneratorFactory;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;

class Generator implements GeneratorInterface
{
    public function __construct(private readonly ReaderInterface $reader)
    {
    }

    public function generate(): array
    {
        $generated = [];

        foreach($this->reader->read()->get() as $lexiconPath) {
            $lexicon = new Lexicon(file_get_contents($lexiconPath));

            foreach($lexicon->defs() as $definitionName => $definition) {
                $definition = new Definition($lexicon, $definitionName);

                $classPath = NamespaceResolver::path($lexicon, $definition);
                $generator = DefGeneratorFactory::create($definition);

                if (is_null($generator)) {
                    continue;
                }

                $generatedClass = $generator->generate();

                if (! is_array($generatedClass)) {
                    $generatedClass = [basename($classPath) => $generatedClass];
                }

                $classPath = dirname($classPath);

                $generated[$classPath] = array_merge($generated[$classPath] ?? [], $generatedClass);
            }
        }

        return $generated;
    }
}
