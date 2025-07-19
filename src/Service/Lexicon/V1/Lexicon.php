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
        $content = file_get_contents($path);

        $toArray = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return new Lexicon($toArray);
    }

    public function nsid(): string
    {
        return $this->lexicon['id'];
    }

    public function version(): int
    {
        return $this->lexicon['version'];
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
