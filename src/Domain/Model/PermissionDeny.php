<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class PermissionDeny extends AbstractEntity
{
    /**
     * @var ?ObjectStorage<FrontendUser>
     */
    #[Lazy()]
    public ?ObjectStorage $frontendUsers = null;

    /**
     * @var ?ObjectStorage<Role>
     */
    #[Lazy()]
    public ?ObjectStorage $roles = null;

    /**
     * @var ?ObjectStorage<FrontendGroup>
     */
    #[Lazy()]
    public ?ObjectStorage $frontendGroups = null;

    protected string $permission_key;

    public function getFrontendUsers(): ?ObjectStorage
    {
        return $this->frontendUsers;
    }

    public function setFrontendUsers(?ObjectStorage $frontendUsers): void
    {
        $this->frontendUsers = $frontendUsers;
    }

    public function getRoles(): ?ObjectStorage
    {
        return $this->roles;
    }

    public function setRoles(?ObjectStorage $roles): void
    {
        $this->roles = $roles;
    }

    public function getFrontendGroups(): ?ObjectStorage
    {
        return $this->frontendGroups;
    }

    public function setFrontendGroups(?ObjectStorage $frontendGroups): void
    {
        $this->frontendGroups = $frontendGroups;
    }

    public function getPermissionKey(): string
    {
        return $this->permission_key;
    }

    public function setPermissionKey(string $permission_key): void
    {
        $this->permission_key = $permission_key;
    }
}
