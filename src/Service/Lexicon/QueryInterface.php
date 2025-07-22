<?php

namespace Blugen\Service\Lexicon;

interface QueryInterface
{
    public function setParams(ParamsInterface $params): self;
    public function getParams(): ParamsInterface;
}
