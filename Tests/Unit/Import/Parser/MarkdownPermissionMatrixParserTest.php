<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Tests\Unit\Import\Parser;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicatePermissionKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicateRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\EmptyRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\IncompleteRowException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MalformedInputException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MalformedMarkdownException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MissingPermissionKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\UnknownBooleanValueException;
use Pharmaline\PhlAbc\Import\Parser\MarkdownPermissionMatrixParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Throwable;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(MarkdownPermissionMatrixParser::class)]
final class MarkdownPermissionMatrixParserTest extends UnitTestCase
{
    private MarkdownPermissionMatrixParser $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new MarkdownPermissionMatrixParser();
    }

    private function parseExpectingThrow(string $md): Throwable
    {
        try {
            $this->subject->parse($md);
        } catch (Throwable $e) {
            return $e;
        }

        self::fail('Expected parse() to throw, but it returned normally.');
    }

    #[Test]
    public function nameAndExtensionAreMd(): void
    {
        self::assertSame('md', $this->subject->name());
        self::assertSame('md', $this->subject->extension());
    }

    #[Test]
    public function parsesWellFormedMatrix(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_admin | role_editor |
        |---|---|---|---|
        | perm_edit | Edit things | ✅ | ✅ |
        | perm_delete | Delete things | ✅ | ❌ |
        MD;

        $matrix = $this->subject->parse($md);

        self::assertInstanceOf(PermissionMatrix::class, $matrix);
        self::assertSame(['role_admin', 'role_editor'], $matrix->roleKeys);
        self::assertCount(2, $matrix->rows);

        self::assertSame('perm_delete', $matrix->rows[0]['key']);
        self::assertSame('perm_edit', $matrix->rows[1]['key']);

        self::assertSame(
            ['role_admin' => true, 'role_editor' => false],
            $matrix->rows[0]['roles'],
        );
        self::assertSame('Delete things', $matrix->rows[0]['title']);
    }

    #[Test]
    public function rowsAreSortedByKeyAscending(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_a |
        |---|---|---|
        | zebra | Z | ✅ |
        | alpha | A | ❌ |
        | mike | M | ✅ |
        MD;

        $keys = array_column($this->subject->parse($md)->rows, 'key');

        self::assertSame(['alpha', 'mike', 'zebra'], $keys);
    }

    /**
     * Escaped pipes (the formatter's only escape) round-trip back to a literal pipe.
     */
    #[Test]
    public function unescapesEscapedPipesInCells(): void
    {
        $md = <<<'MD'
        | Permission-Key | Permission-Title | role_a |
        |---|---|---|
        | perm_x | a \| b | ✅ |
        MD;

        self::assertSame('a | b', $this->subject->parse($md)->rows[0]['title']);
    }

    #[Test]
    #[DataProvider('knownBooleanTokenProvider')]
    public function mapsKnownBooleanTokens(string $cell, bool $expected): void
    {
        $md = "| Permission-Key | Permission-Title | role_a |\n|---|---|---|\n| perm | Title | {$cell} |";

        self::assertSame(
            $expected,
            $this->subject->parse($md)->rows[0]['roles']['role_a'],
        );
    }

    /**
     * @return iterable<string, array{0: string, 1: bool}>
     */
    public static function knownBooleanTokenProvider(): iterable
    {
        yield 'check emoji'    => ['✅', true];
        yield 'one'            => ['1', true];
        yield 'true'           => ['true', true];
        yield 'true uppercase' => ['TRUE', true];
        yield 'yes'            => ['yes', true];
        yield 'y'              => ['y', true];

        yield 'cross emoji'    => ['❌', false];
        yield 'zero'           => ['0', false];
        yield 'false'          => ['false', false];
        yield 'no'             => ['no', false];
        yield 'n'              => ['n', false];
        yield 'empty cell'     => ['', false];
    }

    /**
     * A present-but-empty role cell is resolved to "not granted" and maps to false
     */
    #[Test]
    public function blankRoleCellIsFalseNotAnError(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_a | role_b |
        |---|---|---|---|
        | perm | Title |  | ✅ |
        MD;

        self::assertSame(
            ['role_a' => false, 'role_b' => true],
            $this->subject->parse($md)->rows[0]['roles'],
        );
    }

    #[Test]
    public function emptyTitleIsBackfilledFromPermissionKey(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_a |
        |---|---|---|
        | perm_x |  | ✅ |
        MD;

        $row = $this->subject->parse($md)->rows[0];

        self::assertSame('perm_x', $row['key']);
        self::assertSame('perm_x', $row['title']);
    }

    #[Test]
    #[DataProvider('malformedInputProvider')]
    public function throwsMalformedInputOnStructuralProblems(string $md): void
    {
        self::assertInstanceOf(MalformedInputException::class, $this->parseExpectingThrow($md));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function malformedInputProvider(): iterable
    {
        yield 'empty string'  => [''];
        yield 'whitespace'    => ["   \n  \t "];
        yield 'no data row'   => ["| Permission-Key | Permission-Title | role_a |\n|---|---|---|"];
        yield 'header missing title column' => ["| OnlyKey | role_a |\n|---|---|\n| perm | ✅ |"];
        yield 'header has no roles' => ["| Permission-Key | Permission-Title |\n|---|---|\n| perm | Title |"];
        yield 'second row is not a delimiter' => [
            "| Permission-Key | Permission-Title | role_a |\n| perm | Title | ✅ |\n| perm2 | T | ❌ |",
        ];
        yield 'delimiter column count mismatch' => [
            "| Permission-Key | Permission-Title | role_a | role_b |\n|---|---|\n| perm | Title | ✅ | ❌ |",
        ];
    }

    /**
     * Interior empty role header cell: "...| role_a |  | role_c |".
     */
    #[Test]
    public function emptyInteriorRoleHeaderThrowsWithColumn(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_a |  | role_c |
        |---|---|---|---|---|
        | perm | X | ✅ | ❌ | ✅ |
        MD;

        $e = $this->parseExpectingThrow($md);

        self::assertInstanceOf(EmptyRoleKeyException::class, $e);
        self::assertSame(4, $e->column);
    }

    /**
     * Trailing empty role header cell: "...| role_a |  |" throws Exception.
     */
    #[Test]
    public function trailingEmptyRoleHeaderThrows(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_a |  |
        |---|---|---|---|
        | perm | X | ✅ | ❌ |
        MD;

        $e = $this->parseExpectingThrow($md);

        self::assertInstanceOf(EmptyRoleKeyException::class, $e);
        self::assertSame(4, $e->column);
    }

    /**
     * Duplicate role column throws Exception.
     */
    #[Test]
    public function duplicateRoleColumnThrowsWithBothPositions(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_admin | role_admin |
        |---|---|---|---|
        | perm | Title | ✅ | ❌ |
        MD;

        $e = $this->parseExpectingThrow($md);

        self::assertInstanceOf(DuplicateRoleKeyException::class, $e);
        self::assertSame('role_admin', $e->roleKey);
        self::assertSame(3, $e->firstColumn);
        self::assertSame(4, $e->secondColumn);
    }

    /**
     * Duplicate permission key across rows (rows 3 and 4 — data starts at row 3) throws Exception.
     */
    #[Test]
    public function duplicatePermissionKeyThrowsWithBothRows(): void
    {
        $md = <<<MD
        | Permission-Key | Permission-Title | role_a |
        |---|---|---|
        | perm_export | First | ✅ |
        | perm_export | Second | ❌ |
        MD;

        $e = $this->parseExpectingThrow($md);

        self::assertInstanceOf(DuplicatePermissionKeyException::class, $e);
        self::assertSame('perm_export', $e->permissionKey);
        self::assertSame(3, $e->firstRow);
        self::assertSame(4, $e->secondRow);
    }

    /**
     * Unknown boolean token throws Exception.
     */
    #[Test]
    #[DataProvider('unknownBooleanTokenProvider')]
    public function unknownBooleanTokenThrowsWithContext(string $cell): void
    {
        $md = "| Permission-Key | Permission-Title | role_a |\n|---|---|---|\n| perm | Title | {$cell} |";

        $e = $this->parseExpectingThrow($md);

        self::assertInstanceOf(UnknownBooleanValueException::class, $e);
        self::assertSame($cell, $e->value);
        self::assertSame('perm', $e->permissionKey);
        self::assertSame('role_a', $e->roleKey);
        self::assertSame(3, $e->row);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function unknownBooleanTokenProvider(): iterable
    {
        yield 'wrong checkmark' => ['✓'];
        yield 'stray letter'    => ['x'];
        yield 'number two'      => ['2'];
        yield 'word maybe'      => ['maybe'];
    }

    /**
     * Wrong column count (short, long, or junk line) throws Exception.
     */
    #[Test]
    #[DataProvider('wrongColumnCountProvider')]
    public function wrongColumnCountThrows(string $md, int $expectedCols, int $actualCols, int $row): void
    {
        $e = $this->parseExpectingThrow($md);

        self::assertInstanceOf(IncompleteRowException::class, $e);
        self::assertSame($row, $e->row);
        self::assertSame($expectedCols, $e->expectedColumns);
        self::assertSame($actualCols, $e->actualColumns);
    }

    /**
     * @return iterable<string, array{0: string, 1: int, 2: int, 3: int}>
     */
    public static function wrongColumnCountProvider(): iterable
    {
        yield 'short row' => [
            "| Permission-Key | Permission-Title | role_a | role_b |\n|---|---|---|---|\n| perm | Title | ✅ |",
            4, 3, 3,
        ];
        yield 'over-long row' => [
            "| Permission-Key | Permission-Title | role_a |\n|---|---|---|\n| perm | Title | ✅ | extra |",
            3, 4, 3,
        ];
        yield 'junk line without pipes' => [
            "| Permission-Key | Permission-Title | role_a |\n|---|---|---|\n| perm_ok | Fine | ✅ |\njunk_no_pipes",
            3, 1, 4,
        ];
    }

    /**
     * Blank permission key throws Exception.
     */
    #[Test]
    #[DataProvider('blankPermissionKeyProvider')]
    public function blankPermissionKeyThrows(string $md, int $row): void
    {
        $e = $this->parseExpectingThrow($md);

        self::assertInstanceOf(MissingPermissionKeyException::class, $e);
        self::assertSame($row, $e->row);
    }

    /**
     * @return iterable<string, array{0: string, 1: int}>
     */
    public static function blankPermissionKeyProvider(): iterable
    {
        yield 'empty key and title' => [
            "| Permission-Key | Permission-Title | role_a | role_b |\n|---|---|---|---|\n|  |  | ✅ | ❌ |",
            3,
        ];
        yield 'empty key, present title' => [
            "| Permission-Key | Permission-Title | role_a |\n|---|---|---|\n|  | Title | ✅ |",
            3,
        ];
    }
}
