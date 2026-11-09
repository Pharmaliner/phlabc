<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * Format-neutral structural failure at the document/table level: empty content
 */
class MalformedInputException extends PermissionMatrixParseException
{
    public static function emptyContent(): self
    {
        return new self('Input content is empty.');
    }
}
