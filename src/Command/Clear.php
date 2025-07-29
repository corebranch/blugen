<?php

namespace Blugen\Command;

use Blugen\Config\ConfigManager;
use Blugen\Exceptions\Exception;
use Blugen\Exceptions\PrefixNotDefined;
use Blugen\Exceptions\PrefixNotFound;
use Blugen\Exceptions\PrefixPathNotDirectory;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Clear extends Command
{
    protected function configure(): void
    {
        $this->setName('clear')
            ->setDescription('Remove generated code')
            ->addOption(
            'except',
            'e',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Files to be excluded',
            []
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();

        try {
            $files = $this->willBeRemove($input->getOption('except'));
            $filesystem->remove($files);
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws PrefixNotDefined
     * @throws PrefixPathNotDirectory
     * @throws PrefixNotFound
     */
    private function willBeRemove(array $except): array
    {
        $targetPath = $this->target();
        $files = scandir($targetPath);

        $filtered = array_filter($files, fn ($file) => ! in_array($file, array_merge(
            ['.', '..'],
            array_values($except)
        )));

        return array_map(function (string $file) use ($targetPath) {
            return $targetPath . DIRECTORY_SEPARATOR . $file;
        }, $filtered);
    }

    /**
     * @throws PrefixNotDefined
     * @throws PrefixNotFound
     * @throws PrefixPathNotDirectory
     */
    private function target(): string
    {
        $prefixes = container()->get('loader')->getPrefixesPsr4();
        $baseNamespace = config()->get('output.base_namespace');

        if (! $baseNamespace) {
            throw new PrefixNotDefined("Base namespace not defined");
        }

        if (! isset($prefixes[$baseNamespace])) {
            throw new PrefixNotFound("Namespace '{$baseNamespace}' not found in autoloader");
        }

        $paths = $prefixes[$baseNamespace];
        $targetPath = current($paths);

        // Ensure the target path exists and is a directory
        if (! is_dir($targetPath)) {
            throw new PrefixPathNotDirectory("Target path '{$targetPath}' is not a valid directory");
        }

        return $targetPath;
    }
}
