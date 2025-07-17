<?php

defined('TYPO3') or die();

return (static function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', [
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
    ]);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'roles',
        '',
        'after:usergroup'
    );
})();
