<?php

namespace WapplerSystems\ZabbixClient\Authorization;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\ZabbixClient\Utility\Configuration;
use WapplerSystems\ZabbixClient\Domain\Repository\LockRepository;

class IpAuthorizationProvider
{
    /**
     * maxCount
     * maximum number of attempts
     *
     * @var integer
     */
    public $maxCount = 3;

    /**
     * @var LockRepository
     */
    protected $lockRepository = null;

    /**
     * @param string $ip
     * @return bool
     */
    public function isAuthorized($ip)
    {
        $config = Configuration::getExtConfiguration();
        $allowedIps = trim($config['allowedIps'] ?? '');
        return !$allowedIps || GeneralUtility::cmpIP($ip, $allowedIps);
    }


    /**
     * blockedIp()
     * checks if ip should be blocked for access
     *
     * @param string $ip
     * @return bool
     */
    public function blockedIp(string $ip)
    {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->lockRepository = $objectManager->get('WapplerSystems\\ZabbixClient\\Domain\\Repository\\LockRepository');
        $persistenceManager = $objectManager->get("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
        $lockRecord = $this->lockRepository->findByIp($ip)[0];

        // if ip is not listed then lock this record
        if(empty($lockRecord) || count($lockRecord) <= 0) {
            // $newRecord = new \WapplerSystems\ZabbixClient\Domain\Model\Lock();
            $newRecord = $objectManager->get('WapplerSystems\\ZabbixClient\\Domain\\Model\\Lock');
            $newRecord->setTstamp(new \DateTime());
            $newRecord->setIp($ip);
            $newRecord->setCount(1);

            $this->lockRepository->add($newRecord);
            $persistenceManager->persistAll();

            return false;
        }

        $recordCount = $lockRecord->getCount();

        // if the last entry is older than e. g. 5 minutes
        $oldDate = new \DateTime();
        $oldDate->modify('-5 minutes');
        if($lockRecord->getTstamp() <= $oldDate) {
            // $lockRecord->setTstamp(new \DateTime());
            $lockRecord->setCount(1);
            $this->lockRepository->update($lockRecord);
            $persistenceManager->persistAll();

            return false;
        }

        if($recordCount < $this->maxCount) {
            $newCount = $recordCount + 1;
            $lockRecord->setCount($newCount);
            $this->lockRepository->update($lockRecord);
            $persistenceManager->persistAll();

            return false;
        }

        if($recordCount >= $this->maxCount) {
            return true;
        }

        return false;
    }
}
