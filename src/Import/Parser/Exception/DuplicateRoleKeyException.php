<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * The same role key appears in two header columns. Last-write-wins would
 * silently drop one role's data for every row, so this is rejected.
 * $firstColumn and $secondColumn are 1-based CSV column positions.
 */
final class DuplicateRoleKeyException extends PermissionMatrixParseException
{
    public function __construct(
        public readonly string $roleKey,
        public readonly int $firstColumn,
        public readonly int $secondColumn,
    ) {
        parent::__construct(sprintf(
            'Duplicate role key "%s" in header (columns %d and %d).',
            $roleKey,
            $firstColumn,
            $secondColumn,
        ));
    }
}
