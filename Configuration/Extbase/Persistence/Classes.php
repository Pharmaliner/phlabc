<?php

declare(strict_types=1);

use Pharmaline\PhlAbc\Domain\Model\FrontendGroup;
use Pharmaline\PhlAbc\Domain\Model\FrontendUser;
use Pharmaline\PhlAbc\Domain\Model\Permission;
use Pharmaline\PhlAbc\Domain\Model\Role;

return [
    FrontendUser::class => [
        'tableName' => 'fe_users',
        'properties' => [
            'usergroup' => [
                'fieldName' => 'usergroup',
            ],
            'roles' => [
                'fieldName' => 'roles',
            ],
        ],
    ],
    Role::class => [
        'tableName' => 'tx_phlabc_domain_model_role',
        'properties' => [
            'frontendUsers' => [
                'fieldName' => 'frontend_users',
            ],
            'frontendGroups' => [
                'fieldName' => 'frontend_groups',
            ],
            'permissions' => [
                'fieldName' => 'permissions',
            ],
            'permissionsDeny' => [
                'fieldName' => 'permissions_deny',
            ],
        ],
    ],
    FrontendGroup::class => [
        'tableName' => 'fe_groups',
        'properties' => [
            'title' => [
                'fieldName' => 'title',
            ],
        ],
    ],
    Permission::class => [
        'tableName' => 'tx_phlabc_domain_model_permission',
        'properties' => [
            'roles' => [
                'fieldName' => 'roles',
            ],
            'permissionsDeny' => [
                'fieldName' => 'permissions_deny',
            ],
            'permissions' => [
                'fieldName' => 'permissions',
            ],
        ],
    ],
];
