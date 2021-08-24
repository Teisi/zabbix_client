<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\ViewHelpers;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ApiKeyViewHelper extends AbstractViewHelper {

    /**
     * apiKey
     *
     * @param array $config
     * @param $const
     * @return string
     */
    public function apiKey(array $config, $const)
    {
        return '
            <input type="text"
                id="em-'.$const->arguments['configuration']['extensionKey'].'-'.$config['fieldName'].'"
                name="'.$config['propertyName'].'"
                class="form-control"
                pattern="^([a-zA-Z0-9_-]){0,100}$"
                value="'.$config['fieldValue'].'"
            >
            <a id="zabbix-client-keyhashing" class="btn btn-default">Generate API Hash from given api-key</a>
            <script>
                require(["TYPO3/CMS/Backend/Notification", "TYPO3/CMS/Core/Ajax/AjaxRequest"], function (Notification, AjaxRequest) {
                    var button = document.querySelector("#zabbix-client-keyhashing");
                    var apikeyInput = document.querySelector("#em-'.$const->arguments['configuration']['extensionKey'].'-'.$config['fieldName'].'");
                    var apikeyHashed = document.querySelector("#em-'.$const->arguments['configuration']['extensionKey'].'-apiKeyHashed");

                    if(apikeyHashed.value == true) {
                        apikeyInput.removeAttribute("pattern");
                    }

                    if(button) {
                        button.addEventListener("click", function(e) {
                            e.preventDefault();
                            var apikey = apikeyInput.value;

                            new AjaxRequest(TYPO3.settings.ajaxUrls.zabbixclient_configuration_hashapikey)
                                .withQueryArguments({input: apikey})
                                .get()
                                .then(async function (response) {
                                    const resolved = await response.resolve();
                                    apikeyInput.value = resolved.result;
                                    apikeyInput.removeAttribute("pattern");
                                    Notification.success("Done", "API-key has been hashed.");
                                }, function (error) {
                                    console.error("Request failed because of error: "+ error + error.status + " " + error.statusText);
                                    Notification.error("Failed", "API-key could not be hashed - see console output!");
                                });
                        });
                    }
                });
            </script>';
    }
}
