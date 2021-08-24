<?php

namespace WapplerSystems\ZabbixClient\Operation;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;
use WapplerSystems\ZabbixClient\OperationResult;


/**
 *
 * Check if strict syntax is enabled
 *
 */
class HasMissingDefaultMailSettings implements IOperation, SingletonInterface
{

    /**
     *
     * @param array $parameter None
     * @return OperationResult
     */
    public function execute($parameter = [])
    {
        $returnValue = [];
        if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'])) {
            $returnValue['defaultMailFromAddress'] = 'defaultMailFromAddress';
        }

        if (empty($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'])) {
            $returnValue['defaultMailFromName'] = 'defaultMailFromName';
        }

        if(empty($returnValue)) {
            return new OperationResult(true, [], 'No missing default mail settings detected!');
        }

        return new OperationResult(true, [ $returnValue ]);
    }
}
