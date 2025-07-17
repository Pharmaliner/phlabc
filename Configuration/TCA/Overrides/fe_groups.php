<?php

defined('TYPO3') or die();

return (static function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', [
        'roles' => [
            'label' => 'LLL:EXT:phl_abc/Resources/Private/Language/locallang_db.xlf:fe_groups.roles',
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
    ]);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_groups',
        'roles',
        '',
        'after:title'
    );
})();
