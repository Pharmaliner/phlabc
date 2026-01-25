<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionManagementUtility::addTCAcolumns('fe_users', [
    'roles' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.roles',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_phlabc_domain_model_role',
            'MM' => 'tx_phlabc_role_user_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => 0,
        ],
    ],
    'permissions' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.permissions',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_phlabc_domain_model_permission',
            'MM' => 'tx_phlabc_feuser_permission_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => 0,
        ],
    ],
    'permissions_deny' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.permissions_deny',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_phlabc_domain_model_permission',
            'MM' => 'tx_phlabc_feuser_permission_deny_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => 0,
        ],
    ],
    'parent_frontend_user' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_users.parent_frontend_user',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'fe_users',
            'foreign_where' => 'AND fe_users.uid = fe_users.parent_frontend_user',
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