<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * An interior role column in the header is blank, e.g. "...,role_a,,role_c".
 *
 * Trailing empty columns are a tolerated spreadsheet artifact and are trimmed;
 * a blank *between* real role keys is ambiguous and rejected. $column is the
 * 1-based CSV column position of the offending empty cell.
 */
final class EmptyRoleKeyException extends PermissionMatrixParseException
{
    public function __construct(public readonly int $column)
    {
        parent::__construct(sprintf(
            'Empty role key in header at column %d. A blank column between role keys is '
            . 'ambiguous; remove it or give the role a name.',
            $column,
        ));
    }
}
