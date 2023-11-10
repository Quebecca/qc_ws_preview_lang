<?php

namespace Qc\QcWsPreviewLang\Middleware;

/**
 * This file is part of the "qc_ws_preview_lang" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Workspaces\Middleware\WorkspacePreview;

class ExtendedWorkspacePreviewV10 extends WorkspacePreview{

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
        $langServiceFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);
        if($this->usedLanguage !== ''){
            return $langServiceFactory->create($this->usedLanguage);
        }
        return $GLOBALS['LANG'] ?: $langServiceFactory->create('default');
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
