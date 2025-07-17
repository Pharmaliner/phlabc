<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Role extends AbstractEntity
{
    protected string $name;
    protected string $description;
    protected bool $is_custom_role;

    /**
     * @var ?ObjectStorage<FrontendUser>
     */
    public ?ObjectStorage $frontendUsers = null;

    /**
     * @var ?ObjectStorage<Permission>
     */
    public ?ObjectStorage $permissions = null;

    /**
     * @var ?ObjectStorage<FrontendGroup>
     */
    public ?ObjectStorage $frontendGroups = null;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isIsCustomRole(): bool
    {
        return $this->is_custom_role;
    }

    /**
     * @param bool $is_custom_role
     * @return void
     */
    public function setIsCustomRole(bool $is_custom_role): void
    {
        $this->is_custom_role = $is_custom_role;
    }

    /**
     * @return ?ObjectStorage<FrontendUser>
     */
    public function getFrontendUsers(): ?ObjectStorage
    {
        return $this->frontendUsers;
    }

    /**
     * @param ?ObjectStorage<FrontendUser> $frontendUsers
     * @return void
     */
    public function setFrontendUsers(?ObjectStorage $frontendUsers): void
    {
        $this->frontendUsers = $frontendUsers;
    }

    /**
     * @param FrontendUser $frontendUser
     * @return void
     */
    public function addFrontendUser(FrontendUser $frontendUser): void
    {
        $this->frontendUsers?->attach($frontendUser);
    }

    /**
     * @param FrontendUser $frontendUser
     * @return void
     */
    public function removeFrontendUser(FrontendUser $frontendUser): void
    {
        $this->frontendUsers?->detach($frontendUser);
    }

    /**
     * @return ?ObjectStorage<Permission>
     */
    public function getPermissions(): ?ObjectStorage
    {
        return $this->permissions;
    }

    /**
     * @param ?ObjectStorage<Permission> $permissions
     * @return void
     */
    public function setPermissions(?ObjectStorage $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @param Permission $permission
     * @return void
     */
    public function addPermission(Permission $permission): void
    {
        $this->permissions?->attach($permission);
    }

    /**
     * @param Permission $permission
     * @return void
     */
    public function removePermission(Permission $permission): void
    {
        $this->permissions?->detach($permission);
    }

    /**
     * @return ?ObjectStorage<FrontendGroup>
     */
    public function getFrontendGroups(): ?ObjectStorage
    {
        return $this->frontendGroups;
    }

    /**
     * @param ?ObjectStorage<FrontendGroup> $frontendGroups
     * @return void
     */
    public function setFrontendGroups(?ObjectStorage $frontendGroups): void
    {
        $this->frontendGroups = $frontendGroups;
    }

    /**
     * @param FrontendGroup $frontendGroup
     * @return void
     */
    public function addFrontendGroup(FrontendGroup $frontendGroup): void
    {
        $this->frontendGroups?->attach($frontendGroup);
    }

    /**
     * @param FrontendGroup $frontendGroup
     * @return void
     */
    public function removeFrontendGroup(FrontendGroup $frontendGroup): void
    {
        $this->frontendGroups?->detach($frontendGroup);
    }
}