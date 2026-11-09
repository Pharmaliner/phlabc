<?php

namespace Pharmaline\PhlAbc\Export\Formatter;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;

final class MarkdownFormatter implements PermissionMatrixFormatterInterface
{
    public function name(): string
    {
        return 'md';
    }

    public function extension(): string
    {
        return 'md';
    }

    public function format(PermissionMatrix $matrix): string
    {
        $escape = static fn ($cell): string => str_replace('|', '\|', (string)$cell);

        $headerCells = array_map($escape, ['Permission-Key', 'Permission-Title', ...$matrix->roleKeys]);
        $lines = [
            '| ' . implode(' | ', $headerCells) . ' |',
            '|' . str_repeat('---|', count($headerCells)),
        ];

        foreach ($matrix->rows as $row) {
            $cells = [
                $escape($row['key']),
                $escape($row['title']),
            ];

            foreach ($matrix->roleKeys as $roleKey) {
                $cells[] = $row['roles'][$roleKey] ? '✅' : '❌';
            }
            $lines[] = '| ' . implode(' | ', $cells) . ' |';
        }

        return implode("\n", $lines) . "\n";
    }
}
