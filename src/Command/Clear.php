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
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be deleted without actually deleting'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Skip confirmation prompt'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $isDryRun = $input->getOption('dry-run');
        $isForced = $input->getOption('force');
        $except = $input->getOption('except');

        try {
            $files = $this->getFilesToRemove($except);
            
            if (empty($files)) {
                $output->writeln('<info>No files to remove.</info>');
                return Command::SUCCESS;
            }

            if ($isDryRun) {
                $output->writeln('<comment>Files that would be removed:</comment>');
                foreach ($files as $file) {
                    $output->writeln('  - ' . $file);
                }
                return Command::SUCCESS;
            }

            if (!$isForced && !$this->confirmDeletion($input, $output, $files)) {
                $output->writeln('<comment>Operation cancelled.</comment>');
                return Command::SUCCESS;
            }

            $filesystem->remove($files);
            
            $output->writeln(sprintf(
                '<info>Successfully removed %d file(s).</info>',
                count($files)
            ));
            
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
    private function getFilesToRemove(array $except): array
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
            throw new PrefixNotDefined();
        }

        if (! isset($prefixes[$baseNamespace])) {
            throw new PrefixNotFound('', null, ['namespace' => $baseNamespace]);
        }

        $paths = $prefixes[$baseNamespace];
        $targetPath = current($paths);

        // Ensure the target path exists and is a directory
        if (! is_dir($targetPath)) {
            throw new PrefixPathNotDirectory('', null, ['path' => $targetPath]);
        }

        return $targetPath;
    }

    private function confirmDeletion(InputInterface $input, OutputInterface $output, array $files): bool
    {
        $helper = $this->getHelper('question');
        
        $output->writeln('<comment>Files to be removed:</comment>');
        foreach ($files as $file) {
            $output->writeln('  - ' . basename($file));
        }
        
        $question = new ConfirmationQuestion(
            sprintf('Are you sure you want to delete %d file(s)? [y/N] ', count($files)),
            false
        );
        
        return $helper->ask($input, $output, $question);
    }
}
