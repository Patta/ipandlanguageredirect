<?php

namespace In2code\Ipandlanguageredirect\Controller;

use In2code\Ipandlanguageredirect\Domain\Service\RedirectService;
use In2code\Ipandlanguageredirect\Utility\FrontendUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class RedirectController
 */
class RedirectController extends ActionController
{
    /**
     * @var array
     */
    protected $testArguments = [
        [
            'browserLanguage' => 'de',
            'referrer' => 'http://www.google.de?foo=bar',
            'ipAddress' => '192.168.0.1',
            'languageUid' => '0',
            'rootpageUid' => '1',
        ],
        [
            'browserLanguage' => 'de',
            'referrer' => 'http://www.google.de?foo=bar',
            'ipAddress' => '',
            'languageUid' => '0',
            'rootpageUid' => '1',
        ],
    ];

    /**
     * Can be tested with a direct call:
     *      index.php?id=2&type=1555
     *      &tx_ipandlanguageredirect_pi1[browserLanguage]=de
     *      &tx_ipandlanguageredirect_pi1[referrer]=http://google.de/
     *      &tx_ipandlanguageredirect_pi1[ipAddress]=66.85.131.18
     *      &tx_ipandlanguageredirect_pi1[languageUid]=0
     *      &tx_ipandlanguageredirect_pi1[rootpageUid]=1
     *      &tx_ipandlanguageredirect_pi1[domain]=www.domain.org
     *
     * @param string $browserLanguage browser language
     * @param string $referrer referrer address
     * @param string $ipAddress given IP address
     * @param int $languageUid current FE language uid
     * @param int $rootpageUid current rootpage uid
     * @param string $countryCode overrides ip2country function for testing
     * @param string $domain overrides domain function for testing if given
     * @return string
     */
    public function redirectAction(
        string $browserLanguage = '',
        string $referrer = '',
        string $ipAddress = '',
        int $languageUid = 0,
        int $rootpageUid = 1,
        string $countryCode = '',
        string $domain = ''
    ): ResponseInterface {
        $redirectService = new RedirectService(
            $browserLanguage,
            $referrer,
            empty($ipAddress) ? GeneralUtility::getIndpEnv('REMOTE_ADDR') : $ipAddress,
            $languageUid,
            $rootpageUid,
            $countryCode,
            $domain
        );

        return $this->jsonResponse(json_encode($redirectService->buildParameters()));
    }

    /**
     * Test the redirectAction directly with some predefined parameters from a given set
     *      call index.php?id=2&type=1556&tx_ipandlanguageredirect_pi1[set]=1
     *
     * @param int $set
     * @return void
     */
    public function testAction($set = 0): ResponseInterface
    {
        $configuration = [
            'parameter' => $this->request->getAttribute('routing')->getPageId(),
            'additionalParams' =>
                FrontendUtility::getParametersStringFromArray($this->testArguments[$set]) . '&type=1555',
        ];

        /** @var ContentObjectRenderer $contentObject */
        $contentObject = $this->request->getAttribute('currentContentObject');
        $uri = $contentObject->typoLink_URL($configuration);

        return $this->redirectToUri($uri, 0, 307);
    }

    /**
     * Render a suggest container that can be slided down in FE
     *
     * @return void
     */
    public function suggestAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }
}
