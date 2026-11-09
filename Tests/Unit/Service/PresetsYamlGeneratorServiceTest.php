<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Tests\Unit\Service;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Pharmaline\PhlAbc\Service\PresetsYamlGeneratorService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(PresetsYamlGeneratorService::class)]
final class PresetsYamlGeneratorServiceTest extends UnitTestCase
{
    private PresetsYamlGeneratorService $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new PresetsYamlGeneratorService();
    }

    /**
     * Generates the YAML and parses it back into an array, so tests can assert on
     * the data instead of the exact text.
     * @return array{presets: list<array{role: string, permissions: mixed}>}
     */
    private function generateAndParse(PermissionMatrix $matrix): array
    {
        /** @var array{presets: list<array{role: string, permissions: mixed}>} $parsed */
        $parsed = Yaml::parse($this->subject->generate($matrix));

        return $parsed;
    }

    /**
     * @param list<string> $keys
     * @param array<string, bool> ...$grants role => granted, one map per permission
     */
    private function matrix(array $keys, array ...$grants): PermissionMatrix
    {
        $rows = [];
        foreach ($grants as $i => $roles) {
            $rows[] = ['key' => 'perm_' . $i, 'title' => 'Title ' . $i, 'roles' => $roles];
        }

        return new PermissionMatrix($keys, $rows);
    }

    #[Test]
    public function emitsOnePresetPerRoleInRoleKeyOrder(): void
    {
        $matrix = $this->matrix(
            ['role_c', 'role_a', 'role_b'],
            ['role_c' => true, 'role_a' => false, 'role_b' => true],
        );

        $roles = array_column($this->generateAndParse($matrix)['presets'], 'role');

        // Here, the sort order follows roleKeys exactly — this service does not sort roles (this is done in the parser)
        self::assertSame(['role_c', 'role_a', 'role_b'], $roles);
    }

    #[Test]
    public function grantedPermissionsFollowRowOrder(): void
    {
        $matrix = new PermissionMatrix(
            ['role_a'],
            [
                ['key' => 'zebra', 'title' => 'Z', 'roles' => ['role_a' => true]],
                ['key' => 'alpha', 'title' => 'A', 'roles' => ['role_a' => false]],
                ['key' => 'mike', 'title' => 'M', 'roles' => ['role_a' => true]],
            ],
        );

        $permissions = $this->generateAndParse($matrix)['presets'][0]['permissions'];

        self::assertSame(['zebra', 'mike'], $permissions);
    }

    #[Test]
    public function roleWithEveryPermissionCollapsesToWildcard(): void
    {
        $matrix = $this->matrix(
            ['role_admin'],
            ['role_admin' => true],
            ['role_admin' => true],
            ['role_admin' => true],
        );

        self::assertSame(['*'], $this->generateAndParse($matrix)['presets'][0]['permissions']);
    }

    #[Test]
    public function oneMissingGrantPreventsWildcard(): void
    {
        $matrix = $this->matrix(
            ['role_editor'],
            ['role_editor' => true],
            ['role_editor' => true],
            ['role_editor' => false],
        );

        $permissions = $this->generateAndParse($matrix)['presets'][0]['permissions'];

        self::assertSame(['perm_0', 'perm_1'], $permissions);
        self::assertNotContains('*', $permissions);
    }

    #[Test]
    public function wildcardAndExplicitRolesCoexist(): void
    {
        $matrix = $this->matrix(
            ['role_admin', 'role_editor'],
            ['role_admin' => true, 'role_editor' => true],
            ['role_admin' => true, 'role_editor' => false],
        );

        $presets = $this->generateAndParse($matrix)['presets'];

        self::assertSame(['*'], $presets[0]['permissions']);
        self::assertSame(['perm_0'], $presets[1]['permissions']);
    }

    /**
     * Empty matrix: totalPermissions is 0, so the `$totalPermissions > 0` guard must
     * prevent a wildcard. A role with no permissions must NOT collapse to ['*'].
     */
    #[Test]
    public function emptyMatrixDoesNotProduceWildcard(): void
    {
        $matrix = new PermissionMatrix(['role_a'], []);

        $permissions = $this->generateAndParse($matrix)['presets'][0]['permissions'];

        self::assertNotSame(['*'], $permissions);
        self::assertEmptyPermissionList($permissions);
    }

    #[Test]
    public function roleWithNoGrantsHasEmptyPermissions(): void
    {
        $matrix = $this->matrix(
            ['role_admin', 'role_none'],
            ['role_admin' => true, 'role_none' => false],
        );

        $presets = $this->generateAndParse($matrix)['presets'];

        self::assertSame(['*'], $presets[0]['permissions']);
        self::assertEmptyPermissionList($presets[1]['permissions']);
    }

    #[Test]
    public function noRolesProducesEmptyPresets(): void
    {
        $matrix = new PermissionMatrix([], [
            ['key' => 'perm_a', 'title' => 'A', 'roles' => []],
        ]);

        self::assertEmptyPermissionList($this->generateAndParse($matrix)['presets']);
    }

    /**
     * A missing role key in a row's roles map is treated as "not granted", not throw an exception.
     */
    #[Test]
    public function missingRoleKeyInRowCountsAsNotGranted(): void
    {
        $matrix = new PermissionMatrix(
            ['role_a', 'role_ghost'],
            [['key' => 'perm_0', 'title' => 'T', 'roles' => ['role_a' => true]]], // role_ghost absent
        );

        $presets = $this->generateAndParse($matrix)['presets'];

        self::assertSame(['*'], $presets[0]['permissions']);          // role_a: all granted
        self::assertEmptyPermissionList($presets[1]['permissions']);  // role_ghost: nothing
    }

    /**
     * Yaml::DUMP_FORCE_DOUBLE_QUOTES_ON_VALUES:
     * Scalars are emitted double-quoted.
     */
    #[Test]
    public function valuesAreDoubleQuoted(): void
    {
        $yaml = $this->subject->generate($this->matrix(['role_admin'], ['role_admin' => true]));

        self::assertStringContainsString('"role_admin"', $yaml);
        self::assertStringContainsString('"*"', $yaml);
    }

    /**
     * Yaml::DUMP_COMPACT_NESTED_MAPPING:
     * The first key of a sequence item's mapping sits
     * on the dash line (e.g. `- "role": ...`) rather than on its own line.
     */
    #[Test]
    public function usesCompactNestedMapping(): void
    {
        $yaml = $this->subject->generate($this->matrix(['role_admin'], ['role_admin' => true]));

        self::assertMatchesRegularExpression('/^\s*- "role":/m', $yaml);
    }



    /**
     * Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE:
     * An empty permission list must serialise as an empty YAML sequence `[]`, so the
     * shape stays consistent with a populated list (also a sequence).
     */
    #[Test]
    public function emptyPermissionListSerialisesAsEmptySequence(): void
    {
        $yaml = $this->subject->generate($this->matrix(
            ['role_none'],
            ['role_none' => false],
        ));

        self::assertStringContainsString('"permissions": []', $yaml);
        self::assertStringNotContainsString('"permissions": {  }', $yaml);
    }

    /**
     * Asserts, that a parsed "permissions"/"presets" value represents an empty collection,
     * tolerating the dumper's empty-map rendering (parses back to []).
     */
    private static function assertEmptyPermissionList(mixed $value): void
    {
        self::assertIsArray($value);
        self::assertCount(0, $value);
    }
}
