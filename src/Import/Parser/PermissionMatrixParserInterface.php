<?php

namespace Pharmaline\PhlAbc\Import\Parser;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;

interface PermissionMatrixParserInterface
{
    public function parse(string $content): PermissionMatrix;

    public function extension(): string; // 'md', 'csv', ...

    public function name(): string;
}
