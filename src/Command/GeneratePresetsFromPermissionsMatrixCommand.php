<?php

namespace Pharmaline\PhlAbc\Command;

use Exception;
use InvalidArgumentException;
use Pharmaline\PhlAbc\Import\PermissionMatrixLoader;
use Pharmaline\PhlAbc\Service\PresetsYamlGeneratorService;
use Pharmaline\PhlAbc\Utility\PathUtility;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'abc:generate:presetsFromPermissionsMatrix',
    description: 'Generates a presets.yaml from a permission matrix markdown file.'
)]
class GeneratePresetsFromPermissionsMatrixCommand extends Command
{
    public function __construct(
        private readonly PermissionMatrixLoader $loader,
        private readonly PresetsYamlGeneratorService $generator,
        private readonly PathUtility $pathUtility,
    ) {
        parent::__construct();
    }

    /**
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option(description: 'Input file')]
        string $inputFile = '',
        #[Option(description: 'Path where the output file will be written')]
        string $outputPath = 'var/exports/presets.yaml',
    ): int {
        $io = new SymfonyStyle($input, $output);

        if (empty($inputFile)) {
            $io->error('No input file provided.');
            return Command::FAILURE;
        }

        $fileExtension = pathinfo($outputPath, PATHINFO_EXTENSION);

        $outputPath = $this->pathUtility->normalizePath($outputPath, $fileExtension);
        $dir = dirname($outputPath);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            $io->error(sprintf('Could not create directory "%s".', $dir));
            return Command::FAILURE;
        }

        $io->info('Exporting presets.yaml');

        try {
            $permissionMatrix = $this->loader->loadFromFile($inputFile);

            $content = $this->generator->generate($permissionMatrix);

            if (file_put_contents($outputPath, $content) === false) {
                throw new RuntimeException(sprintf('Could not write to "%s".', $outputPath));
            }
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('presets.yaml exported to "%s".', $outputPath));

        return Command::SUCCESS;
    }
}
