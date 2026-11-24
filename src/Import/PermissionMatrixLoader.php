<?php

namespace Pharmaline\PhlAbc\Import;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Import\Parser\PermissionMatrixParserRegistry;
use RuntimeException;

final class PermissionMatrixLoader
{
    public function __construct(
        private readonly PermissionMatrixParserRegistry $parsers,
    ) {
    }

    public function loadFromFile(string $path): PermissionMatrix
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('File not found: %s', $path));
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException(sprintf('Could not read file: %s', $path));
        }

        $format = pathinfo($path, PATHINFO_EXTENSION);
        $parser = $this->parsers->get(strtolower($format));

        return $parser->parse($content);
    }
}
