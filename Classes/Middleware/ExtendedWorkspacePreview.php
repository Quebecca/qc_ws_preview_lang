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
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Routing\RouteResultInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Workspaces\Authentication\PreviewUserAuthentication;
use TYPO3\CMS\Workspaces\Middleware\WorkspacePreview;


class ExtendedWorkspacePreview extends WorkspacePreview{

    protected string $usedLanguage='';
    protected int $currentPage = 1;
    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        if($this->usedLanguage !== ''){
            $langService = LanguageService::create($this->usedLanguage);
            return  $langService;
        }
        return $GLOBALS['LANG'] ?: LanguageService::create('default');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->usedLanguage = $request->getQueryParams()['pageLang'] ?? '';

        $port = $request->getUri()->getPort();
        $matchedRedirect = $this->redirectService->matchRedirect(
            $request->getUri()->getHost() . ($port ? ':' . $port : ''),
            $request->getUri()->getPath(),
            $request->getUri()->getQuery()
        );

        // If the matched redirect is found, resolve it, and check further
        if (is_array($matchedRedirect)) {
            $url = $this->redirectService->getTargetUrl($matchedRedirect, $request);
            if ($url instanceof UriInterface) {
                if ($this->redirectUriWillRedirectToCurrentUri($request, $url)) {
                    if ($url->getFragment()) {
                        // Enrich error message for unsharp check with target url fragment.
                        $this->logger->error('Redirect ' . $url->getPath() . ' eventually points to itself! Target with fragment can not be checked and we take the safe check to avoid redirect loops. Aborting.', ['record' => $matchedRedirect, 'uri' => (string)$url]);
                    } else {
                        $this->logger->error('Redirect ' . $url->getPath() . ' points to itself! Aborting.', ['record' => $matchedRedirect, 'uri' => (string)$url]);
                    }
                    return $handler->handle($request);
                }
                $this->logger->debug('Redirecting', ['record' => $matchedRedirect, 'uri' => (string)$url]);
                $response = $this->buildRedirectResponse($url, $matchedRedirect);
                $this->incrementHitCount($matchedRedirect);

                return $response;
            }
        }

        return $handler->handle($request);
    }

}