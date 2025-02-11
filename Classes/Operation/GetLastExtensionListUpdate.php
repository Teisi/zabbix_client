<?php

namespace WapplerSystems\ZabbixClient\Operation;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Task\UpdateExtensionListTask;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use WapplerSystems\ZabbixClient\OperationResult;
use WapplerSystems\ZabbixClient\Utility\FormatUtility;


class GetLastExtensionListUpdate implements IOperation, SingletonInterface
{

    public function execute($parameter = [])
    {
        // Should be the extensionmanager repository used?
        $useExtensionListRepo = empty((bool)$parameter['extensionlist']) ? false : true;

        if ($useExtensionListRepo) {
            $result = $this->getExtensionListLastUpdate();
            if(empty($result)) {
                return new OperationResult(true, 0);
            }

            if(!empty($parameter['format'])) {
                return new OperationResult(true, FormatUtility::formatDateTime($result, $parameter['format']));
            }

            return new OperationResult(true, (int)$result);
        }

        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            return new OperationResult(true, 0);
        }

        // @TODO: review if this is maybe deprectated? (getExtensionListLastUpdateScheduler())
        if(!empty($parameter['format'])) {
            return new OperationResult(true, FormatUtility::formatDateTime($this->getExtensionListLastUpdateScheduler(), $parameter['format']));
        }

        return new OperationResult(true, $this->getExtensionListLastUpdateScheduler());
    }

    /**
     * getExtensionListLastUpdate()
     * Get last extension list update from extensionmanager repository database table
     *
     * @return int
     */
    public function getExtensionListLastUpdate()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_extensionmanager_domain_model_repository');
        $queryBuilder->getRestrictions()->removeAll();
        $result = $queryBuilder->select('last_update')
            ->from('tx_extensionmanager_domain_model_repository')
            ->execute()->fetch();

        if(!empty($result) && is_array($result)) {
            return $result['last_update'];
        }

        return 0;
    }

    /**
     * getExtensionListLastUpdateScheduler()
     * Get last extension list update of the scheduler task
     * @TODO: review if this method can be deleted/mark as depricated
     *
     * @return void
     */
    public function getExtensionListLastUpdateScheduler()
    {
        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            return 0;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_scheduler_task');
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder->select('t.*')
            ->addSelect(
                'g.groupName AS taskGroupName',
                'g.description AS taskGroupDescription',
                'g.deleted AS isTaskGroupDeleted'
            )
            ->from('tx_scheduler_task', 't')
            ->leftJoin(
                't',
                'tx_scheduler_task_group',
                'g',
                $queryBuilder->expr()->eq('t.task_group', $queryBuilder->quoteIdentifier('g.uid'))
            );
        if (version_compare(TYPO3_version, '9.0.0', '>=')) {
            $result = $result->where(
                $queryBuilder->expr()->eq('t.deleted', 0)
            );
        }
        $result = $result->orderBy('g.sorting')
            ->execute();

        while ($task = $result->fetch()) {

            $taskObj = unserialize($task['serialized_task_object'], [AbstractTask::class]);
            if (get_class($taskObj) === UpdateExtensionListTask::class) {
                if (!empty($task['lastexecution_time'])) {
                    return (int)$task['lastexecution_time'];
                }
            }
        }

        return 0;
    }
}
