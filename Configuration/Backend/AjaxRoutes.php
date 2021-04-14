<?php
return [
    'zabbixclient_configuration_hashapikey' => [
        'path' => '/zabbix/client/hashapikey',
        'target' => \WapplerSystems\ZabbixClient\Controller\ConfigurationController::class . '::hashApiKeyAction',
    ],
];
