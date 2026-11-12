<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

use RuntimeException;

/**
 * Base type for every failure raised while parsing a permission matrix.
 *
 * Extends RuntimeException so existing `catch (RuntimeException)` call sites keep
 * working, while a single `catch (PermissionMatrixParseException)` catches the
 * whole family. Every subtype carries the context an admin needs to repair the
 * source file (key, column, row, raw value) — never a bare message.
 */
abstract class PermissionMatrixParseException extends RuntimeException
{
}
