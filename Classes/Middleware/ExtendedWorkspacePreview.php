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

use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Workspaces\Middleware\WorkspacePreview;


class ExtendedWorkspacePreview extends WorkspacePreview{
    protected string $usedLanguage;
    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getLanguageService(): LanguageService
    {

        $configurationManager= GeneralUtility::makeInstance(ConfigurationManager::class);
        $typoScriptConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,'qc_ws_preview_lang','tx_qc_ws_preview_lang');
        $this->usedLanguage = $typoScriptConfiguration['used_language'];
        if(array_key_exists('used_language', $typoScriptConfiguration)){
            $langService = LanguageService::create($typoScriptConfiguration['used_language']);
            return  $langService;
        }

        return $GLOBALS['LANG'] ?: LanguageService::create('default');
    }

    /**
     * Renders the logout template when the "logout" button was pressed.
     * Returns a string which can be put into a HttpResponse.
     *
     * @param UriInterface $currentUrl
     * @return string
     */
    protected function getLogoutTemplateMessage(UriInterface $currentUrl): string
    {
        $langService = LanguageService::create($this->usedLanguage);

        $currentUrl = $this->removePreviewParameterFromUrl($currentUrl);
        if ($GLOBALS['TYPO3_CONF_VARS']['FE']['workspacePreviewLogoutTemplate']) {
            $templateFile = GeneralUtility::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['FE']['workspacePreviewLogoutTemplate']);
            if (@is_file($templateFile)) {
                $message = (string)file_get_contents($templateFile);
            } else {
                $message = $langService->sL('LLL:EXT:workspaces/Resources/Private/Language/locallang_mod.xlf:previewLogoutError');
                $message = htmlspecialchars($message);
                $message = sprintf($message, '<strong>', '</strong><br>', $templateFile);
            }
        } else {
            $message = $langService->sL('LLL:EXT:workspaces/Resources/Private/Language/locallang_mod.xlf:previewLogoutSuccess');
            $message = htmlspecialchars($message);
            $message = sprintf($message, '<a href="' . htmlspecialchars((string)$currentUrl) . '">', '</a>');
        }
        return sprintf($message, htmlspecialchars((string)$currentUrl));
    }

}