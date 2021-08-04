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
class FeLogRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    ];

    // Class Initialization (after all dependencies have been injected) (similar to __construct)
    public function initializeObject() {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings $querySettings */
        $querySettings = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(FALSE);
        $querySettings->setRespectSysLanguage(FALSE);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function deleteOldEntries(int $offset = 100) {
        $query = $this->createQuery();
        $query->setOffset($offset);

        return $query->execute();
    }

    // /**
    //  * deleteLocks
    //  * delte entries which are older then $time
    //  *
    //  * @param integer $time
    //  * @return int
    //  */
    // public function deleteLocks(int $time = 10)
    // {
    //     $connection = GeneralUtility::makeInstance(ConnectionPool::class)
    //         ->getConnectionForTable('tx_zabbixclient_domain_model_lock');
    //     $queryBuilder = $connection->createQueryBuilder();
    //     $count = $queryBuilder
    //         ->delete('tx_zabbixclient_domain_model_lock')
    //         ->where('tstamp < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '.$time.' DAY))')
    //         ->execute();

    //     return $count;
    // }
}
