<?php

namespace Blugen\Exceptions;

class PrefixNotFound extends Exception
{
    public const ERROR_CODE = 1002;
    
    public function __construct(string $message = '', ?\Throwable $previous = null, array $context = [])
    {
        $namespace = $context['namespace'] ?? 'Unknown';
        $defaultMessage = "Namespace '{$namespace}' not found in autoloader. "
            . 'Please run \'composer dump-autoload\' or check your composer.json autoload configuration.';
            
        parent::__construct(
            $message ?: $defaultMessage,
            self::ERROR_CODE,
            $previous,
            $context
        );
    }
}
