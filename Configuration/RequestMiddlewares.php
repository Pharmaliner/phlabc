<?php

use Pharmaline\PhlAbc\Middleware\SetPermissionAspect;

return [
    'frontend' => [
        'phlabc/frontenduser-permission-aspect' => [
            'target' => SetPermissionAspect::class,
            'before' => [
                'typo3/cms-redirects/redirecthandler',
            ],
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
