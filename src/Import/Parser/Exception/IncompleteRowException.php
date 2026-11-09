<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * A data row has the wrong number of columns (too few or too many) for the
 * header's role count. Covers short rows, over-long rows, and junk lines.
 * A row must have exactly 2 + roleCount columns. $row is the 1-based record
 * number (the header is row 1).
 */
final class IncompleteRowException extends PermissionMatrixParseException
{
    public function __construct(
        public readonly int $row,
        public readonly int $expectedColumns,
        public readonly int $actualColumns,
        public readonly string $rawRow,
    ) {
        parent::__construct(sprintf(
            'Row %d has %d column(s) but %d were expected (2 fixed + %d roles). Raw: %s',
            $row,
            $actualColumns,
            $expectedColumns,
            $expectedColumns - 2,
            $rawRow,
        ));
    }
}
