<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * The same permission key appears in two data rows. For a permission matrix a
 * duplicated key can flip an access decision, and a later sort would make the
 * duplicates adjacent and easy to miss, so it is rejected before sorting.
 * $firstRow and $secondRow are 1-based record numbers (the header is row 1).
 */
final class DuplicatePermissionKeyException extends PermissionMatrixParseException
{
    public function __construct(
        public readonly string $permissionKey,
        public readonly int $firstRow,
        public readonly int $secondRow,
    ) {
        parent::__construct(sprintf(
            'Duplicate permission key "%s" (rows %d and %d).',
            $permissionKey,
            $firstRow,
            $secondRow,
        ));
    }
}
