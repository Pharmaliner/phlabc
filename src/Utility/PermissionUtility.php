<?php

namespace Pharmaline\PhlAbc\Utility;

use Pharmaline\PhlAbc\Domain\Model\FrontendUser;
use Pharmaline\PhlAbc\Domain\Model\FrontendGroup;
use Pharmaline\PhlAbc\Domain\Model\Permission;
use Pharmaline\PhlAbc\Domain\Model\Role;

/**
 * @phpstan-type AdditionalData array{disabled?: array<int, string>}
 */
class PermissionUtility
{
    /**
     * @param FrontendUser $frontendUser
     * @return array<int, string>
     */
    public static function extractPermissions(FrontendUser $frontendUser): array
    {
        $permissions = [];

        $permissions = self::addDirectUserPermissions($frontendUser, $permissions);
        $permissions = self::addUserRolePermissions($frontendUser, $permissions);
        $permissions = self::addUserGroupPermissions($frontendUser, $permissions);

        return array_values(array_unique($permissions));
    }

    /**
     * @param FrontendUser $user
     * @return array<int, string>
     */
    public static function extractPermissionsDeny(FrontendUser $user): array
    {
        $permissionsDeny = [];

        $permissionsDeny = self::addDirectUserDenyPermissions($user, $permissionsDeny);
        $permissionsDeny = self::addUserGroupDenyPermissions($user, $permissionsDeny);
        $permissionsDeny = self::addUserRoleDenyPermissions($user, $permissionsDeny);

        return array_values(array_unique($permissionsDeny));
    }

    /**
     * @param FrontendUser $frontendUser
     * @param array<int, string> $permissions
     * @return array<int, string>
     */
    private static function addDirectUserPermissions(FrontendUser $frontendUser, array $permissions): array
    {
        $frontendUserPermissions = $frontendUser->getPermissions();

        if ($frontendUserPermissions === null) {
            return $permissions;
        }

        foreach ($frontendUserPermissions->toArray() as $permission) {
            if (!$permission instanceof Permission) {
                continue;
            }

            $permissionKey = $permission->getPermissionKey();
            if ($permissionKey !== null) {
                $permissions[] = $permissionKey;
            }
        }

        return $permissions;
    }

    /**
     * @param FrontendUser $frontendUser
     * @param array<int, string> $permissions
     * @return array<int, string>
     */
    private static function addUserRolePermissions(FrontendUser $frontendUser, array $permissions): array
    {
        $frontendUserRoles = $frontendUser->getRoles();

        if ($frontendUserRoles === null) {
            return $permissions;
        }

        foreach ($frontendUserRoles as $role) {
            if (!$role instanceof Role) {
                continue;
            }
            $permissions = self::extractPermissionsFromRole($role, $permissions);
        }

        return $permissions;
    }

    /**
     * @param FrontendUser $frontendUser
     * @param array<int, string> $permissions
     * @return array<int, string>
     */
    private static function addUserGroupPermissions(FrontendUser $frontendUser, array $permissions): array
    {
        $frontendGroups = $frontendUser->getUsergroup();

        if ($frontendGroups === null) {
            return $permissions;
        }

        foreach ($frontendGroups as $frontendGroup) {
            if (!$frontendGroup instanceof FrontendGroup) {
                continue;
            }

            $permissions = self::extractPermissionsFromUserGroup($frontendGroup, $permissions);
        }

        return $permissions;
    }

    /**
     * @param FrontendGroup $frontendGroup
     * @param array<int, string> $permissions
     * @return array<int, string>
     */
    private static function extractPermissionsFromUserGroup(FrontendGroup $frontendGroup, array $permissions): array
    {
        $roles = $frontendGroup->getRoles();

        if ($roles === null) {
            return $permissions;
        }

        foreach ($roles as $role) {
            if (!$role instanceof Role) {
                continue;
            }
            $permissions = self::extractPermissionsFromRole($role, $permissions);
        }

        return $permissions;
    }

    /**
     * @param Role $role
     * @param array<int, string> $permissions
     * @return array<int, string>
     */
    private static function extractPermissionsFromRole(Role $role, array $permissions): array
    {
        $disabledPrefixes = self::getDisabledPrefixes($role);
        $rolePermissions = $role->getPermissions();

        if ($rolePermissions === null) {
            return $permissions;
        }

        foreach ($rolePermissions->toArray() as $rolePermission) {
            if (!$rolePermission instanceof Permission) {
                continue;
            }

            $permissionKey = $rolePermission->getPermissionKey();
            if ($permissionKey === null) {
                continue;
            }

            if (self::isPermissionDisabled($permissionKey, $disabledPrefixes)) {
                continue;
            }

            $permissions[] = $permissionKey;
        }

        return $permissions;
    }

    /**
     * @param Role $role
     * @return array<int, string>
     */
    private static function getDisabledPrefixes(Role $role): array
    {
        $additionalDataJson = $role->getAdditionalData();

        if (empty($additionalDataJson)) {
            return [];
        }

        /** @var AdditionalData|null $additionalData */
        $additionalData = json_decode($additionalDataJson, true);

        if (!is_array($additionalData) || !isset($additionalData['disabled'])) {
            return [];
        }

        return $additionalData['disabled'];
    }

    /**
     * @param string $permissionKey
     * @param array<int, string> $disabledPrefixes
     * @return bool
     */
    private static function isPermissionDisabled(string $permissionKey, array $disabledPrefixes): bool
    {
        return array_any($disabledPrefixes, fn($disabledPrefix) => str_starts_with($permissionKey, $disabledPrefix));
    }

    /**
     * @param FrontendUser $user
     * @param array<int, string> $permissionsDeny
     * @return array<int, string>
     */
    private static function addDirectUserDenyPermissions(FrontendUser $user, array $permissionsDeny): array
    {
        $userDenyPermissions = $user->getPermissionsDeny();

        if ($userDenyPermissions === null) {
            return $permissionsDeny;
        }

        foreach ($userDenyPermissions as $permissionDenyItem) {
            if (!$permissionDenyItem instanceof Permission) {
                continue;
            }

            $permissionKey = $permissionDenyItem->getPermissionKey();
            if ($permissionKey !== null) {
                $permissionsDeny[] = $permissionKey;
            }
        }

        return $permissionsDeny;
    }

    /**
     * @param FrontendUser $user
     * @param array<int, string> $permissionsDeny
     * @return array<int, string>
     */
    private static function addUserGroupDenyPermissions(FrontendUser $user, array $permissionsDeny): array
    {
        $userGroups = $user->getUsergroup();

        if ($userGroups === null) {
            return $permissionsDeny;
        }

        foreach ($userGroups as $usergroup) {
            if (!$usergroup instanceof FrontendGroup) {
                continue;
            }

            $permissionsDeny = self::extractDenyPermissionsFromUserGroup($usergroup, $permissionsDeny);
        }

        return $permissionsDeny;
    }

    /**
     * @param FrontendGroup $usergroup
     * @param array<int, string> $permissionsDeny
     * @return array<int, string>
     */
    private static function extractDenyPermissionsFromUserGroup(FrontendGroup $usergroup, array $permissionsDeny): array
    {
        // Direct deny permissions from usergroup
        $groupDenyPermissions = $usergroup->getPermissionsDeny();
        if ($groupDenyPermissions !== null) {
            foreach ($groupDenyPermissions as $permissionDenyItem) {
                if (!$permissionDenyItem instanceof Permission) {
                    continue;
                }

                $permissionKey = $permissionDenyItem->getPermissionKey();
                if ($permissionKey !== null) {
                    $permissionsDeny[] = $permissionKey;
                }
            }
        }

        // Deny permissions from usergroup roles
        $roles = $usergroup->getRoles();
        if ($roles !== null) {
            foreach ($roles as $role) {
                if (!$role instanceof Role) {
                    continue;
                }
                $permissionsDeny = self::extractDenyPermissionsFromRole($role, $permissionsDeny);
            }
        }

        return $permissionsDeny;
    }

    /**
     * @param FrontendUser $user
     * @param array<int, string> $permissionsDeny
     * @return array<int, string>
     */
    private static function addUserRoleDenyPermissions(FrontendUser $user, array $permissionsDeny): array
    {
        $userRoles = $user->getRoles();

        if ($userRoles === null) {
            return $permissionsDeny;
        }

        foreach ($userRoles as $role) {
            if (!$role instanceof Role) {
                continue;
            }
            $permissionsDeny = self::extractDenyPermissionsFromRole($role, $permissionsDeny);
        }

        return $permissionsDeny;
    }

    /**
     * @param Role $role
     * @param array<int, string> $permissionsDeny
     * @return array<int, string>
     */
    private static function extractDenyPermissionsFromRole(Role $role, array $permissionsDeny): array
    {
        $roleDenyPermissions = $role->getPermissionsDeny();

        if ($roleDenyPermissions === null) {
            return $permissionsDeny;
        }

        foreach ($roleDenyPermissions as $permissionDenyItem) {
            if (!$permissionDenyItem instanceof Permission) {
                continue;
            }

            $permissionKey = $permissionDenyItem->getPermissionKey();
            if ($permissionKey !== null) {
                $permissionsDeny[] = $permissionKey;
            }
        }

        return $permissionsDeny;
    }
}
