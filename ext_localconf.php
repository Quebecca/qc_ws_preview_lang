<?php

defined('TYPO3') || die();

/**
 * Override the original middleware just to add the new method of generate language service
 */

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Workspaces\Middleware\WorkspacePreview::class] = [
    'className' => \Qc\QcWsPreviewLang\Middleware\ExtendedWorkspacePreview::class
];