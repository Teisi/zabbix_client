<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Operation;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\ZabbixClient\OperationResult;


/**
 *
 */
class GetSystemInfos implements IOperation, SingletonInterface
{

    /**
     * @var array Available info scopes
     */
    protected $scopes = ['all'];

    public $methodList = [
        'GetDiskSpace',
        'GetPHPVersion',
        'GetTYPO3Version',
        'HasUpdate',
        'HasSecurityUpdate',
        'GetLastSchedulerRun',
        'GetLastExtensionListUpdate',
        'GetDatabaseVersion',
        'GetApplicationContext',
        'GetTotalLogFilesSize',
        'GetZabbixLogFileSize',
        'GetOpCacheStatus',
        'GetExtensionList' => [
            'scopes' => 'local',
            'withUpdateInfo' => '1'
        ],
        'GetLogResults' => [
            'filter' => 'error',
            'max' => 5
        ],

        // 'CheckPathExists',
        // 'GetExtensionVersion',
        // 'GetFilesystemChecksum',
        // 'HasForbiddenUsers',
        'GetInsecureExtensionList',
        'GetOutdatedExtensionList',
        // 'HasRemainingUpdates',
        // 'HasExtensionUpdate',
        'HasExtensionUpdateList' => 'loaded',
        // 'GetProgramVersion',
        // 'GetFeatureValue',
    ];

    /**
     *
     * @param array $parameter None
     * @return OperationResult
     */
    public function execute(array $parameter = []): OperationResult
    {
        $scope = empty($parameter['scope']) ? 'all' : (is_string($parameter['scope']) ? $parameter['scope'] : '');

        if(!in_array($scope, $this->scopes, true)) {
            return new OperationResult(false, [], 'Error parameter \'scope\' empty or not valid!');
        }

        $resultArray = [];

        foreach ($this->methodList as $methodKey => $methodValue) {
            $methodName = '';
            $methodParams = [];

            if(is_string($methodKey)) {
                $methodName = $methodKey;

                if(is_array($methodValue)) {
                    $methodParams = $methodValue;
                }
            }

            if(is_int($methodKey)) {
                $methodName = $methodValue;
            }

            if(!empty($methodName)) {
                $method = GeneralUtility::makeInstance('WapplerSystems\\ZabbixClient\\Operation\\'.$methodName);

                if(!empty($method)) {
                    $resultArray[$methodName] = [$method->execute($methodParams)->toArray()];
                }
            }

            // TODO: log: methodname not available
        }

        return new OperationResult(true, [$resultArray]);
    }
}
