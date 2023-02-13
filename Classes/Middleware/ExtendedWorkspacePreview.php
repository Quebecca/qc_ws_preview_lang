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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Workspaces\Middleware\WorkspacePreview;

class ExtendedWorkspacePreview extends WorkspacePreview {

    /**
     * @var string
     */
    protected string $usedLanguage='';

    /**
     * @var int
     */
    protected int $currentPage = 1;

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        $localServiceFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);
        if($this->usedLanguage !== ''){
            return $localServiceFactory->create($this->usedLanguage);
        }
        return $GLOBALS['LANG'] ?: $localServiceFactory->create('default');
    }

    /**
     * Initializes a possible preview user (by checking for GET/cookie of name "ADMCMD_prev")
     *
     * The GET parameter "ADMCMD_prev=LIVE" can be used to preview a live workspace from the backend even if the
     * backend user is in a different workspace.
     *
     * Additionally, if a workspace is previewed, an additional message text is shown.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->usedLanguage = $request->getQueryParams()['pageLang'] ?? '';
        return parent::process($request,$handler);
    }

}