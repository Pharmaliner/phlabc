<?php

declare(strict_types=1);

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

/**
 * @phpstan-type PresetDefinition array{
 *     role: string,
 *     permissions: array<int, string>
 * }
 */
class PresetService
{
    /** @var array<int, string> */
    private const array REQUIRED_FIELDS = ['role', 'permissions'];

    private const string WILDCARD_ALL = '*';

    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly LoggerInterface $logger,
        private readonly PermissionRepository $permissionRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Typo3QuerySettings $typo3QuerySettings
    ) {
        $this->configureRepositories();
    }

    /**
     * @param array<array<PresetDefinition>> $content
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
            $this->processPresetDefinitions($presetDefinitions, true);
        }
    }

    /**
     * @param array<array<PresetDefinition>> $content
     * @return void
     * @throws MissingKeyAttributeInYamlFileException
     * @throws RoleNotFoundException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws PermissionNotFoundException|InvalidQueryException
     */
    public function removePermissionsFromRoles(array $content): void
    {
        foreach ($content as $presetDefinitions) {
            $this->processPresetDefinitions($presetDefinitions, false);
        }
    }

    private function configureRepositories(): void
    {
        $this->typo3QuerySettings->setRespectStoragePage(false);
        $this->permissionRepository->setDefaultQuerySettings($this->typo3QuerySettings);
        $this->roleRepository->setDefaultQuerySettings($this->typo3QuerySettings);
    }

    /**
     * @param array<PresetDefinition> $presetDefinitions
     * @param bool $isImport
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidQueryException
     * @throws MissingKeyAttributeInYamlFileException
     * @throws PermissionNotFoundException
     * @throws RoleNotFoundException
     * @throws UnknownObjectException
     */
    private function processPresetDefinitions(array $presetDefinitions, bool $isImport): void
    {
        foreach ($presetDefinitions as $index => $presetDefinition) {
            $this->validatePresetDefinition($presetDefinition, $index);

            if ($isImport) {
                $this->addPermissionsToRole($presetDefinition);
            } else {
                $this->removePermissionsFromRole($presetDefinition);
            }
        }
    }

    /**
     * @param PresetDefinition $definition
     * @param int|string $index
     * @return void
     * @throws MissingKeyAttributeInYamlFileException
     */
    private function validatePresetDefinition(array $definition, int|string $index): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $definition)) {
                throw new MissingKeyAttributeInYamlFileException(
                    sprintf(
                        'Missing key: %s in preset yaml file for role preset definition: %s',
                        $field,
                        (string) $index
                    )
                );
            }
        }
    }

    /**
     * @param PresetDefinition $presetDefinition
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidQueryException
     * @throws PermissionNotFoundException
     * @throws RoleNotFoundException
     * @throws UnknownObjectException
     */
    private function addPermissionsToRole(array $presetDefinition): void
    {
        $role = $this->findRole($presetDefinition['role']);

        $this->logger->log(
            LogLevel::INFO,
            sprintf('Import preset definition for role: %s', (string) $role->getRoleKey())
        );

        if ($this->isWildcardAll($presetDefinition['permissions'])) {
            $this->addAllPermissionsToRole($role);
            return;
        }

        $this->addSpecificPermissionsToRole($role, $presetDefinition['permissions']);
    }

    /**
     * @param PresetDefinition $presetDefinition
     * @return void
     * @throws IllegalObjectTypeException
     * @throws PermissionNotFoundException
     * @throws RoleNotFoundException
     * @throws UnknownObjectException
     */
    private function removePermissionsFromRole(array $presetDefinition): void
    {
        $role = $this->findRole($presetDefinition['role']);

        $this->logger->log(
            LogLevel::INFO,
            sprintf('Remove permissions from the role according to the preset definition: %s', (string) $role->getRoleKey())
        );

        if ($this->isWildcardAll($presetDefinition['permissions'])) {
            return;
        }

        $this->removeUnspecifiedPermissions($role, $presetDefinition['permissions']);
    }

    /**
     * @param string $roleKey
     * @return Role
     * @throws RoleNotFoundException
     */
    private function findRole(string $roleKey): Role
    {
        $role = $this->roleRepository->findOneBy(['role_key' => $roleKey]);

        if (!$role instanceof Role) {
            throw new RoleNotFoundException(sprintf('Role not found: %s', $roleKey));
        }

        return $role;
    }

    /**
     * @param array<int, string> $permissions
     * @return bool
     */
    private function isWildcardAll(array $permissions): bool
    {
        return in_array(self::WILDCARD_ALL, $permissions, true);
    }

    /**
     * @param Role $role
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    private function addAllPermissionsToRole(Role $role): void
    {
        $permissions = $this->permissionRepository->findAll();

        foreach ($permissions as $permission) {
            if (!$permission instanceof Permission) {
                continue;
            }
            $role->addPermission($permission);
        }

        $this->saveRole($role);
    }

    /**
     * @param Role $role
     * @param array<int, string> $permissionDefinitions
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidQueryException
     * @throws PermissionNotFoundException
     * @throws UnknownObjectException
     */
    private function addSpecificPermissionsToRole(Role $role, array $permissionDefinitions): void
    {
        foreach ($permissionDefinitions as $permissionDefinition) {
            if ($this->isWildcardPattern($permissionDefinition)) {
                $this->addPermissionsByPrefix($role, $permissionDefinition);
            } else {
                $this->addSinglePermission($role, $permissionDefinition);
            }
        }

        $this->saveRole($role);
    }

    private function isWildcardPattern(string $permissionDefinition): bool
    {
        return str_contains($permissionDefinition, self::WILDCARD_ALL);
    }

    /**
     * @param Role $role
     * @param string $permissionPattern
     * @return void
     * @throws InvalidQueryException
     */
    private function addPermissionsByPrefix(Role $role, string $permissionPattern): void
    {
        $permissionPrefix = str_replace(self::WILDCARD_ALL, '', $permissionPattern);
        $permissions = $this->permissionRepository->findByPermissionPrefix($permissionPrefix);

        foreach ($permissions as $permission) {
            if (!$permission instanceof Permission) {
                continue;
            }
            $role->addPermission($permission);
        }
    }

    /**
     * @param Role $role
     * @param string $permissionKey
     * @return void
     * @throws PermissionNotFoundException
     */
    private function addSinglePermission(Role $role, string $permissionKey): void
    {
        $permission = $this->findPermission($permissionKey);
        $role->addPermission($permission);
    }

    /**
     * @param string $permissionKey
     * @return Permission
     * @throws PermissionNotFoundException
     */
    private function findPermission(string $permissionKey): Permission
    {
        $permission = $this->permissionRepository->findOneBy(['permission_key' => $permissionKey]);

        if (!$permission instanceof Permission) {
            throw new PermissionNotFoundException(sprintf('Permission not found: %s', $permissionKey));
        }

        return $permission;
    }

    /**
     * @param Role $role
     * @param array<int, string> $allowedPermissions
     * @return void
     * @throws IllegalObjectTypeException
     * @throws PermissionNotFoundException
     * @throws UnknownObjectException
     */
    private function removeUnspecifiedPermissions(Role $role, array $allowedPermissions): void
    {
        $assignedPermissionKeys = $this->getAssignedPermissionKeys($role);
        $wildcardPrefixes = $this->extractWildcardPrefixes($allowedPermissions);
        $permissionsToRemove = $this->calculatePermissionsToRemove(
            $assignedPermissionKeys,
            $allowedPermissions,
            $wildcardPrefixes
        );

        foreach ($permissionsToRemove as $permissionKey) {
            $permission = $this->findPermission($permissionKey);
            $role->removePermission($permission);
        }

        if ($permissionsToRemove !== []) {
            $this->saveRole($role);
        }
    }

    /**
     * @param Role $role
     * @return array<int, string>
     */
    private function getAssignedPermissionKeys(Role $role): array
    {
        $assignedPermissionKeys = [];
        $permissions = $role->getPermissions();

        if ($permissions === null) {
            return $assignedPermissionKeys;
        }

        foreach ($permissions as $permission) {
            if (!$permission instanceof Permission) {
                continue;
            }
            $permissionKey = $permission->getPermissionKey();
            if ($permissionKey !== null) {
                $assignedPermissionKeys[] = $permissionKey;
            }
        }

        return $assignedPermissionKeys;
    }

    /**
     * @param array<int, string> $permissionDefinitions
     * @return array<int, string>
     */
    private function extractWildcardPrefixes(array $permissionDefinitions): array
    {
        $prefixes = [];

        foreach ($permissionDefinitions as $permissionKey) {
            if ($this->isWildcardPattern($permissionKey)) {
                $prefixes[] = str_replace(self::WILDCARD_ALL, '', $permissionKey);
            }
        }

        return $prefixes;
    }

    /**
     * @param array<int, string> $assignedPermissionKeys
     * @param array<int, string> $allowedPermissions
     * @param array<int, string> $wildcardPrefixes
     * @return array<int, string>
     */
    private function calculatePermissionsToRemove(
        array $assignedPermissionKeys,
        array $allowedPermissions,
        array $wildcardPrefixes
    ): array {
        $diff = array_diff($assignedPermissionKeys, $allowedPermissions);
        $permissionsToRemove = [];

        foreach ($diff as $permissionKey) {
            if (!$this->matchesAnyWildcardPrefix($permissionKey, $wildcardPrefixes)) {
                $permissionsToRemove[] = $permissionKey;
            }
        }

        return $permissionsToRemove;
    }

    /**
     * @param string $permissionKey
     * @param array<int, string> $wildcardPrefixes
     * @return bool
     */
    private function matchesAnyWildcardPrefix(string $permissionKey, array $wildcardPrefixes): bool
    {
        return array_any($wildcardPrefixes, fn($prefix) => str_contains($permissionKey, $prefix));
    }

    /**
     * @param Role $role
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    private function saveRole(Role $role): void
    {
        $this->roleRepository->update($role);
        $this->persistenceManager->persistAll();
    }
}