<?php

namespace Propel\Tests\Bookstore\Base;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Tests\Bookstore\Map\BookstoreEmployeeTableMap;

/**
 * Skeleton subclass for representing a query for one of the subclasses of the 'bookstore_employee' table.
 *
 * Hierarchical table to represent employees of a bookstore.
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class BookstoreCashierQuery extends BookstoreEmployeeQuery
{

    /**
     * Returns a new \Propel\Tests\Bookstore\BookstoreCashierQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return \Propel\Tests\Bookstore\BookstoreCashierQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof \Propel\Tests\Bookstore\BookstoreCashierQuery) {
            return $criteria;
        }
        $query = new \Propel\Tests\Bookstore\BookstoreCashierQuery();
        if ($modelAlias !== null) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria !== null) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Filters the query to target only BookstoreCashier objects.
     */
    public function preSelect(ConnectionInterface $con): void
    {
        $this->addUsingAlias(BookstoreEmployeeTableMap::COL_CLASS_KEY, BookstoreEmployeeTableMap::CLASSKEY_2);
    }

    /**
     * Filters the query to target only BookstoreCashier objects.
     *
     * @return int|null
     */
    public function preUpdate(&$values, ConnectionInterface $con, $forceIndividualSaves = false): ?int
    {
        $this->addUsingAlias(BookstoreEmployeeTableMap::COL_CLASS_KEY, BookstoreEmployeeTableMap::CLASSKEY_2);

        return null;
    }

    /**
     * Filters the query to target only BookstoreCashier objects.
     *
     * @return int|null
     */
    public function preDelete(ConnectionInterface $con): ?int
    {
        $this->addUsingAlias(BookstoreEmployeeTableMap::COL_CLASS_KEY, BookstoreEmployeeTableMap::CLASSKEY_2);

        return null;
    }

    /**
     * Issue a DELETE query based on the current ModelCriteria deleting all rows in the table
     * Having the BookstoreCashier class.
     * This method is called by ModelCriteria::deleteAll() inside a transaction
     *
     * @param ConnectionInterface $con a connection object
     *
     * @return int The number of deleted rows
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        // condition on class key is already added in preDelete()
        return parent::delete($con);
    }

}
