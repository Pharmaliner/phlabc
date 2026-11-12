<?php

namespace Pharmaline\PhlAbc\Import\Parser;

use InvalidArgumentException;

final class PermissionMatrixParserRegistry
{
    /** @var array<string, PermissionMatrixParserInterface> */
    private array $parsers = [];

    /** @param iterable<PermissionMatrixParserInterface> $parsers */
    public function __construct(iterable $parsers)
    {
        foreach ($parsers as $parser) {
            $this->parsers[$parser->name()] = $parser;
        }
    }

    public function get(string $name): PermissionMatrixParserInterface
    {
        return $this->parsers[$name]
            ?? throw new InvalidArgumentException(
                sprintf(
                    'Unknown format "%s". Available: %s',
                    $name,
                    implode(', ', array_keys($this->parsers)),
                )
            );
    }

    /** @return list<string> */
    public function available(): array
    {
        return array_keys($this->parsers);
    }
}
