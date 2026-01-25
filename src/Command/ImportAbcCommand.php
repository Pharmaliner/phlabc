<?php

namespace Pharmaline\PhlAbc\Command;

use Exception;
use Pharmaline\PhlAbc\Service\ComposerService;
use Pharmaline\PhlAbc\Service\PermissionService;
use Pharmaline\PhlAbc\Service\PresetService;
use Pharmaline\PhlAbc\Service\RoleService;
use Pharmaline\PhlAbc\Service\YamlService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'abc:import',
    description: 'This command will import all your roles, permissions and presets. You can define this in your personal extensions.'
)]
class ImportAbcCommand extends Command
{
    public function __construct(
        private readonly YamlService $yamlService,
        private readonly ComposerService $composerService,
        private readonly PermissionService $permissionService,
        private readonly PresetService $presetService,
        private readonly RoleService $roleService
    ) {
        parent::__construct();
    }

    /**
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function __invoke(OutputInterface $output): int
    {
        $output->writeln('<info>Importing roles, permissions and presets...</info>');

        $packages = $this->composerService->getYamlFilesOfExtension();

        // Collect all definitions first, then import by type to make sure,
        // all permissions from all packages are imported before referenced by roles/presets
        $allPermissions = [];
        $allRoles = [];
        $allPresets = [];

        foreach ($packages as $package) {
            foreach ($package as $key => $path) {
                $content = $this->yamlService->loadFile($path);

                switch ($key) {
                    case 'permissions':
                        $allPermissions[] = $content;
                        break;
                    case 'roles':
                        $allRoles[] = $content;
                        break;
                    case 'presets':
                        $allPresets[] = $content;
                        break;
                }
            }
        }

        foreach ($allPermissions as $content) {
            $this->permissionService->importIntoDatabase($content);
        }

        foreach ($allRoles as $content) {
            $this->roleService->importIntoDatabase($content);
        }

        foreach ($allPresets as $content) {
            $this->presetService->importIntoDatabase($content);
            $this->presetService->removePermissionsFromRoles($content);
        }

        $output->writeln('<info>Finished import of roles, permissions and presets.</info>');

        return Command::SUCCESS;
    }
}
