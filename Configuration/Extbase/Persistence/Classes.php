<?php
declare(strict_types = 1);

return [
    \Pharmaline\PhlAbc\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
        'properties' => [
            'usergroup' => [
                'fieldName' => 'usergroup',
            ],
        ],
    ],
    \Pharmaline\PhlAbc\Domain\Model\FrontendGroup::class => [
        'tableName' => 'fe_groups',
        'properties' => [
            'title' => [
                'fieldName' => 'title',
            ],
        ],
    ]
];