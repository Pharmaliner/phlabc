<?php

namespace Pharmaline\PhlAbc\Export\Formatter;

use InvalidArgumentException;

final class PermissionMatrixFormatterRegistry
{
    /** @var array<string, PermissionMatrixFormatterInterface> */
    private array $formatters = [];

    /** @param iterable<PermissionMatrixFormatterInterface> $formatters */
    public function __construct(iterable $formatters)
    {
        foreach ($formatters as $formatter) {
            $this->formatters[$formatter->name()] = $formatter;
        }
    }

    public function get(string $name): PermissionMatrixFormatterInterface
    {
        return $this->formatters[$name]
            ?? throw new InvalidArgumentException(
                sprintf(
                    'Unknown format "%s". Available: %s',
                    $name,
                    implode(', ', array_keys($this->formatters)),
                )
            );
    }

    /** @return list<string> */
    public function available(): array
    {
        return array_keys($this->formatters);
    }
}
