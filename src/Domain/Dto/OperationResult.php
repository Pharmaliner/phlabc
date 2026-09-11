<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Domain\Dto;

class OperationResult
{
    /** @var list<string> */
    private array $added = [];

    /** @var list<string> */
    private array $updated = [];

    /** @var list<string> */
    private array $removed = [];

    public function recordAdded(string $key): void
    {
        $this->added[] = $key;
    }

    public function recordUpdated(string $key): void
    {
        $this->updated[] = $key;
    }

    public function recordRemoved(string $key): void
    {
        $this->removed[] = $key;
    }

    public function merge(self $other): void
    {
        $this->added = [...$this->added, ...$other->added];
        $this->updated = [...$this->updated, ...$other->updated];
        $this->removed = [...$this->removed, ...$other->removed];
    }

    /** @return list<string> */
    public function getAdded(): array
    {
        return $this->added;
    }

    /** @return list<string> */
    public function getUpdated(): array
    {
        return $this->updated;
    }

    /** @return list<string> */
    public function getRemoved(): array
    {
        return $this->removed;
    }

    public function hasChanges(): bool
    {
        return $this->added !== [] || $this->updated !== [] || $this->removed !== [];
    }
}
