<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendGroup extends AbstractEntity
{
    /**
     * @var ?ObjectStorage<Role>
     */
    #[Lazy()]
    public ?ObjectStorage $roles = null;

    /**
     * @var ?ObjectStorage<Permission>
     */
    #[Lazy()]
    public ?ObjectStorage $permissionsDeny = null;

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
}
