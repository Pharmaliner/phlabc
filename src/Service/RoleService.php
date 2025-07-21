<?php

namespace Pharmaline\PhlAbc\Service;

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

class RoleService
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly LoggerInterface $logger,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Typo3QuerySettings $typo3QuerySettings,
    )
    {
    }

    /**
     * @param array $content
     * @return void
     * @throws IllegalObjectTypeException
     * @throws MissingKeyAttributeInYamlFileException
     * @throws MissingRoleKeyDefinitionInObjectException
     * @throws UnknownObjectException
     */
    public function importIntoDatabase(array $content): void
    {
        $this->typo3QuerySettings->setRespectStoragePage(false);
        $this->roleRepository->setDefaultQuerySettings($this->typo3QuerySettings);

        foreach ($content as $roles) {
            foreach ($roles as $index => $yamlRoleDefinition) {
                if (array_key_exists('role_key', $yamlRoleDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: role_key in role yaml file for role definition: ' . $index
                    );
                }

                if (array_key_exists('title', $yamlRoleDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: title in role yaml file for role definition: ' . $index
                    );
                }

                if (array_key_exists('description', $yamlRoleDefinition) === false) {
                    throw new MissingKeyAttributeInYamlFileException(
                        'Missing key: description in role yaml file for role definition: ' . $index
                    );
                }

                $role = $this->roleRepository->findOneBy(
                    [
                        'role_key' => $yamlRoleDefinition['role_key'],
                        'is_custom_role' => false
                    ]
                );

                if ($role instanceof Role) {
                    $oldRoleLogContext = [
                        'title' => $role->getTitle(),
                        'description' => $role->getDescription(),
                        'role_key' => $role->getRoleKey(),
                    ];

                    $role->setDescription($yamlRoleDefinition['description']);
                    $role->setTitle($yamlRoleDefinition['title']);

                    if (empty($role->getRoleKey())) {
                        throw new MissingRoleKeyDefinitionInObjectException(
                            'Missing role key in object. Please check your definitions.'
                        );
                    }

                    $this->roleRepository->update($role);

                    $newRoleLogContext = [
                        'title' => $role->getTitle(),
                        'description' => $role->getDescription(),
                        'role_key' => $role->getRoleKey(),
                    ];

                    $this->logger->log(LogLevel::INFO, sprintf('Update role %s', $role->getRoleKey()),
                        [
                            'old_role' => $oldRoleLogContext,
                            'new_role' => $newRoleLogContext,
                        ]
                    );

                } else {
                    $role = new Role();

                    $role->setRoleKey($yamlRoleDefinition['role_key']);
                    $role->setTitle($yamlRoleDefinition['title']);
                    $role->setDescription($yamlRoleDefinition['description']);
                    $role->setIsCustomRole(false);
                    $role->setPid(StorageConfigurationUtility::getRoleStoragePid());

                    $this->roleRepository->add($role);
                    $this->logger->log(LogLevel::INFO, sprintf('Add new role %s', $role->getRoleKey()),
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
        }

        $this->persistenceManager->persistAll();
    }
}