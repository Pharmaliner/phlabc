<?php

return [
    'ctrl' => [
        'title' => 'Permission',
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
    ],
    'types' => [
        '0' => ['showitem' => 'permission_key, title, description'],
    ],
    'columns' => [
        'hidden' => [
            'label' => 'Hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'permission_key' => [
            'label' => 'Permission Key',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required,unique',
            ],
        ],
        'title' => [
            'label' => 'Title',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim,required',
            ],
        ],
        'description' => [
            'label' => 'Description',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 40,
            ],
        ],
    ],
];
