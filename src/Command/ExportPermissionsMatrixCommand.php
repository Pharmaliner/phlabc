<?php

namespace Pharmaline\PhlAbc\Command;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use Pharmaline\PhlAbc\Export\Formatter\PermissionMatrixFormatterRegistry;
use Pharmaline\PhlAbc\Export\PermissionMatrixBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'abc:export:permissionsmatrix',
    description: 'Exports the role-permission matrix as file.'
)]
class ExportPermissionsMatrixCommand extends Command
{
    public function __construct(
        private readonly PermissionMatrixBuilder $builder,
        private readonly PermissionMatrixFormatterRegistry $formatters,
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
        #[Option(description: 'Path where the output file will be written')]
        string $outputPath = 'var/exports/permissions-matrix',
        #[Option(description: 'Output format ("md" or "csv")')]
        string $format = 'md',
    ): int {
        $io = new SymfonyStyle($input, $output);

        try {
            $formatter = $this->formatters->get(strtolower($format));
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $outputPath = $this->normalizePath($outputPath, $formatter->extension());
        $dir = dirname($outputPath);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            $io->error(sprintf('Could not create directory "%s".', $dir));
            return Command::FAILURE;
        }

        $io->info(sprintf('Exporting permission matrix (%s)...', $formatter->name()));

        try {
            $matrix = $this->builder->build();
            $content = $formatter->format($matrix);

            if (file_put_contents($outputPath, $content) === false) {
                throw new RuntimeException(sprintf('Could not write to "%s".', $outputPath));
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('Permission matrix exported to "%s".', $outputPath));
        return Command::SUCCESS;
    }

    private function normalizePath(string $path, string $extension): string
    {
        $currentExt = pathinfo($path, PATHINFO_EXTENSION);
        if ($currentExt === '') {
            return $path . '.' . $extension;
        }
        $dir = dirname($path);
        $base = basename($path, '.' . $currentExt);
        return $dir . '/' . $base . '.' . $extension;
    }
}
