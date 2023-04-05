<?php

declare(strict_types=1);

/**
 * This file is part of the "qc_ws_preview_lang" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Qc\QcWsPreviewLang\Middleware;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Routing\UnableToLinkToPageException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Workspaces\Preview\PreviewUriBuilder;

/**
 * Create links to pages when in a workspace for previewing purposes
 *
 * @internal
 */
class ExtendedPreviewUriBuilder extends PreviewUriBuilder
{
    /**
     * Generates a workspace preview link.
     *
     * @param int $uid The ID of the record to be linked
     * @param int $languageId the language to link to
     * @return string the full domain including the protocol http:// or https://, but without the trailing '/'
     */
    public function buildUriForPage(int $uid, int $languageId = 0): string
    {
        $previewKeyword = $this->compilePreviewKeyword(
            $this->previewLinkLifetime * 3600,
            $this->workspaceService->getCurrentWorkspace()
        );

        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        try {
            $site = $siteFinder->getSiteByPageId($uid);
            try {
                $language = $site->getLanguageById($languageId);
            } catch (\InvalidArgumentException $e) {
                $language = $site->getDefaultLanguage();
            }

            $usedLanguage = BackendUtility::getPagesTSconfig($uid)['mod.']['qcWsPreviewLang.']['used_lang'];
            $uri = $site->getRouter()->generateUri($uid, ['pageLang'=> $usedLanguage, 'ADMCMD_prev' => $previewKeyword, '_language' => $language], '');
            return (string)$uri;
        } catch (SiteNotFoundException | InvalidRouteArgumentsException $e) {
            throw new UnableToLinkToPageException('The page ' . $uid . ' had no proper connection to a site, no link could be built.', 1559794916);
        }
    }

}
