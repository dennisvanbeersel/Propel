<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Tests\Bookstore\AcctAuditLog as ChildAcctAuditLog;
use Propel\Tests\Bookstore\AcctAuditLogQuery as ChildAcctAuditLogQuery;
use Propel\Tests\Bookstore\Map\AcctAuditLogTableMap;

/**
 * Base class that represents a query for the `acct_audit_log` table.
 *
 * @method     ChildAcctAuditLogQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildAcctAuditLogQuery orderByUid($order = Criteria::ASC) Order by the uid column
 * @method     ChildAcctAuditLogQuery orderByMessage($order = Criteria::ASC) Order by the message column
 *
 * @method     ChildAcctAuditLogQuery groupById() Group by the id column
 * @method     ChildAcctAuditLogQuery groupByUid() Group by the uid column
 * @method     ChildAcctAuditLogQuery groupByMessage() Group by the message column
 *
 * @method     ChildAcctAuditLogQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildAcctAuditLogQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildAcctAuditLogQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildAcctAuditLogQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildAcctAuditLogQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildAcctAuditLogQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildAcctAuditLogQuery leftJoinBookstoreEmployeeAccount($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookstoreEmployeeAccount relation
 * @method     ChildAcctAuditLogQuery rightJoinBookstoreEmployeeAccount($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookstoreEmployeeAccount relation
 * @method     ChildAcctAuditLogQuery innerJoinBookstoreEmployeeAccount($relationAlias = null) Adds a INNER JOIN clause to the query using the BookstoreEmployeeAccount relation
 *
 * @method     ChildAcctAuditLogQuery joinWithBookstoreEmployeeAccount($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookstoreEmployeeAccount relation
 *
 * @method     ChildAcctAuditLogQuery leftJoinWithBookstoreEmployeeAccount() Adds a LEFT JOIN clause and with to the query using the BookstoreEmployeeAccount relation
 * @method     ChildAcctAuditLogQuery rightJoinWithBookstoreEmployeeAccount() Adds a RIGHT JOIN clause and with to the query using the BookstoreEmployeeAccount relation
 * @method     ChildAcctAuditLogQuery innerJoinWithBookstoreEmployeeAccount() Adds a INNER JOIN clause and with to the query using the BookstoreEmployeeAccount relation
 *
 * @method     \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildAcctAuditLog|null findOne(?ConnectionInterface $con = null) Return the first ChildAcctAuditLog matching the query
 * @method     ChildAcctAuditLog findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildAcctAuditLog matching the query, or a new ChildAcctAuditLog object populated from the query conditions when no match is found
 *
 * @method     ChildAcctAuditLog|null findOneById(int $id) Return the first ChildAcctAuditLog filtered by the id column
 * @method     ChildAcctAuditLog|null findOneByUid(string $uid) Return the first ChildAcctAuditLog filtered by the uid column
 * @method     ChildAcctAuditLog|null findOneByMessage(string $message) Return the first ChildAcctAuditLog filtered by the message column
 *
 * @method     ChildAcctAuditLog requirePk($key, ?ConnectionInterface $con = null) Return the ChildAcctAuditLog by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAcctAuditLog requireOne(?ConnectionInterface $con = null) Return the first ChildAcctAuditLog matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAcctAuditLog requireOneById(int $id) Return the first ChildAcctAuditLog filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAcctAuditLog requireOneByUid(string $uid) Return the first ChildAcctAuditLog filtered by the uid column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAcctAuditLog requireOneByMessage(string $message) Return the first ChildAcctAuditLog filtered by the message column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAcctAuditLog[]|Collection find(?ConnectionInterface $con = null) Return ChildAcctAuditLog objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildAcctAuditLog> find(?ConnectionInterface $con = null) Return ChildAcctAuditLog objects based on current ModelCriteria
 *
 * @method     ChildAcctAuditLog[]|Collection findById(int|array<int> $id) Return ChildAcctAuditLog objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildAcctAuditLog> findById(int|array<int> $id) Return ChildAcctAuditLog objects filtered by the id column
 * @method     ChildAcctAuditLog[]|Collection findByUid(string|array<string> $uid) Return ChildAcctAuditLog objects filtered by the uid column
 * @psalm-method Collection&\Traversable<ChildAcctAuditLog> findByUid(string|array<string> $uid) Return ChildAcctAuditLog objects filtered by the uid column
 * @method     ChildAcctAuditLog[]|Collection findByMessage(string|array<string> $message) Return ChildAcctAuditLog objects filtered by the message column
 * @psalm-method Collection&\Traversable<ChildAcctAuditLog> findByMessage(string|array<string> $message) Return ChildAcctAuditLog objects filtered by the message column
 *
 * @method     ChildAcctAuditLog[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildAcctAuditLog> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class AcctAuditLogQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\AcctAuditLogQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\AcctAuditLog', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildAcctAuditLogQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildAcctAuditLogQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildAcctAuditLogQuery) {
            return $criteria;
        }
        $query = new ChildAcctAuditLogQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildAcctAuditLog|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(AcctAuditLogTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = AcctAuditLogTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildAcctAuditLog A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, uid, message FROM acct_audit_log WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildAcctAuditLog $obj */
            $obj = new ChildAcctAuditLog();
            $obj->hydrate($row);
            AcctAuditLogTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @return ChildAcctAuditLog|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param array $keys Primary keys to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return Collection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param mixed $key Primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        $this->addUsingAlias(AcctAuditLogTableMap::COL_ID, $key, Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array|int $keys The list of primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        $this->addUsingAlias(AcctAuditLogTableMap::COL_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterById($id = null, ?string $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(AcctAuditLogTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(AcctAuditLogTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AcctAuditLogTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the uid column
     *
     * Example usage:
     * <code>
     * $query->filterByUid('fooValue');   // WHERE uid = 'fooValue'
     * $query->filterByUid('%fooValue%', Criteria::LIKE); // WHERE uid LIKE '%fooValue%'
     * $query->filterByUid(['foo', 'bar']); // WHERE uid IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $uid The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByUid($uid = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($uid)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AcctAuditLogTableMap::COL_UID, $uid, $comparison);

        return $this;
    }

    /**
     * Filter the query on the message column
     *
     * Example usage:
     * <code>
     * $query->filterByMessage('fooValue');   // WHERE message = 'fooValue'
     * $query->filterByMessage('%fooValue%', Criteria::LIKE); // WHERE message LIKE '%fooValue%'
     * $query->filterByMessage(['foo', 'bar']); // WHERE message IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $message The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByMessage($message = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($message)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AcctAuditLogTableMap::COL_MESSAGE, $message, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookstoreEmployeeAccount object
     *
     * @param \Propel\Tests\Bookstore\BookstoreEmployeeAccount|ObjectCollection $bookstoreEmployeeAccount The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookstoreEmployeeAccount($bookstoreEmployeeAccount, ?string $comparison = null)
    {
        if ($bookstoreEmployeeAccount instanceof \Propel\Tests\Bookstore\BookstoreEmployeeAccount) {
            return $this
                ->addUsingAlias(AcctAuditLogTableMap::COL_UID, $bookstoreEmployeeAccount->getLogin(), $comparison);
        } elseif ($bookstoreEmployeeAccount instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(AcctAuditLogTableMap::COL_UID, $bookstoreEmployeeAccount->toKeyValue('PrimaryKey', 'Login'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByBookstoreEmployeeAccount() only accepts arguments of type \Propel\Tests\Bookstore\BookstoreEmployeeAccount or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookstoreEmployeeAccount relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookstoreEmployeeAccount(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookstoreEmployeeAccount');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BookstoreEmployeeAccount');
        }

        return $this;
    }

    /**
     * Use the BookstoreEmployeeAccount relation BookstoreEmployeeAccount object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery A secondary query class using the current class as primary query
     */
    public function useBookstoreEmployeeAccountQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookstoreEmployeeAccount($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookstoreEmployeeAccount', '\Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery');
    }

    /**
     * Use the BookstoreEmployeeAccount relation BookstoreEmployeeAccount object
     *
     * @param callable(\Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery):\Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookstoreEmployeeAccountQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookstoreEmployeeAccountQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookstoreEmployeeAccount table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery The inner query object of the EXISTS statement
     */
    public function useBookstoreEmployeeAccountExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery */
        $q = $this->useExistsQuery('BookstoreEmployeeAccount', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookstoreEmployeeAccount table for a NOT EXISTS query.
     *
     * @see useBookstoreEmployeeAccountExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookstoreEmployeeAccountNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery */
        $q = $this->useExistsQuery('BookstoreEmployeeAccount', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookstoreEmployeeAccount table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery The inner query object of the IN statement
     */
    public function useInBookstoreEmployeeAccountQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery */
        $q = $this->useInQuery('BookstoreEmployeeAccount', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookstoreEmployeeAccount table for a NOT IN query.
     *
     * @see useBookstoreEmployeeAccountInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookstoreEmployeeAccountQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery */
        $q = $this->useInQuery('BookstoreEmployeeAccount', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildAcctAuditLog $acctAuditLog Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($acctAuditLog = null)
    {
        if ($acctAuditLog) {
            $this->addUsingAlias(AcctAuditLogTableMap::COL_ID, $acctAuditLog->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the acct_audit_log table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AcctAuditLogTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            AcctAuditLogTableMap::clearInstancePool();
            AcctAuditLogTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AcctAuditLogTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(AcctAuditLogTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            AcctAuditLogTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            AcctAuditLogTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
