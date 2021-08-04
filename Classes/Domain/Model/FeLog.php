<?php
declare(strict_types=1);
namespace WapplerSystems\ZabbixClient\Domain\Model;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * FeLog model
 */
class FeLog extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var \DateTime
     */
    protected $tstamp;

    /**
     * @var int
     */
    protected $error;

    /**
     * @var string
     *
     */
    protected $logData;

    /**
     * @var string
     */
    protected $logMessage;

    /**
     * @var string
     */
    protected $details;

    /**
     * Get Tstamp
     *
     * @return \DateTime
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set tstamp
     *
     * @param \DateTime $tstamp
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
    }

    /**
     * Get error
     *
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set error
     *
     * @param int $error
     */
    public function setError(int $error)
    {
        $this->error = $error;
    }

    /**
     * Get $logData
     *
     * @return string
     */
    public function getLogData()
    {
        return $this->logData;
    }

    /**
     * Set $logData
     *
     * @param string $logData
     */
    public function setLogData(string $logData)
    {
        $this->logData = $logData;
    }

    /**
     * Get logMessage
     *
     * @return string
     */
    public function getLogMessage()
    {
        return $this->logMessage;
    }

    /**
     * Set logMessage
     *
     * @param string $logMessage
     */
    public function setLogMessage(string $logMessage)
    {
        $this->logMessage = $logMessage;
    }

    /**
     * Get details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set details
     *
     * @param string $details
     */
    public function setDetails(string $details)
    {
        $this->details = $details;
    }
}
