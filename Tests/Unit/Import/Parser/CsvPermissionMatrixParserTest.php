<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Tests\Unit\Import\Parser;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Import\Parser\CsvPermissionMatrixParser;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicatePermissionKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\DuplicateRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\EmptyRoleKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\IncompleteRowException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MalformedCsvException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MalformedInputException;
use Pharmaline\PhlAbc\Import\Parser\Exception\MissingPermissionKeyException;
use Pharmaline\PhlAbc\Import\Parser\Exception\UnknownBooleanValueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Throwable;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CsvPermissionMatrixParser::class)]
final class CsvPermissionMatrixParserTest extends UnitTestCase
{
    private CsvPermissionMatrixParser $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new CsvPermissionMatrixParser();
    }

    private function parseExpectingThrow(string $csv): Throwable
    {
        try {
            $this->subject->parse($csv);
        } catch (Throwable $e) {
            return $e;
        }

        self::fail('Expected parse() to throw, but it returned normally.');
    }

    #[Test]
    public function nameAndExtensionAreCsv(): void
    {
        self::assertSame('csv', $this->subject->name());
        self::assertSame('csv', $this->subject->extension());
    }

    #[Test]
    public function parsesWellFormedMatrix(): void
    {
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_admin,role_editor
        perm_edit,Edit things,1,1
        perm_delete,Delete things,1,0
        CSV;

        $matrix = $this->subject->parse($csv);

        self::assertInstanceOf(PermissionMatrix::class, $matrix);
        self::assertSame(['role_admin', 'role_editor'], $matrix->roleKeys);
        self::assertCount(2, $matrix->rows);

        // rows are sorted by key: perm_delete < perm_edit
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
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_a
        zebra,Z,1
        alpha,A,0
        mike,M,1
        CSV;

        $keys = array_column($this->subject->parse($csv)->rows, 'key');

        self::assertSame(['alpha', 'mike', 'zebra'], $keys);
    }

    #[Test]
    #[DataProvider('knownBooleanTokenProvider')]
    public function mapsKnownBooleanTokens(string $cell, bool $expected): void
    {
        $csv = "Permission-Key,Permission-Title,role_a\nperm,Title,{$cell}";

        self::assertSame(
            $expected,
            $this->subject->parse($csv)->rows[0]['roles']['role_a'],
        );
    }

    /**
     * @return iterable<string, array{0: string, 1: bool}>
     */
    public static function knownBooleanTokenProvider(): iterable
    {
        yield 'one'            => ['1', true];
        yield 'true lowercase' => ['true', true];
        yield 'true uppercase' => ['TRUE', true];
        yield 'true padded'    => [' true ', true];
        yield 'yes'            => ['yes', true];
        yield 'y'              => ['y', true];
        yield 'check emoji'    => ['✅', true];

        yield 'zero'           => ['0', false];
        yield 'false'          => ['false', false];
        yield 'no'             => ['no', false];
        yield 'n'              => ['n', false];
        yield 'cross emoji'    => ['❌', false];
    }

    /**
     * A present-but-empty role cell is resolved to "not granted" and maps to false
     */
    #[Test]
    public function blankRoleCellIsFalseNotAnError(): void
    {
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_a,role_b
        perm,Title,,1
        CSV;

        self::assertSame(
            ['role_a' => false, 'role_b' => true],
            $this->subject->parse($csv)->rows[0]['roles'],
        );
    }

    #[Test]
    public function emptyTitleIsBackfilledFromPermissionKey(): void
    {
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_a
        perm_x,,1
        CSV;

        $row = $this->subject->parse($csv)->rows[0];

        self::assertSame('perm_x', $row['key']);
        self::assertSame('perm_x', $row['title']);
    }

    #[Test]
    #[DataProvider('malformedHeaderProvider')]
    public function throwsMalformedCsvOnStructurallyInvalidInput(string $csv): void
    {
        self::assertInstanceOf(MalformedInputException::class, $this->parseExpectingThrow($csv));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function malformedHeaderProvider(): iterable
    {
        yield 'empty string'            => [''];
        yield 'whitespace only'         => ["   \n  \t "];
        yield 'header with one column'  => ["OnlyOne\nperm,Title,1"];
        yield 'header with two columns' => ["Permission-Key,Permission-Title\nperm,Title"];
        yield 'only role column blank'  => ["Permission-Key,Permission-Title,\nperm,Title,1"];
        yield 'role column all blank'   => ["Permission-Key,Permission-Title,,\nperm,Title,1,1"];
    }

    /**
     * Trailing empty header column (classic spreadsheet export): "...,role_a,role_b,".
     */
    #[Test]
    public function trailingEmptyHeaderColumnIsTrimmedAndTrailingRoleSurvives(): void
    {
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_a,role_b,
        perm_x,X,1,1
        CSV;

        $matrix = $this->subject->parse($csv);

        self::assertSame(['role_a', 'role_b'], $matrix->roleKeys);
        self::assertSame(['role_a' => true, 'role_b' => true], $matrix->rows[0]['roles']);
    }

    /**
     * Empty header column in the MIDDLE of roles: "...,role_a,,role_c" is not allowed.
     */
    #[Test]
    public function emptyMiddleHeaderColumnThrowsWithColumnPosition(): void
    {
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_a,,role_c
        perm_x,X,1,0,1
        CSV;

        $e = $this->parseExpectingThrow($csv);

        self::assertInstanceOf(EmptyRoleKeyException::class, $e);
        self::assertSame(4, $e->column);
    }

    /**
     * Escape character (#5): a title ending in a backslash before the delimiter,
     * e.g. "C:\export\". With the legacy '\\' escape the closing quote is treated
     * as escaped and the field eats the rest of the line; with '' (RFC-4180) it
     * parses cleanly.
     */
    #[Test]
    public function backslashBeforeDelimiterDoesNotCorruptField(): void
    {
        $csv = "Permission-Key,Permission-Title,role_a\n" .
            'perm_path,"C:\export\",1';

        $matrix = $this->subject->parse($csv);

        self::assertCount(1, $matrix->rows);
        self::assertSame('C:\export\\', $matrix->rows[0]['title']);
        self::assertSame(['role_a' => true], $matrix->rows[0]['roles']);
    }

    /**
     * Duplicate role column: "...,role_admin,role_admin" throws Exception.
     */
    #[Test]
    public function duplicateRoleColumnThrowsWithBothPositions(): void
    {
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_admin,role_admin
        perm,Title,1,0
        CSV;

        $e = $this->parseExpectingThrow($csv);

        self::assertInstanceOf(DuplicateRoleKeyException::class, $e);
        self::assertSame('role_admin', $e->roleKey);
        self::assertSame(3, $e->firstColumn);
        self::assertSame(4, $e->secondColumn);
    }

    /**
     * Duplicate permission key across rows throws Exception.
     */
    #[Test]
    public function duplicatePermissionKeyThrowsWithBothRows(): void
    {
        $csv = <<<CSV
        Permission-Key,Permission-Title,role_a
        perm_export,First,1
        perm_export,Second,0
        CSV;

        $e = $this->parseExpectingThrow($csv);

        self::assertInstanceOf(DuplicatePermissionKeyException::class, $e);
        self::assertSame('perm_export', $e->permissionKey);
        self::assertSame(2, $e->firstRow);
        self::assertSame(3, $e->secondRow);
    }

    /**
     * Unknown boolean token throws Exception.
     */
    #[Test]
    #[DataProvider('unknownBooleanTokenProvider')]
    public function unknownBooleanTokenThrowsWithContext(string $cell): void
    {
        $csv = "Permission-Key,Permission-Title,role_a\nperm,Title,{$cell}";

        $e = $this->parseExpectingThrow($csv);

        self::assertInstanceOf(UnknownBooleanValueException::class, $e);
        self::assertSame($cell, $e->value);
        self::assertSame('perm', $e->permissionKey);
        self::assertSame('role_a', $e->roleKey);
        self::assertSame(2, $e->row);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function unknownBooleanTokenProvider(): iterable
    {
        yield 'typo of grant' => ['grnat'];
        yield 'stray letter'  => ['x'];
        yield 'number two'    => ['2'];
        yield 'dash'          => ['-'];
        yield 'word maybe'    => ['maybe'];
    }

    /**
     * Wrong column count (short, long, or junk line) throws Exception.
     */
    #[Test]
    #[DataProvider('wrongColumnCountProvider')]
    public function wrongColumnCountThrows(string $csv, int $expectedCols, int $actualCols, int $row): void
    {
        $e = $this->parseExpectingThrow($csv);

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
        yield 'short row (missing a role cell)' => [
            "Permission-Key,Permission-Title,role_a,role_b\nperm,Title,1",
            4, 3, 2,
        ];
        yield 'over-long row' => [
            "Permission-Key,Permission-Title,role_a\nperm,Title,1,extra",
            3, 4, 2,
        ];
        yield 'junk line with no columns' => [
            "Permission-Key,Permission-Title,role_a\nperm_ok,Fine,1\njunk_line_no_commas",
            3, 1, 3,
        ];
    }

    /**
     * Blank permission key throws Exception.
     */
    #[Test]
    #[DataProvider('blankPermissionKeyProvider')]
    public function blankPermissionKeyThrows(string $csv, int $row): void
    {
        $e = $this->parseExpectingThrow($csv);

        self::assertInstanceOf(MissingPermissionKeyException::class, $e);
        self::assertSame($row, $e->row);
    }

    /**
     * @return iterable<string, array{0: string, 1: int}>
     */
    public static function blankPermissionKeyProvider(): iterable
    {
        yield 'empty key, empty title' => [
            "Permission-Key,Permission-Title,role_a,role_b\n,,1,1",
            2,
        ];
        yield 'empty key, present title' => [
            "Permission-Key,Permission-Title,role_a\n,Title,1",
            2,
        ];
    }
}
