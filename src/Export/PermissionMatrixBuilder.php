<?php

namespace Pharmaline\PhlAbc\Export;

use Pharmaline\PhlAbc\Service\ComposerService;
use Pharmaline\PhlAbc\Service\YamlService;
use RuntimeException;

final class PermissionMatrixBuilder
{
    public function __construct(
        private readonly ComposerService $composerService,
        private readonly YamlService $yamlService,
    ) {
    }

    public function build(): PermissionMatrix
    {
        $defs = $this->loadDefinitions();

        $permissions = $this->flattenPermissions($defs['permissions']); // key => title, sorted
        $roleKeys = $this->flattenRoleKeys($defs['roles']);          // list<string>
        $rolePermissions = $this->flattenPresets($defs['presets']);         // role => list<pattern>

        $rows = [];
        foreach ($permissions as $key => $title) {
            $roles = [];
            foreach ($roleKeys as $roleKey) {
                $roles[$roleKey] = $this->matches($key, $rolePermissions[$roleKey] ?? []);
            }
            $rows[] = ['key' => $key, 'title' => $title, 'roles' => $roles];
        }
        return new PermissionMatrix($roleKeys, $rows);
    }

    /**
     * @return array{permissions: list<array>, roles: list<array>, presets: list<array>}
     */
    private function loadDefinitions(): array
    {
        $defs = ['permissions' => [], 'roles' => [], 'presets' => []];

        foreach ($this->composerService->getYamlFilesOfExtension() as $package) {
            foreach ($package as $key => $path) {
                if (isset($defs[$key])) {
                    $defs[$key][] = $this->yamlService->loadFile($path);
                }
            }
        }
        return $defs;
    }

    /**
     * Flattens permission definitions into a sorted map of key => title.
     * Later definitions win on key collision.
     *
     * @param list<array> $defs
     * @return array<string, string>
     */
    private function flattenPermissions(array $defs): array
    {
        $permissions = [];
        foreach ($defs as $content) {
            foreach ($content['permissions'] ?? [] as $permission) {
                if (!isset($permission['permission_key']) || $permission['permission_key'] === '') {
                    throw new RuntimeException(
                        sprintf(
                            'Permission definition is missing required field "permission_key": %s',
                            json_encode($permission, JSON_UNESCAPED_SLASHES),
                        )
                    );
                }

                if (!isset($permission['title']) || $permission['title'] === '') {
                    throw new RuntimeException(
                        sprintf(
                            'Permission "%s" is missing required field "title".',
                            $permission['permission_key'],
                        )
                    );
                }

                $permissions[$permission['permission_key']] = $permission['title'];
            }
        }
        ksort($permissions);

        return $permissions;
    }

    /**
     * De-duplicated list of role keys, preserving first-seen order across packages.
     *
     * @param list<array> $defs
     * @return list<string>
     */
    private function flattenRoleKeys(array $defs): array
    {
        $roleKeys = [];
        foreach ($defs as $content) {
            foreach ($content['roles'] ?? [] as $role) {
                if (!isset($role['role_key']) || $role['role_key'] === '') {
                    throw new RuntimeException(
                        sprintf(
                            'Role definition is missing required field "role_key": %s',
                            json_encode($role, JSON_UNESCAPED_SLASHES),
                        )
                    );
                }

                $roleKey = $role['role_key'];
                if (!in_array($roleKey, $roleKeys, true)) {
                    $roleKeys[] = $roleKey;
                }
            }
        }
        return $roleKeys;
    }

    /**
     * Merges preset patterns per role across packages (de-duplicated),
     * so extensions can extend a base role's permissions instead of
     * silently overwriting them.
     *
     * @param list<array> $defs
     * @return array<string, list<string>>
     */
    private function flattenPresets(array $defs): array
    {
        $rolePermissions = [];
        foreach ($defs as $content) {
            foreach ($content['presets'] ?? [] as $preset) {
                if (!isset($preset['role']) || $preset['role'] === '') {
                    throw new RuntimeException(
                        sprintf(
                            'Preset definition is missing required field "role": %s',
                            json_encode($preset, JSON_UNESCAPED_SLASHES),
                        )
                    );
                }

                $role = $preset['role'];
                $patterns = $preset['permissions'] ?? [];

                $rolePermissions[$role] = array_values(array_unique([
                    ...($rolePermissions[$role] ?? []),
                    ...$patterns,
                ]));
            }
        }
        return $rolePermissions;
    }

    private function matches(string $permission, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern === '*' || $pattern === $permission) {
                return true;
            }
            if (str_contains($pattern, '*') && fnmatch($pattern, $permission)) {
                return true;
            }
        }
        return false;
    }
}
