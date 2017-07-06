<?php

namespace JWeiland\UrlRedirect\Domain\Model;

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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Config extends AbstractEntity
{
    /**
     * @var bool
     */
    protected $useRegExp = false;

    /**
     * @var string
     *
     * @validate NotEmpty
     */
    protected $requestUri = '';

    /**
     * @var string
     *
     * @validate NotEmpty
     */
    protected $targetUri = '';

    /**
     * @var int
     *
     * @validate NotEmpty
     */
    protected $httpStatus = 301;

    /**
     * Returns the useRegExp
     *
     * @return bool $useRegExp
     */
    public function getUseRegExp()
    {
        return $this->useRegExp;
    }

    /**
     * Sets the useRegExp
     *
     * @param bool $useRegExp
     *
     * @return void
     */
    public function setUseRegExp($useRegExp)
    {
        $this->useRegExp = (bool)$useRegExp;
    }

    /**
     * Returns the requestUri
     *
     * @return string $requestUri
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Sets the requestUri
     *
     * @param string $requestUri
     *
     * @return void
     */
    public function setRequestUri($requestUri)
    {
        $this->requestUri = (string)$requestUri;
    }

    /**
     * Returns the targetUri
     *
     * @return string $targetUri
     */
    public function getTargetUri()
    {
        return $this->targetUri;
    }

    /**
     * Sets the targetUri
     *
     * @param string $targetUri
     *
     * @return void
     */
    public function setTargetUri($targetUri)
    {
        $this->targetUri = (string)$targetUri;
    }

    /**
     * Returns the httpStatus
     *
     * @return int $httpStatus
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * Sets the httpStatus
     *
     * @param int $httpStatus
     *
     * @return void
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = (int)$httpStatus;
    }
}
