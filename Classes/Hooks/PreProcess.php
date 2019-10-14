<?php
namespace StefanFroemken\UrlRedirect\Hooks;

/*
 * This file is part of the url_redirect project.
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

use StefanFroemken\UrlRedirect\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * Class PreProcess
 *
 * @package StefanFroemken\UrlRedirect\Hooks
 */
class PreProcess
{
    /**
     * Redirect request
     *
     * @param array $parameters Should be empty
     * @param array $parent Should be empty
     *
     * @return void
     */
    public function redirect(array $parameters, array $parent)
    {
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');
        $target = $this->findRedirect($requestUri);
        if (empty($target)) {
            return;
        }
        $httpStatus = constant(HttpUtility::class . '::HTTP_STATUS_' . (int)$target['http_status']);
        if (empty($httpStatus)) {
            // constant is not defined
            return;
        }
        HttpUtility::redirect($target['target_uri'], $httpStatus);
    }

    /**
     * Try various methods to find a match for requested URI
     *
     * @param string $requestUri
     *
     * @return array
     */
    protected function findRedirect($requestUri)
    {
        $target = $this->findDirectRedirect($requestUri);
        if (empty($target)) {
            $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['url_redirect']);
            if ($extConf['useRegExp']) {
                $target = $this->findRegExpRedirect($requestUri);
            }
        }

        return $target;
    }

    /**
     * Find direct redirect
     * We found a 1 to 1 match in DB
     * That's the fastest method
     *
     * @param string $requestUri
     *
     * @return array
     */
    protected function findDirectRedirect($requestUri)
    {
        $sysDomainUid = $this->getSysDomainUid();
        $where = [];
        $where[] = 'hidden=0';
        if ($sysDomainUid) {
            $where[] = '(domain=0 OR domain=' . (int)$sysDomainUid . ')';
        }

        // we sort by complete domain. So, if there is a record this one has priority
        $redirects = DatabaseUtility::getRecordsByField(
            'tx_urlredirect_domain_model_config',
            'hidden',
            '0',
            sprintf(
                ' AND ((use_reg_exp=0 AND request_uri=%s) OR complete_domain=1) AND ' . implode(' AND ', $where),
                $GLOBALS['TYPO3_DB']->fullQuoteStr($requestUri, 'tx_urlredirect_domain_model_config')
            ),'', 'complete_domain ASC'
        );

        if (empty($redirects)) {
            return [];
        }

        return current($redirects);
    }

    /**
     * Find redirect by RegExp
     *
     * @param string $requestUri
     *
     * @return array
     */
    protected function findRegExpRedirect($requestUri)
    {
        $redirects = DatabaseUtility::getRecordsByField(
            'tx_urlredirect_domain_model_config',
            'use_reg_exp',
            '1',
            ' AND hidden=0 AND deleted=0'
        );
        if (empty($redirects)) {
            return [];
        }

        $redirect = [];
        foreach ($redirects as $redirect) {
            if (preg_match('@' . $redirect['request_uri'] . '@', $requestUri)) {
                $redirect['target_uri'] = preg_replace(
                    '@' . $redirect['request_uri'] . '@',
                    $redirect['target_uri'],
                    $requestUri
                );
                break;
            } else {
                $redirect = [];
            }
        }
        return $redirect;
    }

    /**
     * Try to find a sysDomain record that matches current request
     *
     * @return int
     */
    protected function getSysDomainUid()
    {
        $domain = DatabaseUtility::getRecordRaw(
            'sys_domain',
            sprintf(
                'domainName=%s AND redirectTo=\'\' AND hidden=0',
                $GLOBALS['TYPO3_DB']->fullQuoteStr(
                    GeneralUtility::getIndpEnv('HTTP_HOST'),
                    'tx_urlredirect_domain_model_config'
                )
            ),
            'uid'
        );

        if (empty($domain)) {
            return 0;
        } else {
            return (int)$domain['uid'];
        }
    }
}
