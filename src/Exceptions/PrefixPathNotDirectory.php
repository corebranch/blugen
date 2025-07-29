<?php

namespace Blugen\Exceptions;

class PrefixPathNotDirectory extends Exception
{
    public const ERROR_CODE = 1003;
    
    public function __construct(string $message = '', ?\Throwable $previous = null, array $context = [])
    {
        $path = $context['path'] ?? 'Unknown';
        $defaultMessage = "Target path '{$path}' is not a valid directory. "
            . 'Please ensure the directory exists and has proper permissions, or run code generation first.';
            
        parent::__construct(
            $message ?: $defaultMessage,
            self::ERROR_CODE,
            $previous,
            $context
        );
    }
}
