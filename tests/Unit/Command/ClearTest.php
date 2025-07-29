<?php

namespace Blugen\Tests\Unit\Command;

use Blugen\Command\Clear;
use Blugen\Config\ConfigManager;
use Blugen\Tests\TestCase;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class ClearTest extends TestCase
{
    private Clear $command;
    private CommandTester $commandTester;
    private string $tempDir;
    private array $testFiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new Clear();
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);

        // Create temporary directory for testing
        $this->tempDir = sys_get_temp_dir() . '/blugen_test_' . uniqid();
        mkdir($this->tempDir);

        // Create test files
        $this->testFiles = [
            'TestClass1.php',
            'TestClass2.php',
            'subdirectory',
            '.gitignore'
        ];

        foreach ($this->testFiles as $file) {
            if ($file === 'subdirectory') {
                mkdir($this->tempDir . '/' . $file);
                file_put_contents($this->tempDir . '/' . $file . '/nested.php', '<?php echo "test";');
            } else {
                file_put_contents($this->tempDir . '/' . $file, '<?php echo "test";');
            }
        }
    }

    protected function tearDown(): void
    {
        // Clean up temporary directory
        if (is_dir($this->tempDir)) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->tempDir);
        }

        parent::tearDown();
    }

    public function testCommandConfiguration(): void
    {
        $this->assertSame('clear', $this->command->getName());
        $this->assertSame('Remove generated code', $this->command->getDescription());

        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasOption('except'));
        $this->assertTrue($definition->hasOption('dry-run'));
        $this->assertTrue($definition->hasOption('force'));

        $exceptOption = $definition->getOption('except');
        $this->assertTrue($exceptOption->isArray());
        $this->assertSame('e', $exceptOption->getShortcut());

        $dryRunOption = $definition->getOption('dry-run');
        $this->assertFalse($dryRunOption->acceptValue());

        $forceOption = $definition->getOption('force');
        $this->assertSame('f', $forceOption->getShortcut());
        $this->assertFalse($forceOption->acceptValue());
    }

    public function testExecuteWithDryRunShowsFilesToBeRemoved(): void
    {
        $this->mockContainerForSuccess();

        $this->commandTester->execute([
            '--dry-run' => true
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Files that would be removed:', $output);
        $this->assertStringContainsString('TestClass1.php', $output);
        $this->assertStringContainsString('TestClass2.php', $output);
        $this->assertStringContainsString('subdirectory', $output);
        $this->assertStringContainsString('.gitignore', $output);
        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithExceptOption(): void
    {
        $this->mockContainerForSuccess();

        $this->commandTester->execute([
            '--dry-run' => true,
            '--except' => ['TestClass1.php', '.gitignore']
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringNotContainsString('TestClass1.php', $output);
        $this->assertStringContainsString('TestClass2.php', $output);
        $this->assertStringContainsString('subdirectory', $output);
    }

    public function testExecuteWithForceSkipsConfirmation(): void
    {
        $this->mockContainerForSuccess();

        $this->commandTester->execute([
            '--force' => true,
            '--except' => ['.gitignore']
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Successfully removed', $output);
        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        // Verify files were actually removed
        $this->assertFileDoesNotExist($this->tempDir . '/TestClass1.php');
        $this->assertFileDoesNotExist($this->tempDir . '/TestClass2.php');
        $this->assertDirectoryDoesNotExist($this->tempDir . '/subdirectory');
        $this->assertFileExists($this->tempDir . '/.gitignore'); // Should still exist
    }

    public function testExecuteWithNoFilesToRemove(): void
    {
        // Remove all test files except . and ..
        $filesystem = new Filesystem();
        $filesystem->remove(array_map(
            fn($file) => $this->tempDir . '/' . $file,
            $this->testFiles
        ));

        $this->mockContainerForSuccess();

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('No files to remove.', $output);
        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithPrefixNotDefinedException(): void
    {
        $this->mockContainerWithoutBaseNamespace();

        $this->commandTester->execute(['--force' => true]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Base namespace not defined', $output);
        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithPrefixNotFoundException(): void
    {
        $this->mockContainerWithMissingPrefix();

        $this->commandTester->execute(['--force' => true]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('not found in autoloader', $output);
        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithPrefixPathNotDirectoryException(): void
    {
        $this->mockContainerWithInvalidPath();

        $this->commandTester->execute(['--force' => true]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('not a valid directory', $output);
        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithUserCancellation(): void
    {
        $this->mockContainerForSuccess();

        // Simulate user input 'n' (no)
        $this->commandTester->setInputs(['n']);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Operation cancelled.', $output);
        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        // Verify files were not removed
        $this->assertFileExists($this->tempDir . '/TestClass1.php');
        $this->assertFileExists($this->tempDir . '/TestClass2.php');
    }

    public function testExecuteWithUserConfirmation(): void
    {
        $this->mockContainerForSuccess();

        // Simulate user input 'y' (yes)
        $this->commandTester->setInputs(['y']);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Successfully removed', $output);
        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());

        // Verify files were actually removed
        $this->assertFileDoesNotExist($this->tempDir . '/TestClass1.php');
        $this->assertFileDoesNotExist($this->tempDir . '/TestClass2.php');
    }

    private function mockContainerForSuccess(): void
    {
        $classLoader = $this->createMock(ClassLoader::class);
        $classLoader->method('getPrefixesPsr4')
            ->willReturn(['TestNamespace\\' => [$this->tempDir]]);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('get')
            ->with('output.base_namespace')
            ->willReturn('TestNamespace\\');

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['loader', $classLoader],
                [ConfigManager::class, $configManager],
            ]);

        \Blugen\Container::set($container);
    }

    private function mockContainerWithoutBaseNamespace(): void
    {
        $classLoader = $this->createMock(ClassLoader::class);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('get')
            ->with('output.base_namespace')
            ->willReturn(null);

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['loader', $classLoader],
                [ConfigManager::class, $configManager],
            ]);

        \Blugen\Container::set($container);
    }

    private function mockContainerWithMissingPrefix(): void
    {
        $classLoader = $this->createMock(ClassLoader::class);
        $classLoader->method('getPrefixesPsr4')
            ->willReturn([]); // Empty prefixes

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('get')
            ->with('output.base_namespace')
            ->willReturn('TestNamespace\\');

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['loader', $classLoader],
                [ConfigManager::class, $configManager],
            ]);

        \Blugen\Container::set($container);
    }

    private function mockContainerWithInvalidPath(): void
    {
        $invalidPath = '/path/that/does/not/exist';

        $classLoader = $this->createMock(ClassLoader::class);
        $classLoader->method('getPrefixesPsr4')
            ->willReturn(['TestNamespace\\' => [$invalidPath]]);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('get')
            ->with('output.base_namespace')
            ->willReturn('TestNamespace\\');

        $container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['loader', $classLoader],
                [ConfigManager::class, $configManager],
            ]);

        \Blugen\Container::set($container);
    }
}
