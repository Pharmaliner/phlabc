<?php

declare(strict_types=1);

namespace Pharmaline\PhlAbc\Service;

use Pharmaline\PhlAbc\Domain\Dto\PermissionMatrix;
use Symfony\Component\Yaml\Yaml;

class PresetsYamlGeneratorService
{
    public function generate(PermissionMatrix $matrix): string
    {
        $presets = [];
        $totalPermissions = count($matrix->rows);

        foreach ($matrix->roleKeys as $roleKey) {
            $grantedPermissions = [];
            $grantedCount = 0;

            foreach ($matrix->rows as $row) {
                if (isset($row['roles'][$roleKey]) && $row['roles'][$roleKey]) {
                    $grantedPermissions[] = $row['key'];
                    $grantedCount++;
                }
            }

            // If the role has access to every single permission, use wildcard
            if ($grantedCount === $totalPermissions && $totalPermissions > 0) {
                $permissions = ['*'];
            } else {
                $permissions = $grantedPermissions;
            }

            $presets[] = [
                'role' => $roleKey,
                'permissions' => $permissions,
            ];
        }

        $data = [
            'presets' => $presets,
        ];

        $yaml = Yaml::dump(
            $data,
            6,
            2,
            Yaml::DUMP_FORCE_DOUBLE_QUOTES_ON_VALUES | Yaml::DUMP_COMPACT_NESTED_MAPPING | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE
        );

        return $yaml;
    }
}
