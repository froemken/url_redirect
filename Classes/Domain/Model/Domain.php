<?php

namespace StefanFroemken\UrlRedirect\Domain\Model;

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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Domain extends AbstractEntity
{
    /**
     * @var string
     */
    protected $domainname = '';

    /**
     * Returns the domainname
     *
     * @return string $domainname
     */
    public function getDomainname()
    {
        return $this->domainname;
    }

    /**
     * Sets the domainname
     *
     * @param string $domainname
     *
     * @return void
     */
    public function setDomainname($domainname)
    {
        $this->domainname = (string)$domainname;
    }
}
