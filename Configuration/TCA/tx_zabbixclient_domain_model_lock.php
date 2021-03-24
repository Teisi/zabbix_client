<?php
return [
    'ctrl' => [
        'title' => 'Zabbix lock',
        'label' => 'ip',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'ip'
    ],
    'types' => [
        '1' => [
            'showitem' => '
                tstamp,
                ip,
                count
            '
        ],
    ],
    'columns' => [
        'crdate' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'ip' => [
            'exclude' => true,
            'label' => 'IP Address',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],

        'count' => [
            'exclude' => true,
            'label' => 'Count',
            'config' => [
                'type' => 'input',
                'size' => 60,
                'eval' => 'trim,required'
            ],
        ],
    ],
];
