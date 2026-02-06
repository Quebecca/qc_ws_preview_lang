<?php

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Workspaces\Middleware\WorkspacePreview::class] = [
    'className' => \Qc\QcWsPreviewLang\Middleware\ExtendedWorkspacePreview::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Workspaces\Preview\PreviewUriBuilder::class] = [
    'className' => \Qc\QcWsPreviewLang\Middleware\ExtendedPreviewUriBuilder::class
];
