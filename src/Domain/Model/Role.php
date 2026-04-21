<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Role extends AbstractEntity
{
    protected string $roleKey;
    protected string $title;
    protected string $description;
    protected ?int $customRole = null;
    protected string $titleTranslationKey = '';
    protected string $descriptionTranslationKey = '';

    /**
     * @var ?ObjectStorage<FrontendUser>
     */
    #[Lazy()]
    public ?ObjectStorage $frontendUsers = null;

    /**
     * @var ?ObjectStorage<Permission>
     */
    #[Lazy()]
    public ?ObjectStorage $permissions = null;

    /**
     * @var ?ObjectStorage<FrontendGroup>
     */
    #[Lazy()]
    public ?ObjectStorage $frontendGroups = null;

    /**
     * @var ?ObjectStorage<Permission>
     */
    #[Lazy()]
    public ?ObjectStorage $permissionsDeny = null;

    protected string $additionalData = '';

    public function __construct()
    {
        $this->permissions = new ObjectStorage();
        $this->permissionsDeny = new ObjectStorage();
        $this->frontendGroups = new ObjectStorage();
        $this->frontendUsers = new ObjectStorage();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCustomRole(): ?int
    {
        return $this->customRole;
    }

    public function setCustomRole(?int $customRole): void
    {
        $this->customRole = $customRole;
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
     */
    public function setFrontendUsers(?ObjectStorage $frontendUsers): void
    {
        $this->frontendUsers = $frontendUsers;
    }

    /**
     * @param FrontendUser $frontendUser
     */
    public function addFrontendUser(FrontendUser $frontendUser): void
    {
        $this->frontendUsers?->attach($frontendUser);
    }

    /**
     * @param FrontendUser $frontendUser
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
     */
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

    /**
     * @return ?ObjectStorage<FrontendGroup>
     */
    public function getFrontendGroups(): ?ObjectStorage
    {
        return $this->frontendGroups;
    }

    /**
     * @param ?ObjectStorage<FrontendGroup> $frontendGroups
     */
    public function setFrontendGroups(?ObjectStorage $frontendGroups): void
    {
        $this->frontendGroups = $frontendGroups;
    }

    /**
     * @param FrontendGroup $frontendGroup
     */
    public function addFrontendGroup(FrontendGroup $frontendGroup): void
    {
        $this->frontendGroups?->attach($frontendGroup);
    }

    /**
     * @param FrontendGroup $frontendGroup
     */
    public function removeFrontendGroup(FrontendGroup $frontendGroup): void
    {
        $this->frontendGroups?->detach($frontendGroup);
    }

    /**
     * @return string
     */
    public function getRoleKey(): string
    {
        return $this->roleKey;
    }

    /**
     * @param string $roleKey
     */
    public function setRoleKey(string $roleKey): void
    {
        $this->roleKey = $roleKey;
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

    public function getAdditionalData(): string
    {
        return $this->additionalData;
    }

    public function setAdditionalData(string $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    public function getTitleTranslationKey(): string
    {
        return $this->titleTranslationKey;
    }

    public function setTitleTranslationKey(string $titleTranslationKey): void
    {
        $this->titleTranslationKey = $titleTranslationKey;
    }

    public function getDescriptionTranslationKey(): string
    {
        return $this->descriptionTranslationKey;
    }

    public function setDescriptionTranslationKey(string $descriptionTranslationKey): void
    {
        $this->descriptionTranslationKey = $descriptionTranslationKey;
    }
}
