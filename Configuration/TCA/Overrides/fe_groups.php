<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionManagementUtility::addTCAcolumns('fe_groups', [
    'roles' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_groups.roles',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_phlabc_domain_model_role',
            'MM' => 'tx_phlabc_role_group_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => 0,
        ],
    ],
    'permissions_deny' => [
        'label' => 'LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_groups.permissions_deny',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_phlabc_domain_model_permission',
            'MM' => 'tx_phlabc_fegroup_permission_deny_mm',
            'size' => 10,
            'autoSizeMax' => 30,
            'multiple' => 0,
        ],
    ],
]);

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_groups',
    '--div--;LLL:EXT:phlabc/Resources/Private/Language/locallang_db.xlf:fe_groups.tabs.phlabc,
        roles,
        permissions_deny'
);