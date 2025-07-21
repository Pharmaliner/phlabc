<?php

namespace Pharmaline\PhlAbc\Domain\Repository;

use Pharmaline\PhlAbc\Utility\StorageConfigurationUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Repository;

class PermissionRepository extends Repository
{

    /**
     * @throws InvalidQueryException
     */
    public function findByPermissionPrefix(string $permissionPrefix): array
    {
        $query = $this->createQuery();
        $query->like('permission_key', $permissionPrefix.'%');
        $query->equals('pid', StorageConfigurationUtility::getPermissionStoragePid());

        return $query->execute()->toArray();
    }

}