<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Session;

use Doctrine\DBAL\Exception;
use Pharmaline\PhlAbc\Domain\Model\FrontendUser;
use Pharmaline\PhlAbc\Domain\Repository\FrontendUserRepository;
use Pharmaline\PhlAbc\Utility\PermissionUtility;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Session\Backend\DatabaseSessionBackend;
use TYPO3\CMS\Core\Session\Backend\Exception\SessionNotCreatedException;
use TYPO3\CMS\Core\Session\Backend\RedisSessionBackend;
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @phpstan-type SessionData array{ses_userid: int, ses_data: string, ses_id?: string}
 * @phpstan-type UserSessionData array<string, mixed>
 */
class PermissionSessionService
{
    private const SESSION_TABLE = 'fe_sessions';
    private const SESSION_CONTEXT = 'FE';
    private const PERMISSIONS_KEY = 'permissions';

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly SessionManager $sessionManager,
    ) {
    }

    /**
     * @param array<int, int> $userIds
     * @return void
     * @throws Exception
     * @throws SessionNotCreatedException
     */
    public function refresh(array $userIds = []): void
    {
        $sessionBackend = $this->sessionManager->getSessionBackend(self::SESSION_CONTEXT);

        if ($sessionBackend instanceof DatabaseSessionBackend) {
            $this->refreshPermissionsForDatabaseBackend($userIds);
            return;
        }

        if ($sessionBackend instanceof RedisSessionBackend) {
            $this->refreshPermissionsForRedisBackend($userIds);
            return;
        }
    }

    /**
     * @param AbstractUserAuthentication $sessionUser
     * @return void
     */
    public function setPermissions(AbstractUserAuthentication $sessionUser): void
    {
        $userId = $sessionUser->getUserId();

        if ($userId === null) {
            return;
        }

        $finalPermissions = $this->calculateUserPermissions($userId);
        $sessionUser->setSessionData(self::PERMISSIONS_KEY, $finalPermissions);
    }

    /**
     * @param int $frontendUserUid
     * @return array<int, string>
     */
    public function getPermissions(int $frontendUserUid): array
    {
        return $this->calculateUserPermissions($frontendUserUid);
    }

    /**
     * @param array<int, int> $userIds
     * @return void
     * @throws Exception
     */
    private function refreshPermissionsForDatabaseBackend(array $userIds = []): void
    {
        $sessions = $this->fetchDatabaseSessions($userIds);

        foreach ($sessions as $sessionData) {
            $this->updateDatabaseSession($sessionData);
        }
    }

    /**
     * @param array<int, int> $userIds
     * @return void
     * @throws SessionNotCreatedException
     */
    private function refreshPermissionsForRedisBackend(array $userIds = []): void
    {
        $sessionBackend = $this->sessionManager->getSessionBackend(self::SESSION_CONTEXT);

        if (!$sessionBackend instanceof RedisSessionBackend) {
            return;
        }

        $sessions = $this->fetchRedisSessions($sessionBackend, $userIds);

        foreach ($sessions as $sessionId => $sessionData) {
            $this->updateRedisSession($sessionBackend, $sessionId, $sessionData);
        }
    }

    /**
     * @param array<int, int> $userIds
     * @return array<int, SessionData>
     * @throws Exception
     */
    private function fetchDatabaseSessions(array $userIds): array
    {
        $connection = $this->getDatabaseConnection();
        $queryBuilder = $connection->createQueryBuilder();

        $queryBuilder->select('*')->from(self::SESSION_TABLE);

        if ($userIds !== []) {
            $queryBuilder
                ->where('ses_userid IN (:ids)')
                ->setParameter('ids', $userIds, Connection::PARAM_INT_ARRAY);
        }

        $result = $queryBuilder->executeQuery()->fetchAllAssociative();

        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @param RedisSessionBackend $sessionBackend
     * @param array<int, int> $userIds
     * @return array<string, UserSessionData>
     */
    private function fetchRedisSessions(RedisSessionBackend $sessionBackend, array $userIds): array
    {
        $allSessions = $sessionBackend->getAll();

        if ($userIds === []) {
            return $allSessions;
        }

        return $this->filterSessionsByUserIds($allSessions, $userIds);
    }

    /**
     * @param array<string, UserSessionData> $sessions
     * @param array<int, int> $userIds
     * @return array<string, UserSessionData>
     */
    private function filterSessionsByUserIds(array $sessions, array $userIds): array
    {
        $filtered = [];

        foreach ($sessions as $sessionId => $sessionData) {
            if (!is_array($sessionData)) {
                continue;
            }

            $userId = $sessionData['ses_userid'] ?? null;

            if ($userId !== null && in_array((int) $userId, $userIds, true)) {
                $filtered[$sessionId] = $sessionData;
            }
        }

        return $filtered;
    }

    /**
     * @param SessionData $sessionData
     * @return void
     */
    private function updateDatabaseSession(array $sessionData): void
    {
        $userId = (int) $sessionData['ses_userid'];
        $serializedData = $sessionData['ses_data'];

        if ($serializedData === '') {
            return;
        }

        $updatedData = $this->updateSessionPermissions($serializedData, $userId);

        if ($updatedData === null) {
            return;
        }

        $this->saveDatabaseSession($userId, $updatedData);
    }

    /**
     * @param RedisSessionBackend $sessionBackend
     * @param string $sessionId
     * @param UserSessionData $sessionData
     * @return void
     * @throws SessionNotCreatedException
     */
    private function updateRedisSession(
        RedisSessionBackend $sessionBackend,
        string $sessionId,
        array $sessionData
    ): void {
        if (!isset($sessionData['ses_userid'])) {
            return;
        }

        $userId = (int) $sessionData['ses_userid'];
        $finalPermissions = $this->calculateUserPermissions($userId);

        $sessionData[self::PERMISSIONS_KEY] = $finalPermissions;

        $sessionBackend->set($sessionId, $sessionData);
    }

    /**
     * @param string $serializedData
     * @param int $userId
     * @return string|null
     */
    private function updateSessionPermissions(string $serializedData, int $userId): ?string
    {
        $data = unserialize($serializedData);

        if (!is_array($data)) {
            return null;
        }

        $finalPermissions = $this->calculateUserPermissions($userId);

        unset($data[self::PERMISSIONS_KEY]);
        $data[self::PERMISSIONS_KEY] = $finalPermissions;

        return serialize($data);
    }

    /**
     * @param int $userId
     * @param string $serializedData
     * @return void
     */
    private function saveDatabaseSession(int $userId, string $serializedData): void
    {
        $connection = $this->getDatabaseConnection();
        $queryBuilder = $connection->createQueryBuilder();

        $queryBuilder
            ->update(self::SESSION_TABLE)
            ->set('ses_data', ':data')
            ->where('ses_userid = :id')
            ->setParameter('data', $serializedData)
            ->setParameter('id', $userId);

        $queryBuilder->executeQuery();
    }

    /**
     * @param int $userId
     * @return array<int, string>
     */
    private function calculateUserPermissions(int $userId): array
    {
        $user = $this->frontendUserRepository->findByUid($userId);

        if (!$user instanceof FrontendUser) {
            return [];
        }

        $permissions = PermissionUtility::extractPermissions($user);
        $permissionsDeny = PermissionUtility::extractPermissionsDeny($user);

        return array_values(array_diff($permissions, $permissionsDeny));
    }

    /**
     * @return Connection
     */
    private function getDatabaseConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::SESSION_TABLE);
    }
}