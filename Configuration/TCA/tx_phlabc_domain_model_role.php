<?php

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role',
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
        'iconfile' => 'EXT:phlabc/Resources/Public/Icons/Extension.png',
        'typeicon_classes' => [
            'default' => 'tx-phlabc-logo-short',
        ],
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                role_key,
                title,
                title_translation_key,
                description,
                description_translation_key,
                custom_role,
                frontend_users,
                frontend_groups,
                permissions,
                permissions_deny,
                additional_data
            ',
        ],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'role_key' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.role_key',
            'config' => [
                'type' => 'input',
                'required' => 'true',
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.title',
            'config' => [
                'type' => 'input',
                'required' => 'true',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
            ],
        ],
        'custom_role' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.custom_role',
            'config' => [
                'type' => 'input',
                'required' => 'false'
            ],
        ],
        'frontend_users' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.frontend_users',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'MM' => 'tx_phlabc_role_user_mm',
                'MM_opposite_field' => 'roles',
                'size' => 10,
                'autoSizeMax' => 30,
                'multiple' => 0,
            ],
        ],
        'frontend_groups' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.frontend_groups',
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
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.permissions',
            'exclude' => true,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_phlabc_domain_model_permission',
                'MM' => 'tx_phlabc_role_permission_mm',
            ],
        ],
        'permissions_deny' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.permissions_deny',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_phlabc_domain_model_permission',
                'MM' => 'tx_phlabc_role_permission_deny_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'multiple' => 0,
            ],
        ],
        'title_translation_key' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.title_translation_key',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
            ],
        ],
        'description_translation_key' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.description_translation_key',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
            ],
        ],
        'additional_data' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_role.additional_data',
            'config' => [
                'type' => 'input',
                'required' => 'false'
            ],
        ]
    ],
];