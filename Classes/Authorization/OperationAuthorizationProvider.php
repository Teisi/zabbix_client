<?php

namespace WapplerSystems\ZabbixClient\Authorization;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use WapplerSystems\ZabbixClient\Authorization\AuthorizationProvider;
use WapplerSystems\ZabbixClient\Authorization\AuthorizationProviderInterface;


class OperationAuthorizationProvider extends AuthorizationProvider implements AuthorizationProviderInterface
{

    /**
     * given requested operation
     *
     * @var string
     */
    protected $operation = '';

    public function init(): void
    {
        $this->setOperation();
    }

    /**
     *  Check if the given param operation is allowed
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        if(empty($this->getOperation())) {
            return false;
        }

        if(!in_array($this->getOperation(), $this->config['operations']) && $this->config['operations'][$this->getOperation()] !== "1") {
            return false;
        }

        return true;
    }

    /**
     * returns the given operation
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * set $operation
     *
     * @return void
     */
    public function setOperation(): void
    {
        $this->operation = $this->getRequest()->getParsedBody()['operation'] ?? $this->getRequest()->getQueryParams()['operation'] ?? '';
    }
}
