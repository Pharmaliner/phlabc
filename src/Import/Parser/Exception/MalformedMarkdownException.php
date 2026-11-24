<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser\Exception;

/**
 * Format-neutral structural failure at the document/table level: empty content,
 * too few lines, a header with too few columns, no roles, or (Markdown) a missing
 * or invalid delimiter row.
 *
 * Shared by every parser. (The CSV parser's MalformedCsvException is to be folded
 * into this type in a later pass.)
 */
final class MalformedMarkdownException extends MalformedInputException
{
    public static function tooFewLines(): self
    {
        return new self(
            'A Markdown permission matrix needs a header row, a delimiter row, and at '
            . 'least one data row.',
        );
    }

    public static function headerTooFewColumns(int $found): self
    {
        return new self(sprintf(
            'Header must have at least 3 columns (Permission-Key, Permission-Title, and '
            . 'at least one role); found %d.',
            $found,
        ));
    }

    public static function invalidDelimiterRow(): self
    {
        return new self(
            'The second row must be a Markdown table delimiter (e.g. |---|---|---|).',
        );
    }

    public static function delimiterColumnMismatch(int $headerColumns, int $delimiterColumns): self
    {
        return new self(sprintf(
            'Delimiter row has %d column(s) but the header has %d.',
            $delimiterColumns,
            $headerColumns,
        ));
    }
}
