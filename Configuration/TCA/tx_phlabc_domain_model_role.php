<?php

return [
    'ctrl' => [
        'title' => 'Role',
        'label' => 'name',
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
        'searchFields' => 'name,description',
        'iconfile' => 'EXT:phlabc/Resources/Public/Icons/role.svg',
    ],
    'types' => [
        '0' => [
            'showitem' => '
                name, 
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
        'name' => [
            'label' => 'Name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required',
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
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_phlabc_domain_model_permission',
                'MM' => 'tx_phlabc_role_permission_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'multiple' => 0,
            ],
        ],
    ],
];
