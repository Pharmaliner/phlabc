<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Tests\Unit\Export\Formatter;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Export\Formatter\CsvFormatter;
use Pharmaline\PhlAbc\Import\Parser\CsvPermissionMatrixParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CsvFormatter::class)]
final class CsvFormatterTest extends UnitTestCase
{
    private CsvFormatter $formatter;
    private CsvPermissionMatrixParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new CsvFormatter();
        $this->parser = new CsvPermissionMatrixParser();
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
    public function nameAndExtensionAreCsv(): void
    {
        self::assertSame('csv', $this->formatter->name());
        self::assertSame('csv', $this->formatter->extension());
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
        yield 'plain'            => ['Edit things'];
        yield 'commas'           => ['Read, write, and delete'];
        yield 'double quotes'    => ['Say "hello" now'];
        yield 'backslashes'      => ['path A\\B\\C'];
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
     * A matrix with no rows formats to a header-only document and round-trips to an
     * empty row set (the CSV parser permits zero data rows).
     */
    #[Test]
    public function headerOnlyMatrixRoundTrips(): void
    {
        $matrix = new PermissionMatrix(['role_a'], []);

        $result = $this->roundTrip($matrix);

        self::assertSame(['role_a'], $result->roleKeys);
        self::assertSame([], $result->rows);
    }

    #[Test]
    public function usesCrlfLineEndings(): void
    {
        $matrix = new PermissionMatrix(
            ['role_a'],
            [['key' => 'perm', 'title' => 'T', 'roles' => ['role_a' => true]]],
        );

        $output = $this->formatter->format($matrix);

        self::assertStringContainsString("\r\n", $output);
        self::assertStringEndsWith("\r\n", $output);
    }

    #[Test]
    public function rendersBooleansAsYesNoInColumnOrder(): void
    {
        $matrix = new PermissionMatrix(
            ['role_a', 'role_b'],
            [['key' => 'perm', 'title' => 'T', 'roles' => ['role_a' => true, 'role_b' => false]]],
        );

        $output = $this->formatter->format($matrix);

        self::assertStringStartsWith('Permission-Key,Permission-Title,role_a,role_b', $output);
        self::assertStringContainsString("perm,T,yes,no\r\n", $output);
    }
}
