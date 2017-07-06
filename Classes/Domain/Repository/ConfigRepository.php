<?php

namespace JWeiland\UrlRedirect\Domain\Repository;

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
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ConfigRepository extends Repository
{
    /**
     * get category objects from list of UIDs.
     *
     * @param string $categoryUids UIDs category
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getCategories($categoryUids)
    {
        $categoryUids = GeneralUtility::intExplode(',', $categoryUids);
        $query = $this->createQuery();

        return $query->matching($query->in('uid', $categoryUids))->execute();
    }

    /**
     * get subcategories of given UID.
     *
     * @param string $category UID category
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getSubCategories($category)
    {
        $query = $this->createQuery();

        return $query->matching($query->equals('parent', $category))->execute();
    }

    /**
     * get all categories given by comma separated list.
     *
     * @param string $categoryUids comma separated list of category uids
     * @param string $parent       parent category UID. This value comes from TS, so it's a string
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getSelectedCategories($categoryUids, $parent = '')
    {
        // remove empty values
        // convert them to int
        // remove values with 0 (array_filter)
        // correct keys for unit tests (array_values)
        $selectedCategories = array_values(array_filter(GeneralUtility::intExplode(',', $categoryUids, true)));
        $query = $this->createQuery();

        $constraint = array();
        $constraint[] = $query->in('uid', $selectedCategories);

        if (MathUtility::canBeInterpretedAsInteger($parent)) {
            $constraint[] = $query->equals('parent', (int)$parent);
        }

        return $query->matching($query->logicalAnd($constraint))->execute();
    }
}
