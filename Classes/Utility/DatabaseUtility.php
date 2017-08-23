<?php

namespace StefanFroemken\UrlRedirect\Utility;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\BackendWorkspaceRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DatabaseUtility
 *
 * @package StefanFroemken\UrlRedirect\Utility
 */
class DatabaseUtility {
    /**
     * Returns the first record found from $table with $where as WHERE clause
     * This function does NOT check if a record has the deleted flag set.
     * $table does NOT need to be configured in $GLOBALS['TCA']
     * The query used is simply this:
     * $query = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE ' . $where;
     *
     * @param string $table Table name (not necessarily in TCA)
     * @param string $where WHERE clause
     * @param string $fields $fields is a list of fields to select, default is '*'
     *
     * @return array|bool First row found, if any, FALSE otherwise
     */
    public static function getRecordRaw($table, $where = '', $fields = '*')
    {
        if (class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            $queryBuilder = self::getQueryBuilderForTable($table);
            $queryBuilder->getRestrictions()->removeAll();

            $row = $queryBuilder
                ->select(...GeneralUtility::trimExplode(',', $fields, true))
                ->from($table)
                ->where(QueryHelper::stripLogicalOperatorPrefix($where))
                ->execute()
                ->fetch();

            return $row ?: false;
        } else {
            $row = false;
            $db = static::getDatabaseConnection();
            if (false !== ($res = $db->exec_SELECTquery($fields, $table, $where, '', '', '1'))) {
                $row = $db->sql_fetch_assoc($res);
                $db->sql_free_result($res);
            }
            return $row;
        }
    }

    /**
     * Returns records from table, $theTable, where a field ($theField) equals the value, $theValue
     * The records are returned in an array
     * If no records were selected, the function returns nothing
     *
     * @param string $theTable Table name present in $GLOBALS['TCA']
     * @param string $theField Field to select on
     * @param string $theValue Value that $theField must match
     * @param string $whereClause Optional additional WHERE clauses put in the end of the query. DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @param bool $useDeleteClause Use the deleteClause to check if a record is deleted (default TRUE)
     * @param null|QueryBuilder $queryBuilder The queryBuilder must be provided, if the parameter $whereClause is given and the concept of prepared statement was used. Example within self::firstDomainRecord()
     *
     * @return mixed Multidimensional array with selected records (if any is selected)
     */
    public static function getRecordsByField(
        $theTable,
        $theField,
        $theValue,
        $whereClause = '',
        $groupBy = '',
        $orderBy = '',
        $limit = '',
        $useDeleteClause = true,
        $queryBuilder = null
    ) {
        if (class_exists('TYPO3\\CMS\\Core\\Database\\ConnectionPool')) {
            if (null === $queryBuilder) {
                $queryBuilder = static::getQueryBuilderForTable($theTable);
            }

            /** @var BackendWorkspaceRestriction $backendWorkspaceRestriction */
            $backendWorkspaceRestriction = GeneralUtility::makeInstance(BackendWorkspaceRestriction::class);
            /** @var DeletedRestriction $deletedRestriction */
            $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);

            // Show all records except versioning placeholders
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add($backendWorkspaceRestriction);

            // Remove deleted records from the query result
            if ($useDeleteClause) {
                $queryBuilder->getRestrictions()->add($deletedRestriction);
            }

            // build fields to select
            $queryBuilder
                ->select('*')
                ->from($theTable)
                ->where($queryBuilder->expr()->eq($theField, $queryBuilder->createNamedParameter($theValue)));

            // additional where
            if ($whereClause) {
                $queryBuilder->andWhere(QueryHelper::stripLogicalOperatorPrefix($whereClause));
            }

            // group by
            if ($groupBy !== '') {
                $queryBuilder->groupBy(QueryHelper::parseGroupBy($groupBy));
            }

            // order by
            if ($orderBy !== '') {
                foreach (QueryHelper::parseOrderBy($orderBy) as $orderPair) {
                    list($fieldName, $order) = $orderPair;
                    $queryBuilder->addOrderBy($fieldName, $order);
                }
            }

            // limit
            if ($limit !== '') {
                if (strpos($limit, ',')) {
                    $limitOffsetAndMax = GeneralUtility::intExplode(',', $limit);
                    $queryBuilder->setFirstResult((int)$limitOffsetAndMax[0]);
                    $queryBuilder->setMaxResults((int)$limitOffsetAndMax[1]);
                } else {
                    $queryBuilder->setMaxResults((int)$limit);
                }
            }

            $rows = $queryBuilder->execute()->fetchAll();
            return $rows;
        } else {
            $db = static::getDatabaseConnection();
            $res = $db->exec_SELECTquery(
                '*',
                $theTable,
                $theField . '=' . $db->fullQuoteStr($theValue, $theTable) .
                ($useDeleteClause ? self::deleteClause($theTable) . ' ' : '') .
                self::versioningPlaceholderClause($theTable) . ' ' .
                $whereClause,
                $groupBy,
                $orderBy,
                $limit
            );
            $rows = [];
            while ($row = $db->sql_fetch_assoc($res)) {
                $rows[] = $row;
            }
            $db->sql_free_result($res);
            if (!empty($rows)) {
                return $rows;
            }
        }
        return null;
    }

    /**
     * @return DatabaseConnection
     */
    protected static function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param string $table
     *
     * @return QueryBuilder
     */
    protected static function getQueryBuilderForTable($table)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
}
