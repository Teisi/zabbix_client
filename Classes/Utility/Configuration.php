<?php
namespace WapplerSystems\ZabbixClient\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class Configuration
{

    /**
     * @return array
     */
    public static function getExtConfiguration()
    {
        if (version_compare(TYPO3_version, '9.0.0', '>=')) {

            return GeneralUtility::makeInstance(ExtensionConfiguration::class)
                ->get('zabbix_client');
        }

        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['zabbix_client'])) {
            $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['zabbix_client']);

            return $extensionConfiguration;

        }
        return [];
    }


    /**
     * Get the whole typoscript array
     * @return array
     */
    public static function getTypoScriptConfiguration(): array
    {
        $configurationManager = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ConfigurationManagerInterface::class);

        return $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'zabbix_client'
        );
    }

    /**
     * setExtConfiguration
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setExtConfiguration(string $key, $value): void
    {
        $config = self::getExtConfiguration();
        $config[$key] = $value;
        GeneralUtility::makeInstance(ExtensionConfiguration::class)->set('zabbix_client', '', $config);
    }
}
