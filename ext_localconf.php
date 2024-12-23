<?php

defined('TYPO3') || die();

/**
 * Override the original middleware just to add the new method of generate language service
 */
$typoVersion = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Workspaces\Middleware\WorkspacePreview::class] = [
    'className' => \Qc\QcWsPreviewLang\Middleware\ExtendedWorkspacePreview::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Workspaces\Preview\PreviewUriBuilder::class] = [
    'className' => \Qc\QcWsPreviewLang\Middleware\ExtendedPreviewUriBuilder::class
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    "@import 'EXT:qc_ws_preview_lang/Configuration/TSconfig/pageconfig.tsconfig'"
);
