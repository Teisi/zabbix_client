<?php
return [
    'ctrl' => [
        'title' => 'Zabbix fe log',
        'label' => 'feLog',
        'versioningWS' => true,
        'enablecolumns' => [
        ],
        'searchFields' => 'error'
    ],
    'types' => [
        '1' => [
            'showitem' => '
                tstamp,
                error,
                log_data,
                log_message
            '
        ],
    ],
    'columns' => [
        'tstamp' => [
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],

        'error' => [
            'exclude' => true,
            'label' => 'Error Code',
            'description' => 'e. g. 404',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],

        'log_data' => [
            'exclude' => true,
            'label' => 'Log data',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'eval' => 'trim,required'
            ],
        ],

        'log_message' => [
            'exclude' => true,
            'label' => 'Log Message',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'eval' => 'trim,required'
            ],
        ],

        'details' => [
            'exclude' => true,
            'label' => 'Details',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'eval' => 'trim'
            ],
        ],
    ],
];
