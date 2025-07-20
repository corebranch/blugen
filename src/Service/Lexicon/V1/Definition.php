<?php

namespace Blugen\Service\Lexicon\V1;

use Blugen\Service\Lexicon\DefinitionInterface;
use Blugen\Service\Lexicon\LexiconInterface;

class Definition implements DefinitionInterface
{
    public function __construct(
        private readonly LexiconInterface $lexicon,
        private readonly string $name
    )
    {
        if (! isset($this->lexicon->defs()[$this->name])) {
            throw new \InvalidArgumentException("Definition '{$this->name}' does not exist in the lexicon.");
        }
    }

    public static function fromNsid(Nsid $nsid): DefinitionInterface
    {
        $lexicon = Lexicon::fromNsid($nsid);

        if (! $name = $nsid->fragment()) {
            $name = 'main';
        }

        return new self($lexicon, str_starts_with($name, '#') ? substr($name, 1) : $name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->lexicon->defs()[$this->name]['type'];
    }

    public function description(): ?string
    {
        return $this->lexicon->defs()[$this->name]['description'] ?? null;
    }

    public function lexicon(): LexiconInterface
    {
        return $this->lexicon;
    }

    public function __get(string $name): mixed
    {
        $def = $this->lexicon->defs()[$this->name()] ?? null;

        $pathParts = explode('.', $name);

        foreach ($pathParts as $part) {
            if (is_array($def) && array_key_exists($part, $def)) {
                $def = $def[$part];
            } else {
                return null;
            }
        }

        return $def;
    }
}
