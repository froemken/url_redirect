<?php

namespace StefanFroemken\UrlRedirect\Domain\Repository;

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
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConfigRepository extends Repository
{
    /**
     * Get all HTTP status constants of HttpUtility
     *
     * @return array
     */
    public function getHttpStatus()
    {
        $httpStatus = [];
        $httpReflection = new \ReflectionClass(HttpUtility::class);
        $constants = $httpReflection->getConstants();
        foreach ($constants as $constant => $value) {
            if (StringUtility::beginsWith($constant, 'HTTP_STATUS_')) {
                $status = str_replace('HTTP_STATUS_', '', $constant);
                $httpStatus[$status] = $value;
            }
        }

        return $httpStatus;
    }

    /**
     * Get all SYS Domains configured in TYPO3
     *
     * @return array
     */
    public function getSysDomains()
    {
        $sysDomains = DatabaseUtility::getRecordsByField(
            'sys_domain',
            'redirectTo',
            '',
            ' AND hidden=0',
            '', 'domainName ASC'
        );

        if (empty($sysDomains)) {
            $sysDomains = [];
        }

        $domains = [];
        foreach ($sysDomains as $domain) {
            $domains[$domain['uid']] = $domain['domainName'];
        }

        return $domains;
    }
}
