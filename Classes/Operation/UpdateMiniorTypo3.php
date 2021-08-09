<?php

namespace WapplerSystems\ZabbixClient\Operation;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Controller\EnvironmentController;
use WapplerSystems\ZabbixClient\OperationResult;


/**
 *
 */
class UpdateMiniorTypo3 implements IOperation, SingletonInterface
{
    /**
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * @var ServerRequest
     */
    protected $serverRequest;

    /**
     * @var EnvironmentController
     */
    protected $environmentController;

    public function __construct(ServerRequest $serverRequest) {
        $this->serverRequest = $serverRequest;
        $this->environmentController = GeneralUtility::makeInstance(EnvironmentController::class);
    }

    /**
     *
     * @param array $parameter None
     * @return OperationResult
     */
    public function execute($parameter = [])
    {
        $this->request = $parameter['request'];
        $this->initTSFE();

        /** @var \TYPO3\CMS\Install\Controller\UpgradeController $upgradeController */
        $upgradeController = GeneralUtility::makeInstance(\TYPO3\CMS\Install\Controller\UpgradeController::class);

        $coreUpdateIsUpdateAvailable = $upgradeController->coreUpdateIsUpdateAvailableAction();

        if($coreUpdateIsUpdateAvailable->getStatusCode() === 200) {
            $content = $coreUpdateIsUpdateAvailable->getBody()->getContents();
            if($content) {
                $jsonResponse = json_decode($content, true);

                if($jsonResponse['success'] && $jsonResponse['action']['action'] === 'updateRegular') {
                    $folderStructureStatus = $this->environmentController->folderStructureGetStatusAction($this->request);
                    $folderStructureStatusContent = json_decode($folderStructureStatus->getBody()->getContents(), true);

                    if(!empty($folderStructureStatusContent['errorStatus'])) {
                        try {
                            $this->environmentController->folderStructureFixAction();
                        } catch (\Throwable $th) {
                            return new OperationResult(false, $th);
                        }
                    }

                    $this->request = $this->request->withQueryParams([
                        'install' => [
                            'type' => 'regular'
                        ]
                    ]);
                    $checkPreConditions = $upgradeController->coreUpdateCheckPreConditionsAction($this->request);
                    if($checkPreConditions->getStatusCode() === 200) {
                        $checkPreConditionsJson = json_decode($checkPreConditions->getBody()->getContents(), true);
                        if($checkPreConditionsJson['success']) {
                            // coreUpdateDownloadAction
                            // coreUpdateVerifyChecksum
                            // coreUpdateUnpack
                            // coreUpdateMove
                            // coreUpdateActivate
                        }

                        return new OperationResult(true, $checkPreConditionsJson['status']);
                    }
                }
            }

            return new OperationResult(true, false);
        }

        // TYPO3\CMS\Install\Command\UpgradeWizardRunCommand::runAllWizards()

        // TYPO3\CMS\Install\Controller\UpgradeController::upgradeWizardsListAction()

        // TYPO3\CMS\Install\Controller\UpgradeController -> coreUpdateActivateAction

        return new OperationResult(true, false);
    }

    protected function initTSFE($typeNum = 0) {
        $uid = 1;
        // \TYPO3\CMS\Frontend\Utility\EidUtility::initTCA();
        $site = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Site\SiteFinder::class)->getSiteByRootPageId($uid);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute('language', $site->getLanguageById(0));
        $GLOBALS['TSFE']->id = $uid;

        /** @var TypoScriptFrontendController $GLOBALS['TSFE'] */
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            $uid,
            $typeNum
        );

        // /** @var GeneralUtility sys_page */
        // $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        // $GLOBALS['TSFE']->sys_page->init(true);

        // $GLOBALS['TSFE']->connectToDB();
        // $GLOBALS['TSFE']->initFEuser();
        // $GLOBALS['TSFE']->determineId();
        // $GLOBALS['TSFE']->initTemplate();

        // $GLOBALS['TSFE']->rootLine = $this->rootLine;
        // $GLOBALS['TSFE']->getConfigArray();
    }
}
