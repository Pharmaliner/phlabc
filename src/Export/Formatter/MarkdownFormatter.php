<?php

namespace Pharmaline\PhlAbc\Export\Formatter;

use Pharmaline\PhlAbc\Export\PermissionMatrix;

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
        $escape = fn($cell) => str_replace('|', '\|', (string)$cell);

        $header = array_map($escape, ['Permission-Key', 'Permission-Title', ...$matrix->roleKeys]);
        $lines = [
            '| ' . implode(' | ', array_map($escape, $header)) . ' |',
            '|' . str_repeat('---|', count($header)),
        ];

        foreach ($matrix->rows as $row) {
            $cells = [
                $escape($row['key']),
                $escape($row['title'])
            ];

            foreach ($matrix->roleKeys as $roleKey) {
                $cells[] = $row['roles'][$roleKey] ? '✅' : '❌';
            }
            $lines[] = '| ' . implode(' | ', $cells) . ' |';
        }

        return implode("\n", $lines) . "\n";
    }
}
