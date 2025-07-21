<?php

namespace Pharmaline\PhlAbc\Service;

use Pharmaline\PhlAbc\Domain\Model\Permission;
use Pharmaline\PhlAbc\Domain\Model\Role;
use Pharmaline\PhlAbc\Domain\Repository\PermissionRepository;
use Pharmaline\PhlAbc\Domain\Repository\RoleRepository;
use Pharmaline\PhlAbc\Exception\MissingKeyAttributeInYamlFileException;
use Pharmaline\PhlAbc\Exception\PermissionNotFoundException;
use Pharmaline\PhlAbc\Exception\RoleNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class PresetService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly LoggerInterface $logger,
        private readonly PermissionRepository $permissionRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Typo3QuerySettings $typo3QuerySettings
    )
    {
        $this->typo3QuerySettings->setRespectStoragePage(false);
        $this->permissionRepository->setDefaultQuerySettings($this->typo3QuerySettings);
        $this->roleRepository->setDefaultQuerySettings($this->typo3QuerySettings);


    }

    /**
     * @TODO cleanup code - code looks a bit messy
     * @param array $content
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidQueryException
     * @throws MissingKeyAttributeInYamlFileException
     * @throws PermissionNotFoundException
     * @throws RoleNotFoundException
     * @throws UnknownObjectException
     */
    public function importIntoDatabase(array $content): void
    {
        foreach ($content as $presetDefinitions) {
            foreach ($presetDefinitions as $index => $presetDefinition) {
                if (array_key_exists('role', $presetDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: role in preset yaml file for role preset definition: ' . $index
                    );
                }

                if (array_key_exists('permissions', $presetDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: permissions in preset yaml file for role preset definition: ' . $index
                    );
                }

                $role = $this->roleRepository->findOneBy(
                    [
                        'role_key' => $presetDefinition['role']
                    ]
                );

                if ($role instanceof Role === false) {
                    throw new RoleNotFoundException('Role not found: ' . $presetDefinition['role']);
                }

                $this->logger->log(LogLevel::INFO, 'Import preset definition for role: ' . $role->getRoleKey());

                if (in_array('*', $presetDefinition['permissions'])) {
                    $permissions = $this->permissionRepository->findAll();
                    foreach ($permissions as $permission) {
                        $role->addPermission($permission);
                    }

                    $this->roleRepository->update($role);

                    continue;
                }

                $permissionDefinitions = $presetDefinition['permissions'];
                foreach ($permissionDefinitions as $permissionDefinition) {
                    if (str_contains($permissionDefinition, '*')) {
                        $permissionPrefix = str_replace('*', '', $permissionDefinition);
                        $permissions = $this->permissionRepository->findByPermissionPrefix($permissionPrefix);

                        if (empty($permissions) === false) {
                            foreach($permissions as $permission) {
                                $role->addPermission($permission);
                            }
                        }
                    } else {
                        $permission = $this->permissionRepository->findOneBy(['permission_key' => $permissionDefinition]);

                        if($permission instanceof Permission === false) {
                            throw new PermissionNotFoundException('Permission not found: ' . $permissionDefinition);
                        }

                        $role->addPermission($permission);
                    }
                }

                $this->roleRepository->update($role);
            }
        }

        $this->persistenceManager->persistAll();
    }

    /**
     * @TODO cleanup code - code looks a bit messy
     * @param array $content
     * @return void
     * @throws MissingKeyAttributeInYamlFileException
     * @throws RoleNotFoundException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function removePermissionsFromRoles(array $content): void
    {
        foreach ($content as $presetDefinitions) {
            foreach ($presetDefinitions as $index => $presetDefinition) {
                if (array_key_exists('role', $presetDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: role in preset yaml file for role preset definition: ' . $index
                    );
                }

                if (array_key_exists('permissions', $presetDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: permissions in preset yaml file for role preset definition: ' . $index
                    );
                }

                $role = $this->roleRepository->findOneBy(
                    [
                        'role_key' => $presetDefinition['role']
                    ]
                );

                if ($role instanceof Role === false) {
                    throw new RoleNotFoundException('Role not found: ' . $presetDefinition['role']);
                }

                $this->logger->log(LogLevel::INFO, 'Remove permissions from the role according to the preset definition: ' . $role->getRoleKey());

                if (in_array('*', $presetDefinition['permissions'])) {
                    continue;
                }

                $assignedPermissions = [];
                $permissions = $role->getPermissions();

                foreach ($permissions as $permission) {
                    $assignedPermissions[] = $permission->getPermissionKey();
                }

                $inherits = [];
                foreach($presetDefinition['permissions'] as $permissionKey) {
                    if (str_contains($permissionKey, '*')) {
                        $inherits[] = str_replace('*', '', $permissionKey);
                    }
                }

                $diff = array_diff($assignedPermissions, $presetDefinition['permissions']);

                foreach($diff as $permission) {

                    if (array_filter($inherits, function($key) use ($permission) {
                        return str_contains($permission, $key);
                    })) {

                        continue;
                    }

                    $permissionObject = $this->permissionRepository->findOneBy(['permission_key' => $permission]);
                    $role->removePermission($permissionObject);
                }

                $this->roleRepository->update($role);
            }
        }

        $this->persistenceManager->persistAll();
    }
}