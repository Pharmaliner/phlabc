<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Pharmaline ABC',
    'description' => 'Pharmaline Extension to manage access to objects, content and actions with permissions.',
    'category' => 'fe',
    'author' => 'Christian Platt, Eike Drost, Holger Krämer',
    'author_email' => 'christian.platt@pharmaline.de',
    'author_company' => 'Pharmaline',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            'php' => '8.4.0-8.99.99',
            'extbase' => '14.0.0-14.99.99',
            'felogin' => '14.0.0-14.99.99',
            'fluid' => '14.4.0-14.99.99',
            'frontend' => '14.0.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];