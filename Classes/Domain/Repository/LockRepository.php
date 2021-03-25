<?php
declare(strict_types=1);
namespace WapplerSystems\ZabbixClient\Domain\Repository;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Repository for tag objects
 */
class LockRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    ];

    /**
     * findLast
     * returns last $count lock records
     *
     * @param integer $count - how many rows to return
     * @return array
     */
    public function findLast(int $count = 10)
    {
        // Method with count-subquery maybe slower
        // $connection = GeneralUtility::makeInstance(ConnectionPool::class)
        //     ->getConnectionForTable('tx_zabbixclient_domain_model_lock');
        // $queryBuilder = $connection->createQueryBuilder();
        // $queryBuilderCount = $connection->createQueryBuilder();
        // $countRows = $queryBuilderCount
        //     ->count('uid')
        //     ->from('tx_zabbixclient_domain_model_lock')
        //     ->execute()->fetchColumn(0);

        // $queryBuilder
        //     ->select('*')
        //     ->from('tx_zabbixclient_domain_model_lock');

        // if($countRows > $count) {
        //     $queryBuilder
        //         ->setMaxResults($count)
        //         ->setFirstResult($countRows - $count);
        // }

        // $rows = $queryBuilder->execute()->fetchAll();

        // return $rows;

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_zabbixclient_domain_model_lock');
        $queryBuilder = $connection->createQueryBuilder();
        $rows = $queryBuilder
            ->select('*')
            ->from('tx_zabbixclient_domain_model_lock')
            ->orderBy('tstamp', 'DESC')
            ->addOrderBy('uid', 'DESC')
            ->setMaxResults($count)
            ->execute()->fetchAll();
        return $rows;
    }
}
