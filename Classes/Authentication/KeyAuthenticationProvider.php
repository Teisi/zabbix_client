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
        $this->request = $request;
        $this->config = Configuration::getExtConfiguration();
    }

    /**
     * @return bool
     */
    public function hasValidKey(): bool
    {
        $accessMethod = $this->config['accessMethod'];
        switch (intval($accessMethod)) {
            case 1:
                $key = $this->request->getHeaders()['api-key'][0];
                break;

            default:
                $key = $this->request->getParsedBody()['key'] ?? $this->request->getQueryParams()['key'] ?? null;
                break;
        }

        if(!empty($this->config['apiKeyHashed']) && $this->config['apiKeyHashed'] === true) {
            // The context, either 'FE' or 'BE'
            $mode = 'BE';
            return GeneralUtility::makeInstance(PasswordHashFactory::class)
                ->get($this->config['apiKey'], $mode) # or getDefaultHashInstance($mode)
                ->checkPassword($key, $this->config['apiKey']);
        }

        return trim($this->config['apiKey']) === trim($key);
    }
}
