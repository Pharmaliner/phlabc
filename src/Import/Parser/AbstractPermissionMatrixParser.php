<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser;

use Pharmaline\PhlAbc\Import\Parser\Exception\UnknownBooleanValueException;

abstract class AbstractPermissionMatrixParser
{
    /** @var list<string> recognized true tokens (compared lower-cased, trimmed) */
    private const TRUE_TOKENS = ['1', 'true', 'yes', 'y', '✅'];

    /** @var list<string> recognized false tokens (compared lower-cased, trimmed) */
    private const FALSE_TOKENS = ['0', 'false', 'no', 'n', '❌', ''];

    /**
     * Strict boolean parsing. An unrecognized value is corruption in a permission
     * cell, so it is rejected rather than silently coerced to false. A present-but-
     * empty cell is a deliberate "not granted" and maps to false.
     *
     * @throws UnknownBooleanValueException
     */
    final protected function parseBoolean(string $value, string $permissionKey, string $roleKey, int $row): bool
    {
        $normalized = strtolower(trim($value));

        if (in_array($normalized, self::TRUE_TOKENS, true)) {
            return true;
        }
        if (in_array($normalized, self::FALSE_TOKENS, true)) {
            return false;
        }

        throw new UnknownBooleanValueException(trim($value), $permissionKey, $roleKey, $row);
    }
}
