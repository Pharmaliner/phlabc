<?php

namespace Pharmaline\PhlAbc\Export;

/**
 * @phpstan-type PermissionRow array{key: string, title: string, roles: array<string, bool>}
 */
final readonly class PermissionMatrix
{
    /**
     * @param list<string> $roleKeys
     * @param list<PermissionRow> $rows
     */
    public function __construct(
        public array $roleKeys,
        public array $rows,
    ) {
    }
}
