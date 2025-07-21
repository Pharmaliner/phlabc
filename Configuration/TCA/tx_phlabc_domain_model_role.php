<?php

return [
    'ctrl' => [
        'title' => 'Role',
        'label' => 'role_key',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'role_key,title,description',
        'iconfile' => 'EXT:phlabc/Resources/Public/Icons/role.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                title, 
                role_key, 
                description, 
                is_custom_role,
                frontend_users,
                frontend_groups,
                permissions
            ',
        ],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'Hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'role_key' => [
            'label' => 'Role key',
            'config' => [
                'type' => 'input',
                'required' => 'true',
            ],
        ],
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
                'required' => 'true',
            ],
        ],
        'description' => [
            'label' => 'Description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
            ],
        ],
        'is_custom_role' => [
            'label' => 'Is Custom Role',
            'config' => [
                'type' => 'check',
                'items' => [
                    ['Yes', 1],
                ],
            ],
        ],
        'frontend_users' => [
            'label' => 'Frontend Users',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'MM' => 'tx_phlabc_role_user_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'multiple' => 0,
            ],
        ],
        'frontend_groups' => [
            'label' => 'Frontend Groups',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'MM' => 'tx_phlabc_role_group_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'multiple' => 0,
            ],
        ],
        'permissions' => [
            'label' => 'Permissions',
            'exclude' => true,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_phlabc_domain_model_permission',
                'MM' => 'tx_phlabc_role_permission_mm',
                'foreign_table_where' => 'AND 1=1',
            ],
        ],
    ],
];
