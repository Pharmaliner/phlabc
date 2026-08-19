<?php

namespace Pharmaline\PhlAbc\Export\Formatter;

use Pharmaline\PhlAbc\Export\PermissionMatrix;
use RuntimeException;

final class CsvFormatter implements PermissionMatrixFormatterInterface
{
    public function name(): string
    {
        return 'csv';
    }

    public function extension(): string
    {
        return 'csv';
    }

    public function format(PermissionMatrix $matrix): string
    {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new RuntimeException('Could not open temp stream.');
        }

        fputcsv($stream, ['Permission-Key', 'Permission-Title', ...$matrix->roleKeys], ',', '"', '\\', "\r\n");
        foreach ($matrix->rows as $row) {
            $cells = [$row['key'], $row['title']];
            foreach ($matrix->roleKeys as $roleKey) {
                $cells[] = $row['roles'][$roleKey] ? 'yes' : 'no';
            }
            fputcsv($stream, $cells, ',', '"', '\\', "\r\n");
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content ?: '';
    }
}
