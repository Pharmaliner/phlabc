<?php

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

class PermissionService
{
    public function __construct(
        private readonly PermissionRepository $permissionRepository,
        private readonly LoggerInterface $logger,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Typo3QuerySettings $typo3QuerySettings
    )
    {
    }

    /**
     * @TODO clean up code its a bit messy
     * @param array $content
     * @return void
     * @throws MissingKeyAttributeInYamlFileException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function importIntoDatabase(array $content): void
    {
        $this->typo3QuerySettings->setRespectStoragePage(false);
        $this->permissionRepository->setDefaultQuerySettings($this->typo3QuerySettings);

        foreach ($content as $roles) {
            foreach ($roles as $index => $yamlPermissionDefinition) {
                if (array_key_exists('permission_key', $yamlPermissionDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: permission_key in permission yaml file for permission definition: ' . $index
                    );
                }

                if (array_key_exists('title', $yamlPermissionDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: title in permission yaml file for permission definition: ' . $index
                    );
                }

                if (array_key_exists('description', $yamlPermissionDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: description in permission yaml file for permission definition: ' . $index
                    );
                }

                $permission = $this->permissionRepository->findOneBy(
                    [
                        'permission_key' => $yamlPermissionDefinition['permission_key']
                    ]
                );

                if ($permission instanceof Permission) {
                    $oldPermissionLogContext = [
                        'title' => $permission->getTitle(),
                        'description' => $permission->getDescription(),
                        'permission_key' => $permission->getPermissionKey(),
                    ];

                    $permission->setDescription($yamlPermissionDefinition['description']);
                    $permission->setTitle($yamlPermissionDefinition['title']);

                    if (empty($permission->getPermissionKey())) {
                        throw new MissingRoleKeyDefinitionInObjectException(
                            'Missing role key in object. Please check your definitions.'
                        );
                    }

                    $this->permissionRepository->update($permission);

                    $newPermissionLogContext = [
                        'title' => $permission->getTitle(),
                        'description' => $permission->getDescription(),
                        'permission_key' => $permission->getPermissionKey(),
                    ];

                    $this->logger->log(LogLevel::INFO, sprintf('Update permission %s', $permission->getPermissionKey()),
                        [
                            'old_permission' => $oldPermissionLogContext,
                            'new_permission' => $newPermissionLogContext,
                        ]
                    );

                } else {
                    $permission = new Permission();

                    $permission->setPermissionKey($yamlPermissionDefinition['permission_key']);
                    $permission->setTitle($yamlPermissionDefinition['title']);
                    $permission->setDescription($yamlPermissionDefinition['description']);
                    $permission->setPid(StorageConfigurationUtility::getPermissionStoragePid());

                    $this->permissionRepository->add($permission);
                    $this->logger->log(LogLevel::INFO, sprintf('Add new permission %s', $permission->getPermissionKey()),
                        [
                            'permission' => [
                                'title' => $permission->getTitle(),
                                'description' => $permission->getDescription(),
                                'permission_key' => $permission->getPermissionKey(),
                            ],
                        ]
                    );
                }
            }
        }

        $this->persistenceManager->persistAll();
    }
}