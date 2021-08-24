<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Authorization;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use WapplerSystems\ZabbixClient\Authorization\AuthorizationProvider;
use WapplerSystems\ZabbixClient\Authorization\AuthorizationProviderInterface;


class IpAuthorizationProvider extends AuthorizationProvider implements AuthorizationProviderInterface
{

    /**
     * Ip
     *
     * @var string
     */
    protected $ip;

    /**
     * $objectManager
     *
     * @var TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * $persistenceManager
     *
     * @var TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager $persistenceManager
     */
    protected $persistenceManager;

    /**
     * $lockRepository
     *
     * @var WapplerSystems\\ZabbixClient\\Domain\\Repository\\LockRepository $lockRepository
     */
    protected $lockRepository;

    /**
     * $blockedRecord
     * from Database
     *
     * @var WapplerSystems\ZabbixClient\Domain\Model\Lock $blockedRecord
     */
    protected $blockedRecord;

    public function init(): void
    {
        $this->setIp();
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        $this->lockRepository = $this->objectManager->get('WapplerSystems\\ZabbixClient\\Domain\\Repository\\LockRepository');
        $this->blockedRecord = $this->getBlockedRecordByIp($this->getIp());
    }

    /**
     * check if given IP address is allowed
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        $allowedIps = trim($this->getConfig()['allowedIps']);
        if($this->validateIps($allowedIps) && $this->checkIfBlockedIp() === false) {
            return true;
        }

        return false;
    }

    /**
     * blockedIp()
     * checks if ip should be blocked for access
     * returns true if record is blocked
     *
     * @param int $time - in minutes e. g. 5 = 5 minutes locked since last failed attempt
     * @param int $maxCount - how often a request may take place before it is blocked = returns true
     * @return bool
     */
    public function checkIfBlockedIp(int $time = 5, int $maxCount = 3): bool
    {
        if($this->isAllowedIp()) {
            // if no entry given for this ip
            if(empty($this->blockedRecord)) {
                return false;
            }

            // if the last entry is older than e. g. 5 minutes
            // don't block and set reset counter
            $oldDate = new \DateTime();
            $oldDate->modify('-'.$time.' minutes');
            if($this->blockedRecord->getTstamp() <= $oldDate) {
                $this->setBlockedIp(1);

                return false;
            }

            // if there are less than $maxCount entries then increase the counter by one
            $recordCount = $this->blockedRecord->getCount();
            if($recordCount < $maxCount) {
                $newCount = $recordCount + 1;
                $this->setBlockedIp($newCount);

                return false;
            }
        }

        $recordCount = 1;
        if(!empty($this->blockedRecord)) {
            $recordCount = $this->blockedRecord->getCount() + 1;
        }

        $this->setBlockedIp($recordCount);

        return true;
    }

    /**
     * validateIps
     * check if given input contains only IPs or '*'
     *
     * @return void
     */
    public function validateIps(): bool
    {
        $allowedIps = $this->getAllowedIps();
        if($allowedIps === '*') {
            return true;
        }

        // check if given ip(string) contains only IP's
        $ipArray = \explode(',', $allowedIps);
        foreach ($ipArray as $ip) {
            if(filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * isAllowedIp
     * checks if given ip is in the allowedIp list from extension config
     *
     * @return boolean
     */
    public function isAllowedIp(): bool
    {
        $allowedIps = $this->getAllowedIps();
        if($allowedIps === '*') {
            return true;
        }

        $ipArray = \explode(',', $allowedIps);
        if(\in_array($this->getIp(), $ipArray)) {
            return true;
        }

        return false;
    }

    /**
     * setIp
     *
     * @return void
     */
    public function setIp(): void
    {
        $this->ip = $this->getRequest()->getAttributes()['normalizedParams']->getRemoteAddress();
    }

    /**
     * getIp
     *
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * getAllowedIps
     * returns the allowed ips which are set in the extensions config
     *
     * @return string
     */
    public function getAllowedIps(): string
    {
        return trim($this->getConfig()['allowedIps']);
    }

    /**
     * setBlockedIp
     * check if a record exists allready
     * if then update it else create a new record
     *
     * @param int $count
     * @return void
     */
    public function setBlockedIp(int $count = 1)
    {
        // if no record exists for this ip address then create a new record
        if(empty($this->blockedRecord)) {
            $newRecord = $this->objectManager->get('WapplerSystems\\ZabbixClient\\Domain\\Model\\Lock');
            $newRecord->setTstamp(new \DateTime());
            $newRecord->setIp($this->getIp());
            $newRecord->setCount($count);
            $this->lockRepository->add($newRecord);
        } else {
            $this->blockedRecord->setCount($count);
            $this->lockRepository->update($this->blockedRecord);
        }

        $this->persistenceManager->persistAll();
    }

    /**
     * getBlockedRecordByIp
     * get record by IP from database
     *
     * @param string $ip
     */
    public function getBlockedRecordByIp(string $ip)
    {
        return $this->lockRepository->findByIp($ip)[0];
    }
}
