<?php

namespace Pharmaline\PhlAbc\Command;

use Exception;
use Pharmaline\PhlAbc\Service\ComposerService;
use Pharmaline\PhlAbc\Service\PermissionService;
use Pharmaline\PhlAbc\Service\RoleService;
use Pharmaline\PhlAbc\Service\YamlService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'abc:cleanup',
    description: 'Remove obsolete roles and permissions not defined in YAML configuration file(s).',
    help: 'This command scans all extension YAML files. Any role or permission in the database that is not found in these files will be deleted. By default the command runs in dry-run mode and only reports what would be removed; pass --force (-f) to actually delete the obsolete entries.'
)]
class CleanupAbcCommand extends Command
{
    public function __construct(
        private readonly YamlService $yamlService,
        private readonly ComposerService $composerService,
        private readonly PermissionService $permissionService,
        private readonly RoleService $roleService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Actually delete the obsolete roles and permissions from the database.'
        );
    }

    /**
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Default is true (Dry Run), false if --force is passed
        $dryRun = !$input->getOption('force');

        if ($dryRun) {
            $io->warning('DRY-RUN MODE ACTIVE. No data will be deleted. Use --force to execute.');
        } else {
            $io->info('Cleaning up roles and permissions...');
        }

        $packages = $this->composerService->getYamlFilesOfExtension();

        // Collect all defined keys for permissions and roles
        $validPermissionKeys = [];
        $validRoleKeys = [];

        foreach ($packages as $package) {
            foreach ($package as $key => $path) {
                $content = $this->yamlService->loadFile($path);

                if ($key === 'permissions' && isset($content['permissions']) && is_array($content['permissions'])) {
                    foreach ($content['permissions'] as $definition) {
                        if (isset($definition['permission_key'])) {
                            $validPermissionKeys[] = $definition['permission_key'];
                        }
                    }
                }

                if ($key === 'roles' && isset($content['roles']) && is_array($content['roles'])) {
                    foreach ($content['roles'] as $definition) {
                        if (isset($definition['role_key'])) {
                            $validRoleKeys[] = $definition['role_key'];
                        }
                    }
                }
            }
        }

        if (empty($validPermissionKeys) && empty($validRoleKeys)) {
            $io->error(
                'No permissions or roles definitions found in YAML files. Aborting to prevent accidental data loss.'
            );
            return Command::FAILURE;
        }

        // Cleanup permissions
        $result = $this->permissionService->cleanupObsoletePermissions($validPermissionKeys, $dryRun);
        $removedPermissions = $result->getRemoved();

        if (empty($removedPermissions)) {
            $io->success('No obsolete permissions to remove.');
        } else {
            $header = $dryRun ? ['Permissions to be removed'] : ['Removed permission'];
            $io->table(
                $header,
                array_map(static fn(string $key): array => [$key], $removedPermissions),
            );

            $label = $dryRun ? 'Cleanup would remove' : 'Removed';
            $io->success(sprintf('%s %d obsolete permission(s).', $label, count($removedPermissions)));
        }

        // Cleanup roles
        $result = $this->roleService->cleanupObsoleteRoles($validRoleKeys, $dryRun);
        $removedRoles = $result->getRemoved();

        if (empty($removedRoles)) {
            $io->success('No obsolete roles to remove.');
        } else {
            $header = $dryRun ? ['Roles to be removed'] : ['Removed role'];
            $io->table(
                $header,
                array_map(static fn(string $key): array => [$key], $removedRoles),
            );

            $label = $dryRun ? 'Cleanup would remove' : 'Removed';
            $io->success(sprintf('%s %d obsolete role(s).', $label, count($removedRoles)));
        }

        if ($dryRun) {
            $io->success('Dry run finished. Re-run with --force to apply changes.');
        } else {
            $io->success('Cleanup of permissions and roles finished.');
        }

        return Command::SUCCESS;
    }
}
