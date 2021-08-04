<?php
return [
    'frontend' => [
        'wapplersystems/zabbix-client' => [
            'target' => \WapplerSystems\ZabbixClient\Middleware\ZabbixClient::class,
            'before' => [
                'typo3/cms-frontend/eid',
                'typo3/cms-frontend/maintenance-mode'
            ]
        ],
        'wapplersystems/zabbix-client/log-404' => [
            'target' => \WapplerSystems\ZabbixClient\Middleware\Log404::class,
            'after' => [
                'typo3/cms-core/normalized-params-attribute'
            ],
            'before' => [
                'typo3/cms-frontend/eid'
            ]
        ],
    ]
];
