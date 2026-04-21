<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUser extends AbstractEntity
{
    /**
     * @var ?ObjectStorage<Role>
     */
    #[Lazy()]
    public ?ObjectStorage $roles = null;

    /**
     * @var ?ObjectStorage<FrontendGroup>
     */
    #[Lazy()]
    public ?ObjectStorage $usergroup = null;

    /**
     * @var ?ObjectStorage<Permission>
     */
    #[Lazy()]
    public ?ObjectStorage $permissions = null;

    /**
     * @var ?ObjectStorage<Permission>
     */
    #[Lazy()]
    public ?ObjectStorage $permissionsDeny = null;

    /**
     * @var FrontendUser|null
     */
    #[Lazy()]
    protected ?FrontendUser $parentFrontendUser = null;

    /**
     * @return ?ObjectStorage<Role>
     */
    public function getRoles(): ?ObjectStorage
    {
        return $this->roles;
    }

    /**
     * @param ?ObjectStorage<Role> $roles
     */
    public function setRoles(?ObjectStorage $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param Role $role
     */
    public function addRole(Role $role): void
    {
        $this->roles?->attach($role);
    }

    /**
     * @param Role $role
     */
    public function removeRole(Role $role): void
    {
        $this->roles?->detach($role);
    }

    /**
     * @return ?ObjectStorage<FrontendGroup>
     */
    public function getUsergroup(): ?ObjectStorage
    {
        return $this->usergroup;
    }

    /**
     * @param ?ObjectStorage<FrontendGroup> $usergroups
     */
    public function setUsergroup(?ObjectStorage $usergroups): void
    {
        $this->usergroup = $usergroups;
    }

    public function getPermissionsDeny(): ?ObjectStorage
    {
        return $this->permissionsDeny;
    }

    public function setPermissionsDeny(?ObjectStorage $permissionsDeny): void
    {
        $this->permissionsDeny = $permissionsDeny;
    }

    /**
     * @param Permission $permission
     */
    public function addPermissionDeny(Permission $permission): void
    {
        $this->permissionsDeny?->attach($permission);
    }

    /**
     * @param Permission $permission
     */
    public function removePermissionDeny(Permission $permission): void
    {
        $this->permissionsDeny?->detach($permission);
    }

    public function getParentFrontendUser(): ?FrontendUser
    {
        return $this->parentFrontendUser;
    }

    public function setParentFrontendUser(?FrontendUser $parentFrontendUser): void
    {
        $this->parentFrontendUser = $parentFrontendUser;
    }

    public function getPermissions(): ?ObjectStorage
    {
        return $this->permissions;
    }

    public function setPermissions(?ObjectStorage $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @param Permission $permission
     */
    public function addPermission(Permission $permission): void
    {
        $this->permissions?->attach($permission);
    }

    /**
     * @param Permission $permission
     */
    public function removePermission(Permission $permission): void
    {
        $this->permissions?->detach($permission);
    }
}
