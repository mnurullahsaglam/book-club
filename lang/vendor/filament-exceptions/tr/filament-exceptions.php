<?php

return [

    'labels' => [
        'model' => 'Exception',
        'model_plural' => 'Exceptions',
        'navigation' => 'Exception',
        'navigation_group' => 'Yönetim',

        'tabs' => [
            'exception' => 'EXception',
            'headers' => 'Headers',
            'cookies' => 'Cookies',
            'body' => 'Body',
            'queries' => 'Queries',
        ],
    ],

    'empty_list' => 'Hiç hata yok 😎',

    'columns' => [
        'method' => 'Method',
        'path' => 'Path',
        'type' => 'Type',
        'code' => 'Code',
        'ip' => 'IP',
        'occurred_at' => 'Occurred at',
    ],

];
