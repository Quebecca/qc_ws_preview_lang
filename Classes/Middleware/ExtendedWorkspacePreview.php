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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\RouteResultInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Workspaces\Authentication\PreviewUserAuthentication;
use TYPO3\CMS\Workspaces\Middleware\WorkspacePreview;


class ExtendedWorkspacePreview extends WorkspacePreview{


    protected string $usedLanguage='';
    protected int $currentPage = 1;
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

    /**
     * Renders the logout template when the "logout" button was pressed.
     * Returns a string which can be put into a HttpResponse.
     *
     * @param UriInterface $currentUrl
     * @return string
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getLogoutTemplateMessage(UriInterface $currentUrl): string
    {


        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId((int)$this->currentPage);
        $associatedResult = $this->getAssociatedPageUid($this->currentPage,$this->usedLanguage);
        $siteLanguage = $associatedResult['slUid'];
        $pageUid  = $associatedResult['uid'];
        $uri = $site->getRouter()->generateUri($pageUid, ['_language' => $siteLanguage]);
        $url = $currentUrl->getScheme().'://'.$currentUrl->getHost().$uri;

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
            $message = sprintf($message, '<a href="' . htmlspecialchars((string)$url) . '">', '</a>');
        }
        return sprintf($message, htmlspecialchars((string)$url));
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
        $addInformationAboutDisabledCache = false;
        $keyword = $this->getPreviewInputCode($request);
        $setCookieOnCurrentRequest = false;
        /** @var NormalizedParams $normalizedParams */
        $normalizedParams = $request->getAttribute('normalizedParams');
        $context = GeneralUtility::makeInstance(Context::class);

        $response = $handler->handle($request);
        if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController && $GLOBALS['TSFE']->isOutputting(true) && $context->getPropertyFromAspect('workspace', 'isOffline', false)) {
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $typoScriptConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'qc_ws_preview_lang', 'tx_qc_ws_preview_lang');
            $this->usedLanguage = $typoScriptConfiguration['used_language'] ?? '';
            $this->currentPage = $GLOBALS['TSFE']->id;
        }
        // First, if a Log out is happening, a custom HTML output page is shown and the request exits with removing
        // the cookie for the backend preview.
        if ($keyword === 'LOGOUT') {
            // "log out", and unset the cookie
            $message = $this->getLogoutTemplateMessage($request->getUri());
            $response = new HtmlResponse($message);
            return $this->addCookie('', $normalizedParams, $response);
        }

        // If the keyword is ignore, then the preview is not managed as "Preview User" but handled
        // via the regular backend user or even no user if the GET parameter ADMCMD_noBeUser is set
        if (!empty($keyword) && $keyword !== 'IGNORE' && $keyword !== 'LIVE') {
            $routeResult = $request->getAttribute('routing', null);
            // A keyword was found in a query parameter or in a cookie
            // If the keyword is valid, activate a BE User and override any existing BE Users
            // (in case workspace ID was given and a corresponding site to be used was found)
            $previewWorkspaceId = (int)$this->getWorkspaceIdFromRequest($request, $keyword);
            if ($previewWorkspaceId > 0 && $routeResult instanceof RouteResultInterface) {
                $previewUser = $this->initializePreviewUser($previewWorkspaceId);
                if ($previewUser instanceof PreviewUserAuthentication) {
                    $GLOBALS['BE_USER'] = $previewUser;
                    // Register the preview user as aspect
                    $this->setBackendUserAspect($context, $previewUser);
                    // If the GET parameter is set, and we have a valid Preview User, the cookie needs to be
                    // set and the GET parameter should be removed.
                    $setCookieOnCurrentRequest = $request->getQueryParams()[$this->previewKey] ?? false;
                }
            }
        }

        // If keyword is set to "LIVE", then ensure that there is no workspace preview, but keep the BE User logged in.
        // This option is solely used to ensure that a be user can preview the live version of a page in the
        // workspace preview module.
        if ($keyword === 'LIVE' && $GLOBALS['BE_USER'] instanceof FrontendBackendUserAuthentication) {
            // We need to set the workspace to live here
            $GLOBALS['BE_USER']->setTemporaryWorkspace(0);
            // Register the backend user as aspect
            $this->setBackendUserAspect($context, $GLOBALS['BE_USER']);
            // Caching is disabled, because otherwise generated URLs could include the keyword parameter
            $request = $request->withAttribute('noCache', true);
            $addInformationAboutDisabledCache = true;
            $setCookieOnCurrentRequest = false;
        }

        if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController && $addInformationAboutDisabledCache) {
            $GLOBALS['TSFE']->set_no_cache('GET Parameter ADMCMD_prev=LIVE was given', true);
        }

        // Add an info box to the frontend content
        if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController && $GLOBALS['TSFE']->isOutputting(true) && $context->getPropertyFromAspect('workspace', 'isOffline', false)) {
            $previewInfo = $this->renderPreviewInfo($GLOBALS['TSFE'], $request->getUri());
            $body = $response->getBody();
            $body->rewind();
            $content = $body->getContents();
            $content = str_ireplace('</body>', $previewInfo . '</body>', $content);
            $body = new Stream('php://temp', 'rw');
            $body->write($content);
            $response = $response->withBody($body);
        }

        // If the GET parameter ADMCMD_prev is set, then a cookie is set for the next request to keep the preview user
        if ($setCookieOnCurrentRequest) {
            $response = $this->addCookie($keyword, $normalizedParams, $response);
        }
        return $response;
    }

    public function getAssociatedPageUid($pageUid, $langCode){
        // uid de la page
        // lang, uid or pid
        /*
        select pages.uid
        from pages
        where uid = $uid
        OR pid = $uid
        inner join sys_language
        on
        sys_language.code  = $code
        */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder =  $connectionPool->getQueryBuilderForTable('pages');

        return  $queryBuilder
                    ->select('pages.uid', 'sl.uid AS slUid')
                    ->from('pages')
                    ->join(
                        'pages',
                        'sys_language',
                        'sl',
                        "sl.language_isocode like '$langCode'"
                    )
                    ->orWhere(
                        $queryBuilder->expr()->eq(
                            'pages.uid',
                            $queryBuilder->createNamedParameter($pageUid,\PDO::PARAM_INT)),
                        $queryBuilder->expr()->eq(
                            'pages.pid',
                            $queryBuilder->createNamedParameter($pageUid,\PDO::PARAM_INT)),
                    )
                    ->execute()
                    ->fetchAssociative();


    }
}