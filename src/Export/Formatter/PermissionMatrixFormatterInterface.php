<?php

namespace Pharmaline\PhlAbc\Export\Formatter;

use Pharmaline\PhlAbc\Export\PermissionMatrix;

interface PermissionMatrixFormatterInterface
{
    public function format(PermissionMatrix $matrix): string;

    public function extension(): string; // 'md', 'csv', ...

    public function name(): string;      // matches the CLI --format value
}
