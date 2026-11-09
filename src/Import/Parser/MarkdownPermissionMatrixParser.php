<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicatePermissionKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicateRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\EmptyRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\IncompleteRowException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MalformedMarkdownException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MissingPermissionKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\PermissionMatrixParseException;

/**
 * Parses markdown content into a PermissionMatrix.
 *
 * Expected format (see documentation for details):
 *
 *   | Permission-Key | Permission-Title | role_a | role_b |
 *   |---|---|---|---|
 *   | perm_x | Title | ✅ | ❌ |
 */
final class MarkdownPermissionMatrixParser extends AbstractPermissionMatrixParser implements PermissionMatrixParserInterface
{
    public function name(): string
    {
        return 'md';
    }

    public function extension(): string
    {
        return 'md';
    }

    public function parse(string $content): PermissionMatrix
    {
        if (trim($content) === '') {
            throw MalformedMarkdownException::emptyContent();
        }

        // Filter out blank lines
        $lines = array_values(
            array_filter(
                explode("\n", $content),
                static fn(string $line): bool => trim($line) !== '',
            )
        );

        if (count($lines) < 3) {
            throw MalformedMarkdownException::tooFewLines();
        }

        $header = $this->parseTableRow($lines[0]);
        if (count($header) < 3) {
            throw MalformedMarkdownException::headerTooFewColumns(count($header));
        }

        $roleKeys = $this->extractRoleKeys($header);
        $this->assertDelimiterRow($this->parseTableRow($lines[1]), count($header));

        $expectedColumns = 2 + count($roleKeys);

        $rows = [];
        /** @var array<string, int> $seenPermissionKeys key => first row number */
        $seenPermissionKeys = [];

        for ($i = 2, $n = count($lines); $i < $n; $i++) {
            $rowNumber = $i + 1; // header is row 1, delimiter row 2
            $cells = $this->parseTableRow($lines[$i]);

            if (count($cells) !== $expectedColumns) {
                throw new IncompleteRowException($rowNumber, $expectedColumns, count($cells), $lines[$i]);
            }

            $key = $cells[0];
            if ($key === '') {
                throw new MissingPermissionKeyException($rowNumber);
            }

            if (isset($seenPermissionKeys[$key])) {
                throw new DuplicatePermissionKeyException($key, $seenPermissionKeys[$key], $rowNumber);
            }
            $seenPermissionKeys[$key] = $rowNumber;

            $title = $cells[1] !== '' ? $cells[1] : $key;

            $roles = [];
            foreach ($roleKeys as $offset => $roleKey) {
                $roles[$roleKey] = $this->parseBoolean($cells[2 + $offset], $key, $roleKey, $rowNumber);
            }

            $rows[] = [
                'key' => $key,
                'title' => $title,
                'roles' => $roles,
            ];
        }

        usort($rows, static fn(array $a, array $b): int => strcmp($a['key'], $b['key']));

        return new PermissionMatrix(array_values($roleKeys), $rows);
    }

    /**
     * Extracts and validates role keys from the header cells.
     *
     * @param list<string> $header
     * @return list<string>
     */
    private function extractRoleKeys(array $header): array
    {
        $roleCells = array_slice($header, 2);

        /** @var array<string, int> $seen key => 1-based column */
        $seen = [];
        $roleKeys = [];
        foreach ($roleCells as $offset => $roleKey) {
            $csvColumn = $offset + 3; // header index (offset + 2) -> 1-based column

            if ($roleKey === '') {
                throw new EmptyRoleKeyException($csvColumn);
            }
            if (isset($seen[$roleKey])) {
                throw new DuplicateRoleKeyException($roleKey, $seen[$roleKey], $csvColumn);
            }
            $seen[$roleKey] = $csvColumn;
            $roleKeys[] = $roleKey;
        }

        return $roleKeys;
    }

    /**
     * The second row must be a valid GFM delimiter with one cell per header column.
     * Alignment colons are accepted and discarded.
     *
     * @param list<string> $cells
     */
    private function assertDelimiterRow(array $cells, int $headerColumns): void
    {
        if (count($cells) !== $headerColumns) {
            throw MalformedMarkdownException::delimiterColumnMismatch($headerColumns, count($cells));
        }

        foreach ($cells as $cell) {
            if (preg_match('/^:?-+:?$/', $cell) !== 1) {
                throw MalformedMarkdownException::invalidDelimiterRow();
            }
        }
    }

    /**
     * Splits a Markdown table row into trimmed cells.
     *
     * @return list<string>
     */
    private function parseTableRow(string $line): array
    {
        $line = trim($line);
        if (str_starts_with($line, '|')) {
            $line = substr($line, 1);
        }
        if (str_ends_with($line, '|')) {
            $line = substr($line, 0, -1);
        }

        $line = str_replace('\|', "\0", $line);
        $cells = explode('|', $line);

        return array_map(
            static fn(string $cell): string => trim(str_replace("\0", '|', $cell)),
            $cells,
        );
    }
}
