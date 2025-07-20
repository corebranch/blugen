<?php

namespace Blugen\Service\Lexicon\V1;

use Blugen\Service\Lexicon\LexiconInterface;
use Blugen\Service\Lexicon\V1\Resolver\NsidResolver;

class Lexicon implements LexiconInterface
{
    private array $lexicon;

    public function __construct(string|array $lexicon)
    {
        if (is_string($lexicon)) {
            $lexicon = json_decode($lexicon, true, 512, JSON_THROW_ON_ERROR);
        }

        $this->lexicon = $lexicon;
    }

    public static function fromNsid(Nsid $nsid): LexiconInterface
    {
        $path = NsidResolver::path($nsid);
        $nsid = $nsid->full();

        if (! file_exists($path)) {
            throw new \Error("The \"$nsid\" file does not exist: \"$path\"");
        }

        $content = file_get_contents($path);

        try {
            $toArray = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $realpath = realpath($path);
            throw new \JsonException("The \"$nsid\" does not contain valid JSON: $realpath");
        }

        return new Lexicon($toArray);
    }

    public function nsid(): string
    {
        return $this->lexicon['id'];
    }

    public function version(): int
    {
        return $this->lexicon['lexicon'];
    }

    public function description(): ?string
    {
        return $this->lexicon['description'] ?? null;
    }

    public function defs(): array
    {
        return $this->lexicon['defs'];
    }
}
