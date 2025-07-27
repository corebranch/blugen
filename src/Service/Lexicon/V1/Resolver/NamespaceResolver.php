<?php

namespace Blugen\Service\Lexicon\V1\Resolver;

use Blugen\Config\ConfigManager;
use Blugen\Service\Lexicon\DefinitionInterface;
use Blugen\Service\Lexicon\LexiconInterface;

use function \toPascalCase;

class NamespaceResolver
{
    /**
     * Return the full namespace and class name derived from NSID and definition.
     *
     * @return array{0: string, 1: string} [$namespace, $className]
     */
    public static function namespace(LexiconInterface $lexicon, DefinitionInterface $definition): array
    {
        self::assertDefinitionExists($lexicon, $definition);

        $namespaceParts = array_map('ucfirst', explode('.', $lexicon->nsid()));
        $definitionName = toPascalCase($definition->name());

        // If definition is named 'main', override class name with last part of NSID
        if ($definition->name() === "main") {
            $definitionName = array_pop($namespaceParts);
        }

        $namespace = implode('\\', $namespaceParts);
        $namespace = self::prefixed($namespace);

        return [$namespace, $definitionName];
    }

    public static function prefixed(string $namespace): string
    {
        $baseNamespace = container()->get(ConfigManager::class)->get('output.base_namespace') ?? null;
        return $baseNamespace . $namespace;
    }

    /**
     * Return the filesystem path where this definition class should be written.
     */
    public static function path(LexiconInterface $lexicon, DefinitionInterface $definition): string
    {
        self::assertDefinitionExists($lexicon, $definition);

        [$namespace, $className] = self::namespace($lexicon, $definition);

        $baseNamespace = container()->get(ConfigManager::class)->get('output.base_namespace') ?? null;

        if ($baseNamespace) {
            $namespace = str_replace(
                $baseNamespace,
                container()->get('loader')->getPrefixesPsr4()[$baseNamespace][0] . DIRECTORY_SEPARATOR,
                $namespace
            );
        }

        return str_replace('\\', DIRECTORY_SEPARATOR, $namespace)
            . DIRECTORY_SEPARATOR
            . $className . ".php";
    }

    /**
     * Ensure the definition actually exists inside the lexicon.
     */
    private static function assertDefinitionExists(LexiconInterface $lexicon, DefinitionInterface $definition): void
    {
        $name = $definition->name();

        if (!isset($lexicon->defs()[$name])) {
            throw new \InvalidArgumentException("Definition \"$name\" does not exist in the lexicon.");
        }
    }
}
