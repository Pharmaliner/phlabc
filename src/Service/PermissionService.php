<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Service;

use Pharmaline\PhlAbc\Domain\Model\Permission;
use Pharmaline\PhlAbc\Domain\Repository\PermissionRepository;
use Pharmaline\PhlAbc\Exception\MissingKeyAttributeInYamlFileException;
use Pharmaline\PhlAbc\Exception\MissingRoleKeyDefinitionInObjectException;
use Pharmaline\PhlAbc\Utility\StorageConfigurationUtility;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model\Category;

/**
 * @phpstan-type PermissionDefinition array{
 *     permission_key: string,
 *     title: string,
 *     description: string,
 *     title_translation_key?: string,
 *     description_translation_key?: string,
 *     category?: int
 * }
 */
class PermissionService
{
    /** @var array<int, string> */
    private const array REQUIRED_FIELDS = ['permission_key', 'title', 'description'];

    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly LoggerInterface $logger,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Typo3QuerySettings $typo3QuerySettings
    ) {
    }

    /**
     * @param array<array<PermissionDefinition>> $content
     * @return void
     * @throws MissingKeyAttributeInYamlFileException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function importIntoDatabase(array $content): void
    {
        $this->configureRepository();

        foreach ($content as $permissions) {
            $this->processPermissionDefinitions($permissions);
        }
    }

    /**
     * @return void
     */
    private function configureRepository(): void
    {
        $this->typo3QuerySettings->setRespectStoragePage(false);
        $this->permissionRepository->setDefaultQuerySettings($this->typo3QuerySettings);
    }

    /**
     * @param array<PermissionDefinition> $permissionDefinitions
     * @return void
     * @throws MissingKeyAttributeInYamlFileException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    private function processPermissionDefinitions(array $permissionDefinitions): void
    {
        foreach ($permissionDefinitions as $index => $yamlPermissionDefinition) {
            $this->validatePermissionDefinition($yamlPermissionDefinition, $index);
            $this->importPermission($yamlPermissionDefinition);
        }
    }

    /**
     * @param PermissionDefinition $definition
     * @param int|string $index
     * @return void
     * @throws MissingKeyAttributeInYamlFileException
     */
    private function validatePermissionDefinition(array $definition, int|string $index): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $definition)) {
                throw new MissingKeyAttributeInYamlFileException(
                    sprintf(
                        'Missing key: %s in permission yaml file for permission definition: %s',
                        $field,
                        (string) $index
                    )
                );
            }
        }
    }

    /**
     * @param PermissionDefinition $yamlDefinition
     * @return void
     * @throws IllegalObjectTypeException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws UnknownObjectException
     */
    private function importPermission(array $yamlDefinition): void
    {
        $permission = $this->findOrCreatePermission($yamlDefinition['permission_key']);
        $isNew = $permission->getUid() === null;

        $oldState = null;
        if (!$isNew) {
            $oldState = $this->capturePermissionState($permission);
        }

        $this->updatePermissionFromYaml($permission, $yamlDefinition);
        $this->validatePermission($permission);
        $this->savePermission($permission, $isNew);

        if ($isNew) {
            $this->logNewPermission($permission);
        } else {
            assert($oldState !== null);
            $this->logUpdatedPermission($permission, $oldState);
        }
    }

    /**
     * @param string $permissionKey
     * @return Permission
     */
    private function findOrCreatePermission(string $permissionKey): Permission
    {
        $permission = $this->permissionRepository->findOneBy(['permission_key' => $permissionKey]);

        if ($permission instanceof Permission) {
            return $permission;
        }

        $newPermission = new Permission();
        $newPermission->setPermissionKey($permissionKey);
        $newPermission->setPid(StorageConfigurationUtility::getPermissionStoragePid());

        return $newPermission;
    }

    /**
     * @param Permission $permission
     * @param PermissionDefinition $yamlDefinition
     * @return void
     */
    private function updatePermissionFromYaml(Permission $permission, array $yamlDefinition): void
    {
        $permission->setTitle($yamlDefinition['title']);
        $permission->setDescription($yamlDefinition['description']);

        if (isset($yamlDefinition['title_translation_key'])) {
            $permission->setTitleTranslationKey($yamlDefinition['title_translation_key']);
        }

        if (isset($yamlDefinition['description_translation_key'])) {
            $permission->setDescriptionTranslationKey($yamlDefinition['description_translation_key']);
        }

        $this->updateCategoryFromYaml($permission, $yamlDefinition);
    }

    /**
     * @param Permission $permission
     * @param PermissionDefinition $yamlDefinition
     * @return void
     */
    private function updateCategoryFromYaml(Permission $permission, array $yamlDefinition): void
    {
        if (!isset($yamlDefinition['category'])) {
            return;
        }

        $categoryUid = $yamlDefinition['category'];

        if ($categoryUid <= 0) {
            $this->logger->warning(
                sprintf(
                    'Invalid category UID for permission %s: %d',
                    (string) $permission->getPermissionKey(),
                    $categoryUid
                )
            );
            return;
        }

        $category = $this->persistenceManager->getObjectByIdentifier(
            $categoryUid,
            Category::class
        );

        if (!$category instanceof Category) {
            $this->logger->warning(
                sprintf(
                    'Category with UID %d not found for permission %s',
                    $categoryUid,
                    (string) $permission->getPermissionKey()
                )
            );
            return;
        }

        $permission->addCategory($category);
    }

    /**
     * @param Permission $permission
     * @return void
     * @throws MissingRoleKeyDefinitionInObjectException
     */
    private function validatePermission(Permission $permission): void
    {
        $permissionKey = $permission->getPermissionKey();
        if (empty($permissionKey)) {
            throw new MissingRoleKeyDefinitionInObjectException(
                'Missing role key in object. Please check your definitions.'
            );
        }
    }

    /**
     * @param Permission $permission
     * @param bool $isNew
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    private function savePermission(Permission $permission, bool $isNew): void
    {
        if ($isNew) {
            $this->permissionRepository->add($permission);
        } else {
            $this->permissionRepository->update($permission);
        }

        $this->persistenceManager->persistAll();
    }

    /**
     * @param Permission $permission
     * @return array<string, string|null>
     */
    private function capturePermissionState(Permission $permission): array
    {
        return [
            'title' => $permission->getTitle(),
            'description' => $permission->getDescription(),
            'permission_key' => $permission->getPermissionKey(),
        ];
    }

    /**
     * @param Permission $permission
     * @return void
     */
    private function logNewPermission(Permission $permission): void
    {
        $this->logger->log(
            LogLevel::INFO,
            sprintf('Add new permission %s', (string) $permission->getPermissionKey()),
            ['permission' => $this->capturePermissionState($permission)]
        );
    }

    /**
     * @param Permission $permission
     * @param array<string, string|null> $oldState
     * @return void
     */
    private function logUpdatedPermission(Permission $permission, array $oldState): void
    {
        $this->logger->log(
            LogLevel::INFO,
            sprintf('Update permission %s', (string) $permission->getPermissionKey()),
            [
                'old_permission' => $oldState,
                'new_permission' => $this->capturePermissionState($permission),
            ]
        );
    }
}