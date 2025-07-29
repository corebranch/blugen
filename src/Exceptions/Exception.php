<?php

namespace Blugen\Exceptions;

abstract class Exception extends \Exception
{
    protected array $context = [];
    
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    
    public function getContext(): array
    {
        return $this->context;
    }
    
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }
}
