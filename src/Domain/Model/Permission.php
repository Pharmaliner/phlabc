<?php

namespace Pharmaline\PhlAbc\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Permission extends AbstractEntity
{
    protected string $permissionKey;
    protected string $title;
    protected string $description;

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
}