<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Authorization;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


interface AuthorizationProviderInterface
{
    public function init(): void;
    public function isAuthorized(): bool;
}
