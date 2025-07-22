<?php

namespace Blugen\Service\Lexicon;

interface ProcedureInterface
{
    public function setSchema(InputInterface $schema): self;
    public function getSchema(): InputInterface;
}
