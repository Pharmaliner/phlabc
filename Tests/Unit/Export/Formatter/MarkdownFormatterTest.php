<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Tests\Unit\Export\Formatter;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Export\Formatter\MarkdownFormatter;
use Pharmaline\PhlAbc\Import\Parser\Exception\MalformedInputException;
use Pharmaline\PhlAbc\Import\Parser\MarkdownPermissionMatrixParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(MarkdownFormatter::class)]
final class MarkdownFormatterTest extends UnitTestCase
{
    private MarkdownFormatter $formatter;
    private MarkdownPermissionMatrixParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new MarkdownFormatter();
        $this->parser = new MarkdownPermissionMatrixParser();
    }

    /**
     * Formats the matrix and parses the result straight back, so a test can assert
     * the matrix survives a "format -> parse cycle" intact.
     */
    private function roundTrip(PermissionMatrix $matrix): PermissionMatrix
    {
        return $this->parser->parse($this->formatter->format($matrix));
    }

    #[Test]
    public function nameAndExtensionAreMd(): void
    {
        self::assertSame('md', $this->formatter->name());
        self::assertSame('md', $this->formatter->extension());
    }

    #[Test]
    #[DataProvider('specialCharacterTitleProvider')]
    public function specialCharacterTitlesSurviveRoundTrip(string $title): void
    {
        $matrix = new PermissionMatrix(
            ['role_a', 'role_b'],
            [['key' => 'perm_x', 'title' => $title, 'roles' => ['role_a' => true, 'role_b' => false]]],
        );

        $row = $this->roundTrip($matrix)->rows[0];

        self::assertSame('perm_x', $row['key']);
        self::assertSame($title, $row['title']);
        self::assertSame(['role_a' => true, 'role_b' => false], $row['roles']);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function specialCharacterTitleProvider(): iterable
    {
        yield 'plain'              => ['Edit things'];
        yield 'single pipe'        => ['A | B'];
        yield 'many pipes'         => ['A | B | C'];
        yield 'literal backslash-pipe' => ['a\\|b'];
        yield 'backslashes'        => ['path A\\B\\C'];
    }

    #[Test]
    public function emptyTitleRoundTripsAsBackfilledKey(): void
    {
        $matrix = new PermissionMatrix(
            ['role_a'],
            [['key' => 'perm_x', 'title' => '', 'roles' => ['role_a' => true]]],
        );

        self::assertSame('perm_x', $this->roundTrip($matrix)->rows[0]['title']);
    }

    #[Test]
    public function multiRowMatrixRoundTrips(): void
    {
        $matrix = new PermissionMatrix(
            ['role_admin', 'role_editor'],
            [
                ['key' => 'perm_edit', 'title' => 'Edit', 'roles' => ['role_admin' => true, 'role_editor' => true]],
                ['key' => 'perm_delete', 'title' => 'Delete', 'roles' => ['role_admin' => true, 'role_editor' => false]],
            ],
        );

        $result = $this->roundTrip($matrix);

        self::assertSame(['role_admin', 'role_editor'], $result->roleKeys);
        self::assertSame(['perm_delete', 'perm_edit'], array_column($result->rows, 'key'));
    }

    /**
     * A role key containing a pipe must be escaped exactly once in the header and survive the round-trip.
     */
    #[Test]
    public function roleKeyWithPipeIsEscapedOnceAndRoundTrips(): void
    {
        $matrix = new PermissionMatrix(
            ['a|b'],
            [['key' => 'perm', 'title' => 'T', 'roles' => ['a|b' => true]]],
        );

        $output = $this->formatter->format($matrix);

        // Escaped once (\|), not twice (\\|).
        self::assertStringContainsString('a\|b', $output);
        self::assertStringNotContainsString('a\\\\|b', $output);

        self::assertSame(['a|b'], $this->roundTrip($matrix)->roleKeys);
    }

    /**
     * A matrix with no rows formats to just a header + delimiter, which the parser
     * rejects (it requires at least one data row).
     */
    #[Test]
    public function headerOnlyMatrixIsNotReparseable(): void
    {
        $output = $this->formatter->format(new PermissionMatrix(['role_a'], []));

        $this->expectException(MalformedInputException::class);
        $this->parser->parse($output);
    }

    #[Test]
    public function emitsDelimiterRowOfCorrectWidth(): void
    {
        $matrix = new PermissionMatrix(
            ['role_a', 'role_b'],
            [['key' => 'perm', 'title' => 'T', 'roles' => ['role_a' => true, 'role_b' => false]]],
        );

        $lines = explode("\n", $this->formatter->format($matrix));

        // 2 fixed columns + 2 roles = 4 delimiter cells.
        self::assertSame('|---|---|---|---|', $lines[1]);
    }

    #[Test]
    public function rendersBooleansAsCheckAndCrossInColumnOrder(): void
    {
        $matrix = new PermissionMatrix(
            ['role_a', 'role_b'],
            [['key' => 'perm', 'title' => 'T', 'roles' => ['role_a' => true, 'role_b' => false]]],
        );

        $lines = explode("\n", $this->formatter->format($matrix));

        self::assertStringStartsWith('| Permission-Key | Permission-Title | role_a | role_b |', $lines[0]);
        self::assertSame('| perm | T | ✅ | ❌ |', $lines[2]);
    }
}
