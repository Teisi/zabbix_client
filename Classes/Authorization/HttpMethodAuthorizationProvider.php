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


class HttpMethodAuthorizationProvider extends AuthorizationProvider implements AuthorizationProviderInterface
{

    public function init(): void
    {
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        $allowedHttpMethods = explode('-', $this->config['httpMethod']);
        if(in_array($this->getRequest()->getMethod(), $allowedHttpMethods)) {
            return true;
        }

        return false;
    }
}
