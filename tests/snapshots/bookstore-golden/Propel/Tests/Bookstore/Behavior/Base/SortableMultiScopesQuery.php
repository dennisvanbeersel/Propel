<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Tests\Bookstore\Behavior\SortableMultiScopes as ChildSortableMultiScopes;
use Propel\Tests\Bookstore\Behavior\SortableMultiScopesQuery as ChildSortableMultiScopesQuery;
use Propel\Tests\Bookstore\Behavior\Map\SortableMultiScopesTableMap;

/**
 * Base class that represents a query for the `sortable_multi_scopes` table.
 *
 * @method     ChildSortableMultiScopesQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildSortableMultiScopesQuery orderByCategoryId($order = Criteria::ASC) Order by the category_id column
 * @method     ChildSortableMultiScopesQuery orderBySubCategoryId($order = Criteria::ASC) Order by the sub_category_id column
 * @method     ChildSortableMultiScopesQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildSortableMultiScopesQuery orderByPosition($order = Criteria::ASC) Order by the position column
 *
 * @method     ChildSortableMultiScopesQuery groupById() Group by the id column
 * @method     ChildSortableMultiScopesQuery groupByCategoryId() Group by the category_id column
 * @method     ChildSortableMultiScopesQuery groupBySubCategoryId() Group by the sub_category_id column
 * @method     ChildSortableMultiScopesQuery groupByTitle() Group by the title column
 * @method     ChildSortableMultiScopesQuery groupByPosition() Group by the position column
 *
 * @method     ChildSortableMultiScopesQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildSortableMultiScopesQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildSortableMultiScopesQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildSortableMultiScopesQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildSortableMultiScopesQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildSortableMultiScopesQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildSortableMultiScopes|null findOne(?ConnectionInterface $con = null) Return the first ChildSortableMultiScopes matching the query
 * @method     ChildSortableMultiScopes findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildSortableMultiScopes matching the query, or a new ChildSortableMultiScopes object populated from the query conditions when no match is found
 *
 * @method     ChildSortableMultiScopes|null findOneById(int $id) Return the first ChildSortableMultiScopes filtered by the id column
 * @method     ChildSortableMultiScopes|null findOneByCategoryId(int $category_id) Return the first ChildSortableMultiScopes filtered by the category_id column
 * @method     ChildSortableMultiScopes|null findOneBySubCategoryId(int $sub_category_id) Return the first ChildSortableMultiScopes filtered by the sub_category_id column
 * @method     ChildSortableMultiScopes|null findOneByTitle(string $title) Return the first ChildSortableMultiScopes filtered by the title column
 * @method     ChildSortableMultiScopes|null findOneByPosition(int $position) Return the first ChildSortableMultiScopes filtered by the position column
 *
 * @method     ChildSortableMultiScopes requirePk($key, ?ConnectionInterface $con = null) Return the ChildSortableMultiScopes by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableMultiScopes requireOne(?ConnectionInterface $con = null) Return the first ChildSortableMultiScopes matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSortableMultiScopes requireOneById(int $id) Return the first ChildSortableMultiScopes filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableMultiScopes requireOneByCategoryId(int $category_id) Return the first ChildSortableMultiScopes filtered by the category_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableMultiScopes requireOneBySubCategoryId(int $sub_category_id) Return the first ChildSortableMultiScopes filtered by the sub_category_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableMultiScopes requireOneByTitle(string $title) Return the first ChildSortableMultiScopes filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSortableMultiScopes requireOneByPosition(int $position) Return the first ChildSortableMultiScopes filtered by the position column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSortableMultiScopes[]|Collection find(?ConnectionInterface $con = null) Return ChildSortableMultiScopes objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildSortableMultiScopes> find(?ConnectionInterface $con = null) Return ChildSortableMultiScopes objects based on current ModelCriteria
 *
 * @method     ChildSortableMultiScopes[]|Collection findById(int|array<int> $id) Return ChildSortableMultiScopes objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildSortableMultiScopes> findById(int|array<int> $id) Return ChildSortableMultiScopes objects filtered by the id column
 * @method     ChildSortableMultiScopes[]|Collection findByCategoryId(int|array<int> $category_id) Return ChildSortableMultiScopes objects filtered by the category_id column
 * @psalm-method Collection&\Traversable<ChildSortableMultiScopes> findByCategoryId(int|array<int> $category_id) Return ChildSortableMultiScopes objects filtered by the category_id column
 * @method     ChildSortableMultiScopes[]|Collection findBySubCategoryId(int|array<int> $sub_category_id) Return ChildSortableMultiScopes objects filtered by the sub_category_id column
 * @psalm-method Collection&\Traversable<ChildSortableMultiScopes> findBySubCategoryId(int|array<int> $sub_category_id) Return ChildSortableMultiScopes objects filtered by the sub_category_id column
 * @method     ChildSortableMultiScopes[]|Collection findByTitle(string|array<string> $title) Return ChildSortableMultiScopes objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildSortableMultiScopes> findByTitle(string|array<string> $title) Return ChildSortableMultiScopes objects filtered by the title column
 * @method     ChildSortableMultiScopes[]|Collection findByPosition(int|array<int> $position) Return ChildSortableMultiScopes objects filtered by the position column
 * @psalm-method Collection&\Traversable<ChildSortableMultiScopes> findByPosition(int|array<int> $position) Return ChildSortableMultiScopes objects filtered by the position column
 *
 * @method     ChildSortableMultiScopes[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildSortableMultiScopes> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class SortableMultiScopesQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\SortableMultiScopesQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\SortableMultiScopes', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildSortableMultiScopesQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildSortableMultiScopesQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof static) {
            return $criteria;
        }
        if ($criteria instanceof ChildSortableMultiScopesQuery) {
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
     * @return ChildSortableMultiScopes|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = SortableMultiScopesTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildSortableMultiScopes A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, category_id, sub_category_id, title, position FROM sortable_multi_scopes WHERE id = :p0';
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
            /** @var ChildSortableMultiScopes $obj */
            $obj = new ChildSortableMultiScopes();
            $obj->hydrate($row);
            SortableMultiScopesTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildSortableMultiScopes|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(SortableMultiScopesTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(SortableMultiScopesTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SortableMultiScopesTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the category_id column
     *
     * Example usage:
     * <code>
     * $query->filterByCategoryId(1234); // WHERE category_id = 1234
     * $query->filterByCategoryId(array(12, 34)); // WHERE category_id IN (12, 34)
     * $query->filterByCategoryId(array('min' => 12)); // WHERE category_id > 12
     * </code>
     *
     * @param mixed $categoryId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCategoryId($categoryId = null, ?string $comparison = null)
    {
        if (is_array($categoryId)) {
            $useMinMax = false;
            if (isset($categoryId['min'])) {
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_CATEGORY_ID, $categoryId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($categoryId['max'])) {
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_CATEGORY_ID, $categoryId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SortableMultiScopesTableMap::COL_CATEGORY_ID, $categoryId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the sub_category_id column
     *
     * Example usage:
     * <code>
     * $query->filterBySubCategoryId(1234); // WHERE sub_category_id = 1234
     * $query->filterBySubCategoryId(array(12, 34)); // WHERE sub_category_id IN (12, 34)
     * $query->filterBySubCategoryId(array('min' => 12)); // WHERE sub_category_id > 12
     * </code>
     *
     * @param mixed $subCategoryId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySubCategoryId($subCategoryId = null, ?string $comparison = null)
    {
        if (is_array($subCategoryId)) {
            $useMinMax = false;
            if (isset($subCategoryId['min'])) {
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_SUB_CATEGORY_ID, $subCategoryId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($subCategoryId['max'])) {
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_SUB_CATEGORY_ID, $subCategoryId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SortableMultiScopesTableMap::COL_SUB_CATEGORY_ID, $subCategoryId, $comparison);

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

        $this->addUsingAlias(SortableMultiScopesTableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the position column
     *
     * Example usage:
     * <code>
     * $query->filterByPosition(1234); // WHERE position = 1234
     * $query->filterByPosition(array(12, 34)); // WHERE position IN (12, 34)
     * $query->filterByPosition(array('min' => 12)); // WHERE position > 12
     * </code>
     *
     * @param mixed $position The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPosition($position = null, ?string $comparison = null)
    {
        if (is_array($position)) {
            $useMinMax = false;
            if (isset($position['min'])) {
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_POSITION, $position['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($position['max'])) {
                $this->addUsingAlias(SortableMultiScopesTableMap::COL_POSITION, $position['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(SortableMultiScopesTableMap::COL_POSITION, $position, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildSortableMultiScopes $sortableMultiScopes Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($sortableMultiScopes = null)
    {
        if ($sortableMultiScopes) {
            $this->addUsingAlias(SortableMultiScopesTableMap::COL_ID, $sortableMultiScopes->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the sortable_multi_scopes table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            SortableMultiScopesTableMap::clearInstancePool();
            SortableMultiScopesTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(SortableMultiScopesTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            SortableMultiScopesTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            SortableMultiScopesTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // sortable behavior

    /**
     * Returns the objects in a certain list, from the list scope
     *
     * @param int $scopeCategoryId Scope value for column `CategoryId`
     * @param int $scopeSubCategoryId Scope value for column `SubCategoryId`
     *
     * @return $this The current query, for fluid interface
     */
    public function inList($scopeCategoryId, $scopeSubCategoryId = null)
    {

        $scope[] = $scopeCategoryId;
        $scope[] = $scopeSubCategoryId;


        static::sortableApplyScopeCriteria($this, $scope, 'addUsingAlias');

        return $this;
    }

    /**
     * Filter the query based on a rank in the list
     *
     * @param int $rank rank
     * @param int $scopeCategoryId Scope value for column `CategoryId`
     * @param int $scopeSubCategoryId Scope value for column `SubCategoryId`

     *
     * @return $this The current object, for fluid interface
     */
    public function filterByRank($rank, $scopeCategoryId, $scopeSubCategoryId = null)
    {

        $this
            ->inList($scopeCategoryId, $scopeSubCategoryId)
            ->addUsingAlias(SortableMultiScopesTableMap::RANK_COL, $rank, Criteria::EQUAL);

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
                $this->addAscendingOrderByColumn($this->getAliasedColName(SortableMultiScopesTableMap::RANK_COL));

                return $this;
            case Criteria::DESC:
                $this->addDescendingOrderByColumn($this->getAliasedColName(SortableMultiScopesTableMap::RANK_COL));

                return $this;
            default:
                throw new \Propel\Runtime\Exception\PropelException('ChildSortableMultiScopesQuery::orderBy() only accepts "asc" or "desc" as argument');
        }
    }

    /**
     * Get an item from the list based on its rank
     *
     * @param int $rank rank
     * @param int $scopeCategoryId Scope value for column `CategoryId`
     * @param int $scopeSubCategoryId Scope value for column `SubCategoryId`
     * @param ConnectionInterface $con optional connection
     *
     * @return ChildSortableMultiScopes
     */
    public function findOneByRank($rank, $scopeCategoryId, $scopeSubCategoryId = null, ?ConnectionInterface $con = null)
    {

        return $this
            ->filterByRank($rank, $scopeCategoryId, $scopeSubCategoryId)
            ->findOne($con);
    }

    /**
     * Returns a list of objects
     *
     * @param int $scopeCategoryId Scope value for column `CategoryId`
     * @param int $scopeSubCategoryId Scope value for column `SubCategoryId`

     * @param ConnectionInterface $con Connection to use.
     *
     * @return mixed the list of results, formatted by the current formatter
     */
    public function findList($scopeCategoryId, $scopeSubCategoryId = null, $con = null)
    {

        return $this
            ->inList($scopeCategoryId, $scopeSubCategoryId)
            ->orderByRank()
            ->find($con);
    }

    /**
     * Get the highest rank
     *
     * @param int $scopeCategoryId Scope value for column `CategoryId`
     * @param int $scopeSubCategoryId Scope value for column `SubCategoryId`
     * @param ConnectionInterface $con Optional connection
     *
     * @return int|null Highest position
     */
    public function getMaxRank($scopeCategoryId, $scopeSubCategoryId = null, ?ConnectionInterface $con = null): ?int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }
        // shift the objects with a position lower than the one of object
        $this->addSelectColumn('MAX(' . SortableMultiScopesTableMap::RANK_COL . ')');

        $scope[] = $scopeCategoryId;
        $scope[] = $scopeSubCategoryId;


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
            $con = Propel::getConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }
        // shift the objects with a position lower than the one of object
        $this->addSelectColumn('MAX(' . SortableMultiScopesTableMap::RANK_COL . ')');
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
     * @return ChildSortableMultiScopes
     */
    static public function retrieveByRank($rank, $scope = null, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }

        $c = new Criteria;
        $c->add(SortableMultiScopesTableMap::RANK_COL, $rank);
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
            $con = Propel::getServiceContainer()->getReadConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con, $order) {
            $ids = array_keys($order);
            $objects = $this->findPks($ids, $con);
            foreach ($objects as $object) {
                $pk = $object->getPrimaryKey();
                if ($object->getPosition() != $order[$pk]) {
                    $object->setPosition($order[$pk]);
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
            $con = Propel::getServiceContainer()->getReadConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }

        if (null === $criteria) {
            $criteria = new Criteria();
        } elseif ($criteria instanceof Criteria) {
            $criteria = clone $criteria;
        }

        $criteria->clearOrderByColumns();

        if (Criteria::ASC == $order) {
            $criteria->addAscendingOrderByColumn(SortableMultiScopesTableMap::RANK_COL);
        } else {
            $criteria->addDescendingOrderByColumn(SortableMultiScopesTableMap::RANK_COL);
        }

        return ChildSortableMultiScopesQuery::create(null, $criteria)->find($con);
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

        return ChildSortableMultiScopesQuery::doSelectOrderByRank($c, $order, $con);
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
        $c->add(SortableMultiScopesTableMap::SCOPE_COL, $scope);

        return ChildSortableMultiScopesQuery::create(null, $c)->count($con);
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

        return SortableMultiScopesTableMap::doDelete($c, $con);
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

        $criteria->$method(SortableMultiScopesTableMap::COL_CATEGORY_ID, $scope[0], Criteria::EQUAL);

        $criteria->$method(SortableMultiScopesTableMap::COL_SUB_CATEGORY_ID, $scope[1], Criteria::EQUAL);

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
            $con = Propel::getServiceContainer()->getWriteConnection(SortableMultiScopesTableMap::DATABASE_NAME);
        }

        $whereCriteria = new Criteria(SortableMultiScopesTableMap::DATABASE_NAME);
        $criterion = $whereCriteria->getNewCriterion(SortableMultiScopesTableMap::RANK_COL, $first, Criteria::GREATER_EQUAL);
        if (null !== $last) {
            $criterion->addAnd($whereCriteria->getNewCriterion(SortableMultiScopesTableMap::RANK_COL, $last, Criteria::LESS_EQUAL));
        }
        $whereCriteria->add($criterion);
                static::sortableApplyScopeCriteria($whereCriteria, $scope);

        $valuesCriteria = new Criteria(SortableMultiScopesTableMap::DATABASE_NAME);
        $valuesCriteria->add(SortableMultiScopesTableMap::RANK_COL, array('raw' => SortableMultiScopesTableMap::RANK_COL . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

        $whereCriteria->doUpdate($valuesCriteria, $con);
        SortableMultiScopesTableMap::clearInstancePool();
    }

}
