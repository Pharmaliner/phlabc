<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendGroup extends AbstractEntity
{
    /**
     * @var ?ObjectStorage<Role>
     */
    public ?ObjectStorage $roles = null;

    /**
     * @return ?ObjectStorage<Role>
     */
    public function getRoles(): ?ObjectStorage
    {
        return $this->roles;
    }

    /**
     * @param ?ObjectStorage<Role> $roles
     * @return void
     */
    public function setRoles(?ObjectStorage $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param Role $role
     * @return void
     */
    public function addRole(Role $role): void
    {
        $this->roles?->attach($role);
    }

    /**
     * @param Role $role
     * @return void
     */
    public function removeRole(Role $role): void
    {
        $this->roles?->detach($role);
    }
}