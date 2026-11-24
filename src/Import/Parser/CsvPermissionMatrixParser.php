<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Import\Parser;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicatePermissionKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicateRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\EmptyRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\IncompleteRowException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MalformedCsvException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MissingPermissionKeyException;

/**
 * Parses CSV content into a PermissionMatrix.
 *
 * Expected format (see documentation for details):
 *      Permission-Key,Permission-Title,role_a,role_b
 *      perm_edit,Edit things,yes,no
 *      perm_delete,Delete things,1,0
 */
final class CsvPermissionMatrixParser extends AbstractPermissionMatrixParser implements PermissionMatrixParserInterface
{
    /**
     * Empty escape string selects RFC-4180 behaviour (quotes escaped only by doubling)
     * and disables PHP's legacy backslash escaping, which otherwise
     * corrupts fields ending in a backslash before a delimiter or quote.
     */
    private const CSV_ESCAPE = '';

    public function name(): string
    {
        return 'csv';
    }

    public function extension(): string
    {
        return 'csv';
    }

    /**
     * Parses CSV content into a PermissionMatrix.
     *
     * Expected format (see documentation for details):
     *      Permission-Key,Permission-Title,role_a,role_b
     *      perm_edit,Edit things,yes,no
     *      perm_delete,Delete things,1,0
     */
    public function parse(string $content): PermissionMatrix
    {
        $content = trim($content);
        if ($content === '') {
            throw MalformedCsvException::emptyContent();
        }

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        try {
            $header = fgetcsv($handle, 0, ',', '"', self::CSV_ESCAPE);

            if ($header === false || count($header) < 3) {
                throw MalformedCsvException::tooFewColumns(is_array($header) ? count($header) : 0);
            }

            $roleKeys = $this->extractRoleKeys($header);
            $expectedColumns = 2 + count($roleKeys);

            $rows = [];
            /** @var array<string, int> $seenPermissionKeys key => first row number */
            $seenPermissionKeys = [];
            $rowNumber = 1; // header is row 1

            while (($row = fgetcsv($handle, 0, ',', '"', self::CSV_ESCAPE)) !== false) {
                $rowNumber++;

                if (count($row) !== $expectedColumns) {
                    throw new IncompleteRowException(
                        $rowNumber,
                        $expectedColumns,
                        count($row),
                        implode(',', array_map(static fn($c): string => (string)$c, $row)),
                    );
                }

                $key = trim((string)$row[0]);
                if ($key === '') {
                    throw new MissingPermissionKeyException($rowNumber);
                }

                if (isset($seenPermissionKeys[$key])) {
                    throw new DuplicatePermissionKeyException($key, $seenPermissionKeys[$key], $rowNumber);
                }
                $seenPermissionKeys[$key] = $rowNumber;

                $title = trim((string)$row[1]);
                if ($title === '') {
                    $title = $key; // fallback: use permission key for empty title
                }

                $roles = [];
                foreach ($roleKeys as $offset => $roleKey) {
                    $roles[$roleKey] = $this->parseBoolean(
                        (string)$row[2 + $offset],
                        $key,
                        $roleKey,
                        $rowNumber,
                    );
                }

                $rows[] = [
                    'key' => $key,
                    'title' => $title,
                    'roles' => $roles,
                ];
            }
        } finally {
            fclose($handle);
        }

        usort($rows, static fn(array $a, array $b): int => strcmp($a['key'], $b['key']));

        return new PermissionMatrix(array_values($roleKeys), $rows);
    }

    /**
     * Extracts and validates the role keys from the header row.
     * Trailing empty columns are trimmed (spreadsheet artifact).
     *
     * @param list<string|null> $header
     * @return list<string>
     */
    private function extractRoleKeys(array $header): array
    {
        $roleCells = array_map(static fn($c): string => (string)$c, array_slice($header, 2));

        // Drop trailing empty columns only.
        while ($roleCells !== [] && trim((string)end($roleCells)) === '') {
            array_pop($roleCells);
        }

        if ($roleCells === []) {
            throw MalformedCsvException::noRoles();
        }

        /** @var array<string, int> $seen key => 1-based column */
        $seen = [];
        $roleKeys = [];
        foreach ($roleCells as $offset => $cell) {
            $roleKey = trim($cell);
            $csvColumn = $offset + 3;

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
}
