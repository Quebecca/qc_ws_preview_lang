<?php

namespace Qc\QcWsPreviewLang\Middleware;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Workspaces\Middleware\WorkspacePreview;


class ExtendedWorkspacePreview extends WorkspacePreview{
    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getLanguageService(): LanguageService
    {

        $configurationManager= GeneralUtility::makeInstance(ConfigurationManager::class);
        $typoScriptConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,'qc_ws_preview_lang','tx_qc_ws_preview_lang');
        if(array_key_exists('used_language', $typoScriptConfiguration)){
            $langService = LanguageService::create($typoScriptConfiguration['used_language']);
            return  $langService;
        }

        return $GLOBALS['LANG'] ?: LanguageService::create('default');
    }
}