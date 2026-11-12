<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * Header-level structural failure: empty content, fewer than three columns, or
 * no role columns remaining after trailing-empty trimming.
 */
final class MalformedCsvException extends MalformedInputException
{
    public static function tooFewColumns(int $found): self
    {
        return new self(sprintf(
            'CSV header must have at least 3 columns (Permission-Key, Permission-Title, '
            . 'and at least one role); found %d.',
            $found,
        ));
    }

    public static function noRoles(): self
    {
        return new self('CSV header declares no role columns.');
    }
}
