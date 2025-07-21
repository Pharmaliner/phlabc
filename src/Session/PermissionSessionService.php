<?php

namespace Pharmaline\PhlAbc\Session;

use Doctrine\DBAL\Exception;
use Pharmaline\PhlAbc\Domain\Repository\FrontendGroupRepository;
use Pharmaline\PhlAbc\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Session\Backend\DatabaseSessionBackend;
use TYPO3\CMS\Core\Session\Backend\RedisSessionBackend;
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PermissionSessionService
{
    /**
     * @var array<string>
     */
    private array $permissions = [];

    public function __construct(
        private readonly FrontendGroupRepository $frontendGroupRepository,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly SessionManager $sessionManager,
    )
    {
    }

    /**
     * @param array $ids
     * @return void
     * @throws Exception
     */
    public function refresh(array $ids = []): void
    {
        $feSessionBackend = $this->sessionManager->getSessionBackend('FE');

        if ($feSessionBackend instanceof DatabaseSessionBackend) {
            $this->refreshPermissionsForDatabaseBackend($ids);
        }

        if ($feSessionBackend instanceof RedisSessionBackend) {
            // @TODO
        }
    }

    /**
     * @param array $ids
     * @return void
     * @throws Exception
     */
    private function refreshPermissionsForDatabaseBackend(array $ids = []): void
    {
        $sessionTable = 'fe_sessions';

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($sessionTable);
        $queryBuild = $connection->createQueryBuilder();
        $queryBuild->select('*')->from($sessionTable);

        if (empty($ids) === false) {
            $queryBuild->where('ses_userid IN (:ids)')->setParameter('ids', $ids);
        }

        $sessions = $queryBuild->executeQuery()->fetchAllAssociative();

        foreach ($sessions as $sessionData) {
            $user = $this->frontendUserRepository->findByUid($sessionData['ses_userid']);

            if (empty($sessionData['ses_data'])) {
                continue;
            }

            // @TODO Frontend Group missing

            $usergroups = $user->getUsergroup()->toArray();
            if (empty($usergroups) === false) {
                foreach ($usergroups as $usergroup) {
                    $this->extractPermissions($usergroup->getRoles()?->toArray());
                }
            }

            $this->extractPermissions($user->getRoles()?->toArray());

            $data = unserialize($sessionData['ses_data']);

            unset($data['permissions']);
            $data['permissions'] = $this->permissions;

            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder->update($sessionTable)
                ->set($sessionTable . '.ses_data', serialize($data))
                ->where('ses_userid = :id')->setParameter('id', $sessionData['ses_userid'])
            ;
            $queryBuilder->executeQuery();
        }
    }

    /**
     * @param AbstractUserAuthentication $sessionUser
     * @return void
     */
    public function setPermissions(AbstractUserAuthentication $sessionUser): void
    {
        $user = $this->frontendUserRepository->findByUid($sessionUser->getUserId());

        $frontendUserGroups = $sessionUser->userGroups;
        foreach ($frontendUserGroups as $frontendUserGroupItem) {
            $frontendUserGroup = $this->frontendGroupRepository->findByUid($frontendUserGroupItem['uid']);

            if ($frontendUserGroup) {
                $this->extractPermissions($frontendUserGroup->getRoles()?->toArray());
            }
        }

        $this->extractPermissions($user->getRoles()?->toArray());
        $sessionUser->setSessionData('permissions', $this->permissions);
    }

    /**
     * @param array $roles
     * @return void
     */
    private function extractPermissions(
        array $roles
    ): void
    {
        foreach ($roles as $role) {
            $rolePermissions = $role->getPermissions()->toArray();
            foreach($rolePermissions as $rolePermission) {
                $this->permissions[] = $rolePermission->getPermissionKey();
            }
        }
    }
}