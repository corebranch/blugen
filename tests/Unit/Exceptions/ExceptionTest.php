<?php

namespace Blugen\Tests\Unit\Exceptions;

use Blugen\Exceptions\Exception;
use Blugen\Exceptions\PrefixNotDefined;
use Blugen\Exceptions\PrefixNotFound;
use Blugen\Exceptions\PrefixPathNotDirectory;
use Blugen\Tests\TestCase;

class ExceptionTest extends TestCase
{
    public function testPrefixNotDefinedWithDefaultMessage(): void
    {
        $exception = new PrefixNotDefined();
        
        $this->assertSame(PrefixNotDefined::ERROR_CODE, $exception->getCode());
        $this->assertStringContainsString('Base namespace not defined', $exception->getMessage());
        $this->assertStringContainsString('config/codegen.php', $exception->getMessage());
        $this->assertSame([], $exception->getContext());
    }

    public function testPrefixNotDefinedWithCustomMessage(): void
    {
        $customMessage = 'Custom error message';
        $exception = new PrefixNotDefined($customMessage);
        
        $this->assertSame($customMessage, $exception->getMessage());
        $this->assertSame(PrefixNotDefined::ERROR_CODE, $exception->getCode());
    }

    public function testPrefixNotDefinedWithContext(): void
    {
        $context = ['config_file' => '/path/to/config.php'];
        $exception = new PrefixNotDefined('', null, $context);
        
        $this->assertSame($context, $exception->getContext());
    }

    public function testPrefixNotFoundWithDefaultMessage(): void
    {
        $context = ['namespace' => 'TestNamespace\\'];
        $exception = new PrefixNotFound('', null, $context);
        
        $this->assertSame(PrefixNotFound::ERROR_CODE, $exception->getCode());
        $this->assertStringContainsString('TestNamespace\\', $exception->getMessage());
        $this->assertStringContainsString('composer dump-autoload', $exception->getMessage());
        $this->assertSame($context, $exception->getContext());
    }

    public function testPrefixNotFoundWithUnknownNamespace(): void
    {
        $exception = new PrefixNotFound();
        
        $this->assertStringContainsString('Unknown', $exception->getMessage());
    }

    public function testPrefixPathNotDirectoryWithDefaultMessage(): void
    {
        $context = ['path' => '/invalid/path'];
        $exception = new PrefixPathNotDirectory('', null, $context);
        
        $this->assertSame(PrefixPathNotDirectory::ERROR_CODE, $exception->getCode());
        $this->assertStringContainsString('/invalid/path', $exception->getMessage());
        $this->assertStringContainsString('not a valid directory', $exception->getMessage());
        $this->assertSame($context, $exception->getContext());
    }

    public function testPrefixPathNotDirectoryWithUnknownPath(): void
    {
        $exception = new PrefixPathNotDirectory();
        
        $this->assertStringContainsString('Unknown', $exception->getMessage());
    }

    public function testExceptionWithContext(): void
    {
        $context = ['key1' => 'value1', 'key2' => 'value2'];
        $exception = new PrefixNotDefined('', null, $context);
        
        $this->assertSame($context, $exception->getContext());
        
        // Test withContext method
        $additionalContext = ['key3' => 'value3'];
        $updated = $exception->withContext($additionalContext);
        
        $expectedContext = array_merge($context, $additionalContext);
        $this->assertSame($expectedContext, $updated->getContext());
        $this->assertSame($exception, $updated); // Should return same instance
    }

    public function testExceptionErrorCodes(): void
    {
        $this->assertSame(1001, PrefixNotDefined::ERROR_CODE);
        $this->assertSame(1002, PrefixNotFound::ERROR_CODE);
        $this->assertSame(1003, PrefixPathNotDirectory::ERROR_CODE);
        
        // Ensure each exception uses its own error code
        $prefixNotDefined = new PrefixNotDefined();
        $prefixNotFound = new PrefixNotFound();
        $prefixPathNotDirectory = new PrefixPathNotDirectory();
        
        $this->assertSame(1001, $prefixNotDefined->getCode());
        $this->assertSame(1002, $prefixNotFound->getCode());
        $this->assertSame(1003, $prefixPathNotDirectory->getCode());
    }

    public function testExceptionChaining(): void
    {
        $previousException = new \InvalidArgumentException('Previous error');
        $exception = new PrefixNotDefined('Test message', $previousException);
        
        $this->assertSame($previousException, $exception->getPrevious());
    }
}