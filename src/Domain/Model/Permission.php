<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Permission extends AbstractEntity
{
    protected string $permissionKey;
    protected string $title;
    protected string $description;
    protected ?string $titleTranslationKey = '';
    protected ?string $descriptionTranslationKey = '';

    /**
     * @var ObjectStorage<Category>|null
     */
    protected ?ObjectStorage $categories = null;

    /**
     * @var ObjectStorage<Role>|null
     */
    protected ?ObjectStorage $roles = null;

    /**
     * @var ObjectStorage<Role>|null
     */
    protected ?ObjectStorage $rolesDeny = null;

    public function __construct()
    {
        $this->categories = new ObjectStorage();
        $this->roles = new ObjectStorage();
        $this->rolesDeny = new ObjectStorage();
    }

    public function getPermissionKey(): string
    {
        return $this->permissionKey;
    }

    public function setPermissionKey(string $permissionKey): void
    {
        $this->permissionKey = $permissionKey;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTitleTranslationKey(): ?string
    {
        return $this->titleTranslationKey;
    }

    public function setTitleTranslationKey(?string $titleTranslationKey): void
    {
        $this->titleTranslationKey = $titleTranslationKey;
    }

    public function getDescriptionTranslationKey(): ?string
    {
        return $this->descriptionTranslationKey;
    }

    public function setDescriptionTranslationKey(?string $descriptionTranslationKey): void
    {
        $this->descriptionTranslationKey = $descriptionTranslationKey;
    }

    /**
     * @return ObjectStorage<Category>|null
     */
    public function getCategories(): ?ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param ObjectStorage<Category>|null $categories
     */
    public function setCategories(?ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @param Category $category
     * @return void
     */
    public function addCategory(Category $category): void
    {
        if ($this->categories?->contains($category) === false) {
            $this->categories?->attach($category);
        }
    }

    /**
     * @param Category $category
     * @return void
     */
    public function removeCategory(Category $category): void
    {
        if ($this->categories?->contains($category)) {
            $this->categories?->detach($category);
        }
    }

    public function getRoles(): ?ObjectStorage
    {
        return $this->roles;
    }

    public function setRoles(?ObjectStorage $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(Role $role): void
    {
        $this->roles?->attach($role);
    }

    public function removeRole(Role $role): void
    {
        $this->roles?->detach($role);
    }

    public function getRolesDeny(): ?ObjectStorage
    {
        return $this->rolesDeny;
    }

    public function setRolesDeny(?ObjectStorage $rolesDeny): void
    {
        $this->rolesDeny = $rolesDeny;
    }

    public function addRoleDeny(Role $role): void
    {
        $this->rolesDeny?->attach($role);
    }

    public function removeRoleDeny(Role $role): void
    {
        $this->rolesDeny?->detach($role);
    }
}
