<?php

namespace Blugen\Exceptions;

class PrefixNotDefined extends Exception
{
    public const ERROR_CODE = 1001;
    
    public function __construct(string $message = '', ?\Throwable $previous = null, array $context = [])
    {
        $defaultMessage = 'Base namespace not defined in configuration. '
            . 'Please ensure output.base_namespace is set in your config/codegen.php file.';
            
        parent::__construct(
            $message ?: $defaultMessage,
            self::ERROR_CODE,
            $previous,
            $context
        );
    }
}
