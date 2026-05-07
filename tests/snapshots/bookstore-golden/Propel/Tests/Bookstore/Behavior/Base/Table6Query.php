<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Base;

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
use Propel\Tests\Bookstore\Behavior\Table6 as ChildTable6;
use Propel\Tests\Bookstore\Behavior\Table6Query as ChildTable6Query;
use Propel\Tests\Bookstore\Behavior\Map\Table6TableMap;

/**
 * Base class that represents a query for the `table6` table.
 *
 * @method     ChildTable6Query orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildTable6Query orderById($order = Criteria::ASC) Order by the id column
 *
 * @method     ChildTable6Query groupByTitle() Group by the title column
 * @method     ChildTable6Query groupById() Group by the id column
 *
 * @method     ChildTable6Query leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildTable6Query rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildTable6Query innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildTable6Query leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildTable6Query rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildTable6Query innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildTable6Query leftJoinTable8($relationAlias = null) Adds a LEFT JOIN clause to the query using the Table8 relation
 * @method     ChildTable6Query rightJoinTable8($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Table8 relation
 * @method     ChildTable6Query innerJoinTable8($relationAlias = null) Adds a INNER JOIN clause to the query using the Table8 relation
 *
 * @method     ChildTable6Query joinWithTable8($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Table8 relation
 *
 * @method     ChildTable6Query leftJoinWithTable8() Adds a LEFT JOIN clause and with to the query using the Table8 relation
 * @method     ChildTable6Query rightJoinWithTable8() Adds a RIGHT JOIN clause and with to the query using the Table8 relation
 * @method     ChildTable6Query innerJoinWithTable8() Adds a INNER JOIN clause and with to the query using the Table8 relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\Table8Query endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildTable6|null findOne(?ConnectionInterface $con = null) Return the first ChildTable6 matching the query
 * @method     ChildTable6 findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildTable6 matching the query, or a new ChildTable6 object populated from the query conditions when no match is found
 *
 * @method     ChildTable6|null findOneByTitle(string $title) Return the first ChildTable6 filtered by the title column
 * @method     ChildTable6|null findOneById(int $id) Return the first ChildTable6 filtered by the id column
 *
 * @method     ChildTable6 requirePk($key, ?ConnectionInterface $con = null) Return the ChildTable6 by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTable6 requireOne(?ConnectionInterface $con = null) Return the first ChildTable6 matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildTable6 requireOneByTitle(string $title) Return the first ChildTable6 filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTable6 requireOneById(int $id) Return the first ChildTable6 filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildTable6[]|Collection find(?ConnectionInterface $con = null) Return ChildTable6 objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildTable6> find(?ConnectionInterface $con = null) Return ChildTable6 objects based on current ModelCriteria
 *
 * @method     ChildTable6[]|Collection findByTitle(string|array<string> $title) Return ChildTable6 objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildTable6> findByTitle(string|array<string> $title) Return ChildTable6 objects filtered by the title column
 * @method     ChildTable6[]|Collection findById(int|array<int> $id) Return ChildTable6 objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildTable6> findById(int|array<int> $id) Return ChildTable6 objects filtered by the id column
 *
 * @method     ChildTable6[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildTable6> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class Table6Query extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\Table6Query object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\Table6', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildTable6Query object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildTable6Query
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof static) {
            return $criteria;
        }
        if ($criteria instanceof ChildTable6Query) {
            $query = $criteria;
        } else {
            $query = new static();
            if ($criteria instanceof Criteria) {
                $query->mergeWith($criteria);
            }
        }
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
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
     * @return ChildTable6|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(Table6TableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = Table6TableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildTable6 A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT title, id FROM table6 WHERE id = :p0';
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
            /** @var ChildTable6 $obj */
            $obj = new ChildTable6();
            $obj->hydrate($row);
            Table6TableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildTable6|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(Table6TableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(Table6TableMap::COL_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the title column
     *
     * Example usage:
     * <code>
     * $query->filterByTitle('fooValue');   // WHERE title = 'fooValue'
     * $query->filterByTitle('%fooValue%', Criteria::LIKE); // WHERE title LIKE '%fooValue%'
     * $query->filterByTitle(['foo', 'bar']); // WHERE title IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $title The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTitle($title = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($title)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(Table6TableMap::COL_TITLE, $title, $comparison);

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
                $this->addUsingAlias(Table6TableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(Table6TableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(Table6TableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\Table8 object
     *
     * @param \Propel\Tests\Bookstore\Behavior\Table8|ObjectCollection $table8 the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTable8($table8, ?string $comparison = null)
    {
        if ($table8 instanceof \Propel\Tests\Bookstore\Behavior\Table8) {
            $this
                ->addUsingAlias(Table6TableMap::COL_ID, $table8->getFooId(), $comparison);

            return $this;
        } elseif ($table8 instanceof ObjectCollection) {
            $this
                ->useTable8Query()
                ->filterByPrimaryKeys($table8->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByTable8() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\Table8 or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Table8 relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinTable8(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Table8');

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
            $this->addJoinObject($join, 'Table8');
        }

        return $this;
    }

    /**
     * Use the Table8 relation Table8 object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\Table8Query A secondary query class using the current class as primary query
     */
    public function useTable8Query(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTable8($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Table8', '\Propel\Tests\Bookstore\Behavior\Table8Query');
    }

    /**
     * Use the Table8 relation Table8 object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\Table8Query):\Propel\Tests\Bookstore\Behavior\Table8Query $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withTable8Query(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useTable8Query(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Table8 table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\Table8Query The inner query object of the EXISTS statement
     */
    public function useTable8ExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\Table8Query */
        $q = $this->useExistsQuery('Table8', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Table8 table for a NOT EXISTS query.
     *
     * @see useTable8ExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\Table8Query The inner query object of the NOT EXISTS statement
     */
    public function useTable8NotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\Table8Query */
        $q = $this->useExistsQuery('Table8', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Table8 table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\Table8Query The inner query object of the IN statement
     */
    public function useInTable8Query(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\Table8Query */
        $q = $this->useInQuery('Table8', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Table8 table for a NOT IN query.
     *
     * @see useTable8InQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\Table8Query The inner query object of the NOT IN statement
     */
    public function useNotInTable8Query(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\Table8Query */
        $q = $this->useInQuery('Table8', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildTable6 $table6 Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($table6 = null)
    {
        if ($table6) {
            $this->addUsingAlias(Table6TableMap::COL_ID, $table6->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the table6 table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(Table6TableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            Table6TableMap::clearInstancePool();
            Table6TableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(Table6TableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(Table6TableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            Table6TableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            Table6TableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
