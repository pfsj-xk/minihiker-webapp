<?php

use yii\rest\UrlRule;

/** Url rules for apivp1 application */
return [
    ['class' => UrlRule::class, 'controller' => 'client'],
    ['class' => UrlRule::class, 'controller' => 'family'],
    [
        'class' => UrlRule::class,
        'controller' => 'participant',
        'extraPatterns' => [
            'GET {program_id}' => 'view',
            'POST {client_id}/{program_id}' => 'create',
            'DELETE {client_id}/{program_id}' => 'delete'
        ],
        'tokens' => [
            '{client_id}' => '<client_id:\\d[\\d,]*>',
            '{program_id}' => '<program_id:\\d[\\d,]*>'
        ]
    ],
    ['class' => UrlRule::class, 'controller' => 'program'],
    ['class' => UrlRule::class, 'controller' => 'program-group'],
    ['class' => UrlRule::class, 'controller' => 'program-type'],
    ['class' => UrlRule::class, 'controller' => ['wxbp' => 'banner-program']],
    ['class' => UrlRule::class, 'controller' => ['wxcpi' => 'client-passport-image']],
    ['class' => UrlRule::class, 'controller' => ['wxps' => 'program-search']],
    ['class' => UrlRule::class, 'controller' => 'wx-payment', 'pluralize' => false],
    ['class' => UrlRule::class, 'controller' => 'wx-payment-notify', 'pluralize' => false],
    ['class' => UrlRule::class, 'controller' => 'wx-unified-payment-order'],
];