<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionManagementUtility::addTCAcolumns('fe_users', [
    'roles' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.roles',
        'config' => [
            'type' => 'group',
            'allowed' => 'tx_phlabc_domain_model_role',
            'MM' => 'tx_phlabc_role_user_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => true,
            'suggestOptions' => [
                'default' => [
                    'additionalSearchFields' => 'role_key,title,title_translation_key',
                    'addWhere' => '',
                ],
            ],
        ],
    ],
    'permissions' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.permissions',
        'config' => [
            'type' => 'group',
            'allowed' => 'tx_phlabc_domain_model_permission',
            'MM' => 'tx_phlabc_feuser_permission_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => true,
            'suggestOptions' => [
                'default' => [
                    'additionalSearchFields' => 'permission_key,title,title_translation_key',
                    'addWhere' => '',
                ],
            ],
        ],
    ],
    'permissions_deny' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.permissions_deny',
        'config' => [
            'type' => 'group',
            'allowed' => 'tx_phlabc_domain_model_permission',
            'MM' => 'tx_phlabc_feuser_permission_deny_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => true,
            'suggestOptions' => [
                'default' => [
                    'additionalSearchFields' => 'permission_key,title,title_translation_key',
                    'addWhere' => '',
                ],
            ],
        ],
    ],
    'parent_frontend_user' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.parent_frontend_user',
        'config' => [
            'type' => 'group',
            'allowed' => 'fe_users',
            'multiple' => false,
            'maxitems' => 1,
            'minitems' => 0,
        ]
    ]
]);

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.tabs.phlabc,
        roles,
        permissions,
        permissions_deny,
        parent_frontend_user'
);
