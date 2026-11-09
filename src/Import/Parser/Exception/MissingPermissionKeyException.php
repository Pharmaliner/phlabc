<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * A data row has the correct column count but a blank permission key (first
 * column). The row cannot be addressed and is rejected rather than dropped.
 * $row is the 1-based record number (the header is row 1).
 */
final class MissingPermissionKeyException extends PermissionMatrixParseException
{
    public function __construct(public readonly int $row)
    {
        parent::__construct(sprintf('Row %d has an empty permission key.', $row));
    }
}
