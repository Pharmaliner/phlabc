<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Service;

use Pharmaline\PhlAbc\Domain\Dto\OperationResult;
use Pharmaline\PhlAbc\Domain\Model\Role;
use Pharmaline\PhlAbc\Domain\Repository\RoleRepository;
use Pharmaline\PhlAbc\Exception\MissingKeyAttributeInYamlFileException;
use Pharmaline\PhlAbc\Exception\MissingRoleKeyDefinitionInObjectException;
use Pharmaline\PhlAbc\Utility\StorageConfigurationUtility;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * @phpstan-type RoleDefinition array{
 *     role_key: string,
 *     title: string,
 *     description: string,
 *     title_translation_key?: string,
 *     description_translation_key?: string
 * }
 */
class RoleService
{
    /** @var array<int, string> */
    private const array REQUIRED_FIELDS = ['role_key', 'title', 'description'];

    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly LoggerInterface $logger,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Typo3QuerySettings $typo3QuerySettings,
    ) {
    }

    /**
     * @param array<array<RoleDefinition>> $content
     * @throws IllegalObjectTypeException
     * @throws MissingKeyAttributeInYamlFileException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws UnknownObjectException
     */
    public function importIntoDatabase(array $content): void
    {
        $this->configureRepository();

        foreach ($content as $roles) {
            $this->processRoleDefinitions($roles);
        }
    }

    /**
     * Identifies and removes obsolete roles from the database.
     *
     * Any role in the database not present in $validRoleKeys is considered obsolete.
     *
     * - If $dryRun is true: Returns the list of obsolete roles without modifying the database.
     * - If $dryRun is false: Soft-deletes the obsolete roles and persists the changes.
     *
     * @param array<int, string> $validRoleKeys List of valid role keys defined in YAML config file(s).
     * @param bool $dryRun If true, only reports what would be deleted without executing.
     *
     * @return OperationResult Contains the list of removed/removed-to-be role keys.
     */
    public function cleanupObsoleteRoles(array $validRoleKeys, bool $dryRun = true): OperationResult
    {
        $this->configureRepository();

        $result = new OperationResult();

        // Find all non-deleted roles in DB
        $allRoles = $this->roleRepository->findAll();

        foreach ($allRoles as $role) {
            if (!in_array($role->getRoleKey(), $validRoleKeys, true)) {
                $this->logger->log(
                    LogLevel::INFO,
                    sprintf('Remove obsolete role %s', $role->getRoleKey()),
                    ['role' => $this->captureRoleState($role)]
                );

                $result->recordRemoved($role->getRoleKey());

                if (!$dryRun) {
                    $this->roleRepository->remove($role);
                    $this->logRemovedRole($role);
                }
            }
        }

        if (!$dryRun) {
            $this->persistenceManager->persistAll();
        }

        return $result;
    }

    private function configureRepository(): void
    {
        $this->typo3QuerySettings->setRespectStoragePage(false);
        $this->roleRepository->setDefaultQuerySettings($this->typo3QuerySettings);
    }

    /**
     * @param array<RoleDefinition> $roleDefinitions
     * @throws IllegalObjectTypeException
     * @throws MissingKeyAttributeInYamlFileException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws UnknownObjectException
     */
    private function processRoleDefinitions(array $roleDefinitions): void
    {
        foreach ($roleDefinitions as $index => $yamlRoleDefinition) {
            $this->validateRoleDefinition($yamlRoleDefinition, $index);
            $this->importRole($yamlRoleDefinition);
        }
    }

    /**
     * @param RoleDefinition $definition
     * @param int|string $index
     * @throws MissingKeyAttributeInYamlFileException
     */
    private function validateRoleDefinition(array $definition, int|string $index): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $definition)) {
                throw new MissingKeyAttributeInYamlFileException(
                    sprintf(
                        'Missing key: %s in role yaml file for role definition: %s',
                        $field,
                        (string)$index
                    )
                );
            }
        }
    }

    /**
     * @param RoleDefinition $yamlDefinition
     * @throws IllegalObjectTypeException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws UnknownObjectException
     */
    private function importRole(array $yamlDefinition): void
    {
        $role = $this->findOrCreateRole($yamlDefinition['role_key']);
        $isNew = $role->getUid() === null;

        $oldState = null;
        if (!$isNew) {
            $oldState = $this->captureRoleState($role);
        }

        $this->updateRoleFromYaml($role, $yamlDefinition);
        $this->validateRole($role);
        $this->saveRole($role, $isNew);

        if ($isNew) {
            $this->logNewRole($role);
        } else {
            assert($oldState !== null);
            $this->logUpdatedRole($role, $oldState);
        }
    }

    /**
     * @param string $roleKey
     * @return Role
     */
    private function findOrCreateRole(string $roleKey): Role
    {
        $role = $this->findRoleByKey($roleKey);

        if ($role instanceof Role) {
            return $role;
        }

        return $this->createNewRole($roleKey);
    }

    /**
     * @param string $roleKey
     * @return Role|null
     */
    private function findRoleByKey(string $roleKey): ?Role
    {
        $roles = $this->roleRepository->findBy(['role_key' => $roleKey]);

        if (is_iterable($roles)) {
            return $roles[0];
        }

        return null;
    }

    /**
     * @param string $roleKey
     * @return Role
     */
    private function createNewRole(string $roleKey): Role
    {
        $role = new Role();
        $role->setRoleKey($roleKey);
        $role->setCustomRole(null);
        $role->setPid(StorageConfigurationUtility::getRoleStoragePid());

        return $role;
    }

    /**
     * @param Role $role
     * @param RoleDefinition $yamlDefinition
     */
    private function updateRoleFromYaml(Role $role, array $yamlDefinition): void
    {
        $role->setTitle($yamlDefinition['title']);
        $role->setDescription($yamlDefinition['description']);

        if (isset($yamlDefinition['title_translation_key'])) {
            $role->setTitleTranslationKey($yamlDefinition['title_translation_key']);
        }

        if (isset($yamlDefinition['description_translation_key'])) {
            $role->setDescriptionTranslationKey($yamlDefinition['description_translation_key']);
        }
    }

    /**
     * @param Role $role
     * @throws MissingRoleKeyDefinitionInObjectException
     */
    private function validateRole(Role $role): void
    {
        $roleKey = $role->getRoleKey();
        if (empty($roleKey)) {
            throw new MissingRoleKeyDefinitionInObjectException(
                'Missing role key in object. Please check your definitions.'
            );
        }
    }

    /**
     * @param Role $role
     * @param bool $isNew
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    private function saveRole(Role $role, bool $isNew): void
    {
        if ($isNew) {
            $this->roleRepository->add($role);
        } else {
            $this->roleRepository->update($role);
        }

        $this->persistenceManager->persistAll();
    }

    /**
     * @param Role $role
     * @return array<string, string|null>
     */
    private function captureRoleState(Role $role): array
    {
        return [
            'title' => $role->getTitle(),
            'description' => $role->getDescription(),
            'role_key' => $role->getRoleKey(),
            'title_translation_key' => $role->getTitleTranslationKey(),
            'description_translation_key' => $role->getDescriptionTranslationKey(),
        ];
    }

    /**
     * @param Role $role
     */
    private function logNewRole(Role $role): void
    {
        $this->logger->log(
            LogLevel::INFO,
            sprintf('Add new role %s', (string)$role->getRoleKey()),
            [
                'role' => [
                    'title' => $role->getTitle(),
                    'description' => $role->getDescription(),
                    'role_key' => $role->getRoleKey(),
                ],
            ]
        );
    }

    /**
     * @param Role $role
     * @param array<string, string|null> $oldState
     */
    private function logUpdatedRole(Role $role, array $oldState): void
    {
        $this->logger->log(
            LogLevel::INFO,
            sprintf('Update role %s', (string)$role->getRoleKey()),
            [
                'old_role' => $oldState,
                'new_role' => $this->captureRoleState($role),
            ]
        );
    }

    /**
     * @param Role $role
     */
    private function logRemovedRole(Role $role): void
    {
        $this->logger->log(
            LogLevel::INFO,
            sprintf('Remove role %s', (string)$role->getRoleKey()),
            [
                'role' => [
                    'title' => $role->getTitle(),
                    'description' => $role->getDescription(),
                    'role_key' => $role->getRoleKey(),
                ],
            ]
        );
    }
}
