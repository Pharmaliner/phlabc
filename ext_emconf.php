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
    'version' => '1.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.99.99',
            'php' => '8.4.0-8.99.99',
            'extbase' => '13.0.0-13.99.99',
            'felogin' => '13.0.0-13.99.99',
            'fluid' => '13.4.0-13.99.99',
            'frontend' => '13.0.0-13.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];