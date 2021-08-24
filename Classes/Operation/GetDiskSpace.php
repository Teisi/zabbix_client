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
use WapplerSystems\ZabbixClient\OperationResult;


/**
 * A Operation which returns the current disk space
 *
 * @author Tobias Liebig <tobias.liebig@typo3.org>
 *
 */
class GetDiskSpace implements IOperation, SingletonInterface
{
    /**
     * @param array $parameter
     * @return OperationResult
     */
    public function execute(array $parameter = []): OperationResult
    {
        $path = !empty($parameter['path']) ? $parameter['path'] : '/';

        if((bool)$parameter['format'] === true) {
            $total = \WapplerSystems\ZabbixClient\Utility\FormatUtility::formatBytes(disk_total_space($path));
            $free = \WapplerSystems\ZabbixClient\Utility\FormatUtility::formatBytes(disk_free_space($path));
        } else {
            $total = disk_total_space($path);
            $free = disk_free_space($path);
        }

        return new OperationResult(true, [[
            'total' => $total,
            'free' => $free,
        ]]);
    }
}
