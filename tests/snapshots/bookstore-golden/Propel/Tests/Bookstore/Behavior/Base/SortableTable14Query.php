<?php

namespace Propel\Tests\Bookstore\Behavior\Base;

use \Exception;
use \PDO;
use Propel\Common\Exception\SetColumnConverterException;
use Propel\Common\Util\SetColumnConverter;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Tests\Bookstore\Behavior\SortableTable14 as ChildSortableTable14;
use Propel\Tests\Bookstore\Behavior\SortableTable14Query as ChildSortableTable14Query;
use Propel\Tests\Bookstore\Behavior\Map\SortableTable14TableMap;

/**
 * Base class that represents a query for the `sortable_table14` table.
 *
 * @method     ChildSortableTable14Query orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildSortableTable14Query orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildSortableTable14Query orderByStyle2($order = Criteria::ASC) Order by the style2 column
 * @method     ChildSortableTable14Query orderBySortableRank($order = Criteria::ASC) Order by the sortable_rank column
 *
 * @method     ChildSortableTable14Query groupById() Group by the id column
 * @method     ChildSortableTable14Query groupByTitle() Group by the title column
 * @method     ChildSortableTable14Query groupByStyle2() Group by the style2 column
 * @method     ChildSortableTable14Query groupBySortableRank() Group by the sortable_rank column
 *
 * @method     ChildSortableTable14Query leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildSortableTable14Query rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildSortableTable14Query innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildSortableTable14Query leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildSortableTable14Query rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildSortableTable14Query innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildSortableTable14|null findOne(?ConnectionInterface $con = null) Return the first ChildSortableTable14 matching the query
 * @method     ChildSortableTable14 findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildSortableTable14 matching the query, or a new ChildSortableTable14 object populated from the query conditions when no match is found
 *
 * @method     ChildSortableTable14|null findOneById(int $id) Return the first ChildSortableTable14 filtered by the id column
 * @method     ChildSortableTable14|null findOneByTitle(string $title) Return the first ChildSortableTable14 filtered by the title column
 * @method     ChildSortableTable14|null findOneByStyle2(int $style2) Return the first ChildSortableTable14 filtered by the style2 column
 * @method     ChildSortableTable14|null findOneBySortableRank(int $sortable_rank) Return the first ChildSortableTable14 filtered by the sortable_rank column
 *
 * @method     ChildSortableTable14 requirePk($key, ?ConnectionInterface $con = null) Return the ChildSortableTable14 by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableTable14 requireOne(?ConnectionInterface $con = null) Return the first ChildSortableTable14 matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSortableTable14 requireOneById(int $id) Return the first ChildSortableTable14 filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableTable14 requireOneByTitle(string $title) Return the first ChildSortableTable14 filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableTable14 requireOneByStyle2(int $style2) Return the first ChildSortableTable14 filtered by the style2 column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableTable14 requireOneBySortableRank(int $sortable_rank) Return the first ChildSortableTable14 filtered by the sortable_rank column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSortableTable14[]|Collection find(?ConnectionInterface $con = null) Return ChildSortableTable14 objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildSortableTable14> find(?ConnectionInterface $con = null) Return ChildSortableTable14 objects based on current ModelCriteria
 *
 * @method     ChildSortableTable14[]|Collection findById(int|array<int> $id) Return ChildSortableTable14 objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildSortableTable14> findById(int|array<int> $id) Return ChildSortableTable14 objects filtered by the id column
 * @method     ChildSortableTable14[]|Collection findByTitle(string|array<string> $title) Return ChildSortableTable14 objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildSortableTable14> findByTitle(string|array<string> $title) Return ChildSortableTable14 objects filtered by the title column
 * @method     ChildSortableTable14[]|Collection findByStyle2(int|array<int> $style2) Return ChildSortableTable14 objects filtered by the style2 column
 * @psalm-method Collection&\Traversable<ChildSortableTable14> findByStyle2(int|array<int> $style2) Return ChildSortableTable14 objects filtered by the style2 column
 * @method     ChildSortableTable14[]|Collection findBySortableRank(int|array<int> $sortable_rank) Return ChildSortableTable14 objects filtered by the sortable_rank column
 * @psalm-method Collection&\Traversable<ChildSortableTable14> findBySortableRank(int|array<int> $sortable_rank) Return ChildSortableTable14 objects filtered by the sortable_rank column
 *
 * @method     ChildSortableTable14[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildSortableTable14> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class SortableTable14Query extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\SortableTable14Query object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\SortableTable14', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildSortableTable14Query object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildSortableTable14Query
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildSortableTable14Query) {
            return $criteria;
        }
        $query = new ChildSortableTable14Query();
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
     * @return ChildSortableTable14|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = SortableTable14TableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildSortableTable14 A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, style2, sortable_rank FROM sortable_table14 WHERE id = :p0';
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
            /** @var ChildSortableTable14 $obj */
            $obj = new ChildSortableTable14();
            $obj->hydrate($row);
            SortableTable14TableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildSortableTable14|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(SortableTable14TableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(SortableTable14TableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(SortableTable14TableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(SortableTable14TableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SortableTable14TableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(SortableTable14TableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the style2 column
     *
     * @param mixed $style2 The value to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByStyle2($style2 = null, ?string $comparison = null)
    {
        $valueSet = SortableTable14TableMap::getValueSet(SortableTable14TableMap::COL_STYLE2);
        try {
            $style2 = SetColumnConverter::convertToInt($style2, $valueSet);
        } catch (SetColumnConverterException $e) {
            throw new PropelException(sprintf('Value "%s" is not accepted in this set column', $e->getValue()), $e->getCode(), $e);
        }
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if ($style2 === '0') {
                return $this;
            }
            $comparison = Criteria::BINARY_ALL;
        } elseif ($comparison == Criteria::CONTAINS_SOME || $comparison == Criteria::IN) {
            if ($style2 === '0') {
                return $this;
            }
            $comparison = Criteria::BINARY_AND;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $key = $this->getAliasedColName(SortableTable14TableMap::COL_STYLE2);
            if ($style2 !== '0') {
                $this->add($key, $style2, Criteria::BINARY_NONE);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        $this->addUsingAlias(SortableTable14TableMap::COL_STYLE2, $style2, $comparison);

        return $this;
    }

    /**
     * Filter the query on the sortable_rank column
     *
     * Example usage:
     * <code>
     * $query->filterBySortableRank(1234); // WHERE sortable_rank = 1234
     * $query->filterBySortableRank(array(12, 34)); // WHERE sortable_rank IN (12, 34)
     * $query->filterBySortableRank(array('min' => 12)); // WHERE sortable_rank > 12
     * </code>
     *
     * @param mixed $sortableRank The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySortableRank($sortableRank = null, ?string $comparison = null)
    {
        if (is_array($sortableRank)) {
            $useMinMax = false;
            if (isset($sortableRank['min'])) {
                $this->addUsingAlias(SortableTable14TableMap::COL_SORTABLE_RANK, $sortableRank['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($sortableRank['max'])) {
                $this->addUsingAlias(SortableTable14TableMap::COL_SORTABLE_RANK, $sortableRank['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SortableTable14TableMap::COL_SORTABLE_RANK, $sortableRank, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildSortableTable14 $sortableTable14 Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($sortableTable14 = null)
    {
        if ($sortableTable14) {
            $this->addUsingAlias(SortableTable14TableMap::COL_ID, $sortableTable14->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the sortable_table14 table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            SortableTable14TableMap::clearInstancePool();
            SortableTable14TableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(SortableTable14TableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            SortableTable14TableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            SortableTable14TableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // sortable behavior

    /**
     * Returns the objects in a certain list, from the list scope
     *
     * @param int $scope Scope to determine which objects node to return
     *
     * @return $this The current query, for fluid interface
     */
    public function inList($scope = null)
    {

        static::sortableApplyScopeCriteria($this, $scope, 'addUsingAlias');

        return $this;
    }

    /**
     * Filter the query based on a rank in the list
     *
     * @param int $rank rank
     * @param int $scope Scope to determine which objects node to return

     *
     * @return $this The current object, for fluid interface
     */
    public function filterByRank($rank, $scope = null)
    {

        $this
            ->inList($scope)
            ->addUsingAlias(SortableTable14TableMap::RANK_COL, $rank, Criteria::EQUAL);

        return $this;
    }

    /**
     * Order the query based on the rank in the list.
     * Using the default $order, returns the item with the lowest rank first
     *
     * @param string $order either Criteria::ASC (default) or Criteria::DESC
     *
     * @return $this The current query, for fluid interface
     */
    public function orderByRank(string $order = Criteria::ASC)
    {
        $order = strtoupper($order);
        switch ($order) {
            case Criteria::ASC:
                $this->addAscendingOrderByColumn($this->getAliasedColName(SortableTable14TableMap::RANK_COL));

                return $this;
            case Criteria::DESC:
                $this->addDescendingOrderByColumn($this->getAliasedColName(SortableTable14TableMap::RANK_COL));

                return $this;
            default:
                throw new \Propel\Runtime\Exception\PropelException('ChildSortableTable14Query::orderBy() only accepts "asc" or "desc" as argument');
        }
    }

    /**
     * Get an item from the list based on its rank
     *
     * @param int $rank rank
     * @param int $scope Scope to determine which objects node to return
     * @param ConnectionInterface $con optional connection
     *
     * @return ChildSortableTable14
     */
    public function findOneByRank($rank, $scope = null, ?ConnectionInterface $con = null)
    {

        return $this
            ->filterByRank($rank, $scope)
            ->findOne($con);
    }

    /**
     * Returns a list of objects
     *
     * @param int $scope Scope to determine which objects node to return

     * @param ConnectionInterface $con Connection to use.
     *
     * @return mixed the list of results, formatted by the current formatter
     */
    public function findList($scope = null, $con = null)
    {

        return $this
            ->inList($scope)
            ->orderByRank()
            ->find($con);
    }

    /**
     * Get the highest rank
     *
     * @param int $scope Scope to determine which objects node to return
     * @param ConnectionInterface $con Optional connection
     *
     * @return int|null Highest position
     */
    public function getMaxRank($scope = null, ?ConnectionInterface $con = null): ?int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableTable14TableMap::DATABASE_NAME);
        }
        // shift the objects with a position lower than the one of object
        $this->addSelectColumn('MAX(' . SortableTable14TableMap::RANK_COL . ')');

                static::sortableApplyScopeCriteria($this, $scope);
        $stmt = $this->doSelect($con);

        return $stmt->fetchColumn();
    }

    /**
     * Get the highest rank by a scope with a array format.
     *
     * @param mixed $scope      The scope value as scalar type or array($value1, ...).

     * @param ConnectionInterface $con Optional connection
     *
     * @return int|null Highest position
     */
    public function getMaxRankArray($scope, ?ConnectionInterface $con = null): ?int
    {
        if ($con === null) {
            $con = Propel::getConnection(SortableTable14TableMap::DATABASE_NAME);
        }
        // shift the objects with a position lower than the one of object
        $this->addSelectColumn('MAX(' . SortableTable14TableMap::RANK_COL . ')');
        static::sortableApplyScopeCriteria($this, $scope);
        $stmt = $this->doSelect($con);

        return $stmt->fetchColumn();
    }

    /**
     * Get an item from the list based on its rank
     *
     * @param int $rank rank
     * @param int $scope        Scope to determine which suite to consider
     * @param ConnectionInterface $con optional connection
     *
     * @return ChildSortableTable14
     */
    static public function retrieveByRank($rank, $scope = null, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        $c = new Criteria;
        $c->add(SortableTable14TableMap::RANK_COL, $rank);
                static::sortableApplyScopeCriteria($c, $scope);

        return static::create(null, $c)->findOne($con);
    }

    /**
     * Reorder a set of sortable objects based on a list of id/position
     * Beware that there is no check made on the positions passed
     * So incoherent positions will result in an incoherent list
     *
     * @param mixed $order id => rank pairs
     * @param ConnectionInterface $con   optional connection
     *
     * @return bool true if the reordering took place, false if a database problem prevented it
     */
    public function reorder($order, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con, $order) {
            $ids = array_keys($order);
            $objects = $this->findPks($ids, $con);
            foreach ($objects as $object) {
                $pk = $object->getPrimaryKey();
                if ($object->getSortableRank() != $order[$pk]) {
                    $object->setSortableRank($order[$pk]);
                    $object->save($con);
                }
            }
        });

        return true;
    }

    /**
     * Return an array of sortable objects ordered by position
     *
     * @param Criteria $criteria  optional criteria object
     * @param string $order     sorting order, to be chosen between Criteria::ASC (default) and Criteria::DESC
     * @param ConnectionInterface $con       optional connection
     *
     * @return array list of sortable objects
     */
    static public function doSelectOrderByRank(?Criteria $criteria = null, $order = Criteria::ASC, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        if (null === $criteria) {
            $criteria = new Criteria();
        } elseif ($criteria instanceof Criteria) {
            $criteria = clone $criteria;
        }

        $criteria->clearOrderByColumns();

        if (Criteria::ASC == $order) {
            $criteria->addAscendingOrderByColumn(SortableTable14TableMap::RANK_COL);
        } else {
            $criteria->addDescendingOrderByColumn(SortableTable14TableMap::RANK_COL);
        }

        return ChildSortableTable14Query::create(null, $criteria)->find($con);
    }

    /**
     * Return an array of sortable objects in the given scope ordered by position
     *
     * @param int $scope  the scope of the list
     * @param string $order  sorting order, to be chosen between Criteria::ASC (default) and Criteria::DESC
     * @param ConnectionInterface $con    optional connection
     *
     * @return \Propel\Runtime\Collection\ObjectCollection List of sortable objects
     */
    static public function retrieveList($scope, $order = Criteria::ASC, ?ConnectionInterface $con = null)
    {
        $c = new Criteria();
        static::sortableApplyScopeCriteria($c, $scope);

        return ChildSortableTable14Query::doSelectOrderByRank($c, $order, $con);
    }

    /**
     * Return the number of sortable objects in the given scope
     *
     * @param int $scope  the scope of the list
     * @param ConnectionInterface $con    optional connection
     *
     * @return int Count.
     */
    static public function countList($scope, ?ConnectionInterface $con = null): int
    {
        $c = new Criteria();
        $c->add(SortableTable14TableMap::SCOPE_COL, $scope);

        return ChildSortableTable14Query::create(null, $c)->count($con);
    }

    /**
     * Deletes the sortable objects in the given scope
     *
     * @param int $scope  the scope of the list
     * @param ConnectionInterface $con    optional connection
     *
     * @return int number of deleted objects
     */
    static public function deleteList($scope, ?ConnectionInterface $con = null): int
    {
        $c = new Criteria();
        static::sortableApplyScopeCriteria($c, $scope);

        return SortableTable14TableMap::doDelete($c, $con);
    }

    /**
     * Applies all scope fields to the given criteria.
     *
     * @param Criteria $criteria Applies the values directly to this criteria.
     * @param mixed $scope The scope value as scalar type or array($value1, ...).
     * @param string $method The method we use to apply the values.
     *
     * @return void
     */
    static public function sortableApplyScopeCriteria(Criteria $criteria, $scope, string $method = 'add'): void
    {

        $criteria->$method(SortableTable14TableMap::COL_STYLE2, $scope, Criteria::EQUAL);

    }

    /**
     * Adds $delta to all Rank values that are >= $first and <= $last.
     * '$delta' can also be negative.
     *
     * @param int $delta Value to be shifted by, can be negative
     * @param int $first First node to be shifted
     * @param int $last  Last node to be shifted
     * @param int $scope Scope to use for the shift
     * @param ConnectionInterface $con Connection to use.
     */
    static public function sortableShiftRank($delta, $first, $last = null, $scope = null, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        $whereCriteria = new Criteria(SortableTable14TableMap::DATABASE_NAME);
        $criterion = $whereCriteria->getNewCriterion(SortableTable14TableMap::RANK_COL, $first, Criteria::GREATER_EQUAL);
        if (null !== $last) {
            $criterion->addAnd($whereCriteria->getNewCriterion(SortableTable14TableMap::RANK_COL, $last, Criteria::LESS_EQUAL));
        }
        $whereCriteria->add($criterion);
                static::sortableApplyScopeCriteria($whereCriteria, $scope);

        $valuesCriteria = new Criteria(SortableTable14TableMap::DATABASE_NAME);
        $valuesCriteria->add(SortableTable14TableMap::RANK_COL, array('raw' => SortableTable14TableMap::RANK_COL . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

        $whereCriteria->doUpdate($valuesCriteria, $con);
        SortableTable14TableMap::clearInstancePool();
    }

}
