<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\ViewHelpers;

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use \TYPO3\CMS\Core\ViewHelpers\Form\TypoScriptConstantsViewHelper;

class ApiKeyViewHelper extends AbstractViewHelper {

    /**
     * apiKey
     *
     * @param array $config
     * @param TypoScriptConstantsViewHelper $const
     * @return string
     */
    public function apiKey(array $config, TypoScriptConstantsViewHelper $const)
    {
        return '
            <input type="text"
                id="em-'.$const->arguments['configuration']['extensionKey'].'-'.$config['fieldName'].'"
                name="'.$config['propertyName'].'"
                class="form-control"
                pattern="^([a-zA-Z0-9_-]){0,100}$"
                value="'.$config['fieldValue'].'"
            >
            <a id="zabbix-client-keyhashing" class="btn btn-default">Generate API Hash</a>
            <script>
                require(["TYPO3/CMS/Core/Ajax/AjaxRequest"], function (AjaxRequest) {
                    var button = document.querySelector("#zabbix-client-keyhashing");
                    if(button) {
                        button.addEventListener("click", function(e) {
                            e.preventDefault();
                            var apikeyInput = document.querySelector("#em-'.$const->arguments['configuration']['extensionKey'].'-'.$config['fieldName'].'");
                            var apikeyHashed = document.querySelector("#em-'.$const->arguments['configuration']['extensionKey'].'-em-zabbix_client-apiKeyHashed");
                            var apikey = apikeyInput.value;

                            new AjaxRequest(TYPO3.settings.ajaxUrls.zabbixclient_configuration_hashapikey)
                                .withQueryArguments({input: apikey})
                                .get()
                                .then(async function (response) {
                                    const resolved = await response.resolve();
                                    apikeyInput.value = resolved.result;
                                }, function (error) {
                                    console.error("Request failed because of error: "+ error + error.status + " " + error.statusText);
                                });
                        });
                    }
                });
            </script>';
    }
}
