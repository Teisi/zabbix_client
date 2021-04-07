<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\ViewHelpers;

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use \TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class StringViewHelper extends AbstractViewHelper {

    /**
     * apiKey
     *
     * @param array $config
     * @param \TYPO3\CMS\Core\ViewHelpers\Form\TypoScriptConstantsViewHelper $const
     * @return string
     */
    public function apiKey(array $config, \TYPO3\CMS\Core\ViewHelpers\Form\TypoScriptConstantsViewHelper $const)
    {
        return '
            <input type="text"
                id="em-'.$const->arguments['configuration']['extensionKey'].'-'.$config['fieldName'].'"
                name="'.$config['propertyName'].'"
                class="form-control"
                pattern="^([a-zA-Z0-9_-]){0,100}$"
                value="'.$config['fieldValue'].'"
            >';
    }
}
