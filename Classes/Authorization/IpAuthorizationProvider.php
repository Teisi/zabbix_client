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


class IpAuthorizationProvider
{

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
     * @param int $time - in minutes e. g. 5 = 5 minutes locked since last failed attempt
     * @param int $maxCount - how often a request may take place before it is blocked = returns true
     * @return bool
     */
    public function blockedIp(string $ip, int $time = 5, int $maxCount = 3)
    {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $lockRepository = $objectManager->get('WapplerSystems\\ZabbixClient\\Domain\\Repository\\LockRepository');
        $persistenceManager = $objectManager->get("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
        $lockRecord = $lockRepository->findByIp($ip)[0];

        // if ip is not listed then lock this record
        if(empty($lockRecord) || count($lockRecord) <= 0) {
            // $newRecord = new \WapplerSystems\ZabbixClient\Domain\Model\Lock();
            $newRecord = $objectManager->get('WapplerSystems\\ZabbixClient\\Domain\\Model\\Lock');
            $newRecord->setTstamp(new \DateTime());
            $newRecord->setIp($ip);
            $newRecord->setCount(1);

            $lockRepository->add($newRecord);
            $persistenceManager->persistAll();

            return false;
        }

        $recordCount = $lockRecord->getCount();

        // if the last entry is older than e. g. 5 minutes
        // don't block and set reset counter
        $oldDate = new \DateTime();
        $oldDate->modify('-'.$time.' minutes');
        if($lockRecord->getTstamp() <= $oldDate) {
            // $lockRecord->setTstamp(new \DateTime());
            $lockRecord->setCount(1);
            $lockRepository->update($lockRecord);
            $persistenceManager->persistAll();

            return false;
        }

        if($recordCount < $maxCount) {
            $newCount = $recordCount + 1;
            $lockRecord->setCount($newCount);
            $lockRepository->update($lockRecord);
            $persistenceManager->persistAll();

            return false;
        }

        // if($recordCount >= $maxCount) {
        //     return true;
        // }

        return true;
    }
}
