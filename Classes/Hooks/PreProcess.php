<?php
namespace StefanFroemken\UrlRedirect\Hooks;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

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
        $requestUri = trim(GeneralUtility::getIndpEnv('REQUEST_URI'), '/');
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
        if (class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_urlredirect_domain_model_config');
            $statement = $queryBuilder
                ->select('target_uri', 'http_status')
                ->from('tx_urlredirect_domain_model_config', 'c')
                ->where($queryBuilder->expr()->eq(
                    'use_reg_exp',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))
                )
                ->andWhere($queryBuilder->expr()->eq(
                    'request_uri',
                    $queryBuilder->createNamedParameter(htmlspecialchars('/' . $requestUri), \PDO::PARAM_STR))
                )->execute();
            $row = $statement->fetch();
        } else {
            $sysDomainUid = $this->getSysDomainUid();
            $where = [];
            $where[] = 'use_reg_exp=0';
            if ($sysDomainUid) {
                $where[] = '(domain=0 OR domain=' . (int)$sysDomainUid . ')';
            }
            $where[] = sprintf(
                'request_uri=%s',
                $this->getDatabaseConnection()->fullQuoteStr(
                    htmlspecialchars('/' . $requestUri),
                    'tx_urlredirect_domain_model_config'
                )
            );

            $row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                'target_uri, http_status',
                'tx_urlredirect_domain_model_config',
                implode(' AND ', $where)
            );
        }

        if (empty($row)) {
            return [];
        }

        return $row;
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
        $row = [];
        if (class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_urlredirect_domain_model_config');
            $statement = $queryBuilder
                ->select('request_uri', 'target_uri', 'http_status')
                ->from('tx_urlredirect_domain_model_config', 'c')
                ->where($queryBuilder->expr()->eq(
                    'use_reg_exp',
                    $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT))
                )->execute();
            $rows = $statement->fetchAll();
        } else {
            $rows = $this->getDatabaseConnection()->exec_SELECTgetRows(
                'request_uri, target_uri, http_status',
                'tx_urlredirect_domain_model_config',
                'use_reg_exp=1'
            );
        }
        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (preg_match('@' . $row['request_uri'] . '@', $requestUri)) {
                    $row['target_uri'] = preg_replace(
                        '@' . $row['request_uri'] . '@',
                        $row['target_uri'],
                        $requestUri
                    );
                    break;
                } else {
                    $row = [];
                }
            }
        }
        return $row;
    }

    /**
     * Try to find a sysDomain record that matches current request
     *
     * @return int
     */
    protected function getSysDomainUid()
    {
        $sysDomain = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            'uid',
            'sys_domain',
            sprintf(
                'domainName=%s AND hidden=0 AND redirectTo=\'\'',
                $this->getDatabaseConnection()->fullQuoteStr(
                    GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'),
                    'sys_domain'
                )
            )
        );
        if (empty($sysDomain)) {
            return 0;
        } else {
            return $sysDomain['uid'];
        }
    }

    /**
     * Get TYPO3s Database Connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

}
