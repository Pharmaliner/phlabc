<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * A role cell holds a value that is neither a recognized true nor a recognised
 * false token. Coercing an unrecognized value to false would silently corrupt a
 * permission, so it is rejected. Carries everything needed to locate the cell.
 * $row is the 1-based record number (the header is row 1).
 */
final class UnknownBooleanValueException extends PermissionMatrixParseException
{
    public function __construct(
        public readonly string $value,
        public readonly string $permissionKey,
        public readonly string $roleKey,
        public readonly int $row,
    ) {
        parent::__construct(sprintf(
            'Unrecognized boolean value "%s" for role "%s" of permission "%s" (row %d).',
            $value,
            $roleKey,
            $permissionKey,
            $row,
        ));
    }
}
