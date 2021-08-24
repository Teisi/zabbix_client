<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Authorization;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use WapplerSystems\ZabbixClient\Utility\Configuration;


class AuthorizationProvider
{
    /**
     * ServerRequest
     *
     * @var \TYPO3\CMS\Core\Http\ServerRequest
     */
    protected $request;

    /**
     * Extension configuration
     *
     * @var array
     */
    protected $config = [];

    public function __construct(\TYPO3\CMS\Core\Http\ServerRequest $request) {
        $this->setRequest($request);
        $this->setConfig();
        $this->init();
    }

    public function setRequest(\TYPO3\CMS\Core\Http\ServerRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setConfig()
    {
        $this->config = Configuration::getExtConfiguration();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function init()
    {
    }
}
