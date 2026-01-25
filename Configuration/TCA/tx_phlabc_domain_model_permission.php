<?php

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission',
        'label' => 'title',
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
        'searchFields' => 'title,permission_key,description',
        'iconfile' => 'EXT:phlabc/Resources/Public/Icons/permission.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'permission_key, title, title_translation_key, description, description_translation_key, categories'],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'permission_key' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.permission_key',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required,unique',
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim,required',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.description',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 40,
            ],
        ],
        'title_translation_key' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.title_translation_key',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
            ],
        ],
        'description_translation_key' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.description_translation_key',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
            ],
        ],
        'categories' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.categories',
            'config' => [
                'type' => 'category',
            ],
        ],
        'roles' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.roles',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_phlabc_domain_model_role',
                'MM' => 'tx_phlabc_role_permission_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'multiple' => 0,
            ],
        ],
        'roles_deny' => [
            'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:tx_phlabc_domain_model_permission.roles_deny',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_phlabc_domain_model_role',
                'MM' => 'tx_phlabc_role_permission_deny_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'multiple' => 0,
            ],
        ],
    ],
];