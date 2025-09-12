<?php

namespace Blugen\Service\Lexicon\V1\Resolver;

use Blugen\Config\ConfigManager;
use Blugen\Container;
use Blugen\Service\Lexicon\V1\Nsid;

class NsidResolver
{
    public static function namespace(string $nsid): string
    {
        [$nsid, $fragment] = static::parts($nsid);

        $nsidParts = explode(".", $nsid);
        $namespace = implode("\\", array_map('ucwords', $nsidParts));

        if ($fragment) {
            $namespace .= "\\" . ucwords($fragment);
        }

        return $namespace;
    }

    public static function path(Nsid $nsid): string
    {
        $id = $nsid->id();
        $ds = DIRECTORY_SEPARATOR;
        $basePath = rtrim(config()->get('lexicons.source'), $ds);
        $path = str_replace(".", $ds, $id);

        return $basePath . $ds . $path . ".json";
    }

    private static function parts(string $nsid): array
    {
        $parts = explode("#", $nsid, 2);
        $nsid = current($parts);
        $fragment = next($parts) ?? null;

        return [$nsid, $fragment];
    }
}
