<?php

namespace WapplerSystems\ZabbixClient\Authentication;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use WapplerSystems\ZabbixClient\Utility\Configuration;


class KeyAuthenticationProvider
{

    /**
     * @param $key
     * @return bool
     */
    public function hasValidKey($key)
    {
        $config = Configuration::getExtConfiguration();

        if(!empty($config['apiKeyHashed']) && $config['apiKeyHashed'] === true) {
            // The context, either 'FE' or 'BE'
            $mode = 'BE';
            return GeneralUtility::makeInstance(PasswordHashFactory::class)
                ->get($config['apiKey'], $mode) # or getDefaultHashInstance($mode)
                ->checkPassword($key, $config['apiKey']);
        }

        return trim($config['apiKey']) === trim($key);
    }
}
