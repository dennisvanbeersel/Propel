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
use Propel\Tests\Bookstore\RecordLabel as ChildRecordLabel;
use Propel\Tests\Bookstore\RecordLabelQuery as ChildRecordLabelQuery;
use Propel\Tests\Bookstore\Map\RecordLabelTableMap;

/**
 * Base class that represents a query for the `record_label` table.
 *
 * @method     ChildRecordLabelQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildRecordLabelQuery orderByAbbr($order = Criteria::ASC) Order by the abbr column
 * @method     ChildRecordLabelQuery orderByName($order = Criteria::ASC) Order by the name column
 *
 * @method     ChildRecordLabelQuery groupById() Group by the id column
 * @method     ChildRecordLabelQuery groupByAbbr() Group by the abbr column
 * @method     ChildRecordLabelQuery groupByName() Group by the name column
 *
 * @method     ChildRecordLabelQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildRecordLabelQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildRecordLabelQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildRecordLabelQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildRecordLabelQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildRecordLabelQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildRecordLabelQuery leftJoinReleasePool($relationAlias = null) Adds a LEFT JOIN clause to the query using the ReleasePool relation
 * @method     ChildRecordLabelQuery rightJoinReleasePool($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ReleasePool relation
 * @method     ChildRecordLabelQuery innerJoinReleasePool($relationAlias = null) Adds a INNER JOIN clause to the query using the ReleasePool relation
 *
 * @method     ChildRecordLabelQuery joinWithReleasePool($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ReleasePool relation
 *
 * @method     ChildRecordLabelQuery leftJoinWithReleasePool() Adds a LEFT JOIN clause and with to the query using the ReleasePool relation
 * @method     ChildRecordLabelQuery rightJoinWithReleasePool() Adds a RIGHT JOIN clause and with to the query using the ReleasePool relation
 * @method     ChildRecordLabelQuery innerJoinWithReleasePool() Adds a INNER JOIN clause and with to the query using the ReleasePool relation
 *
 * @method     \Propel\Tests\Bookstore\ReleasePoolQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildRecordLabel|null findOne(?ConnectionInterface $con = null) Return the first ChildRecordLabel matching the query
 * @method     ChildRecordLabel findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildRecordLabel matching the query, or a new ChildRecordLabel object populated from the query conditions when no match is found
 *
 * @method     ChildRecordLabel|null findOneById(int $id) Return the first ChildRecordLabel filtered by the id column
 * @method     ChildRecordLabel|null findOneByAbbr(string $abbr) Return the first ChildRecordLabel filtered by the abbr column
 * @method     ChildRecordLabel|null findOneByName(string $name) Return the first ChildRecordLabel filtered by the name column
 *
 * @method     ChildRecordLabel requirePk($key, ?ConnectionInterface $con = null) Return the ChildRecordLabel by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildRecordLabel requireOne(?ConnectionInterface $con = null) Return the first ChildRecordLabel matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildRecordLabel requireOneById(int $id) Return the first ChildRecordLabel filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildRecordLabel requireOneByAbbr(string $abbr) Return the first ChildRecordLabel filtered by the abbr column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildRecordLabel requireOneByName(string $name) Return the first ChildRecordLabel filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildRecordLabel[]|Collection find(?ConnectionInterface $con = null) Return ChildRecordLabel objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildRecordLabel> find(?ConnectionInterface $con = null) Return ChildRecordLabel objects based on current ModelCriteria
 *
 * @method     ChildRecordLabel[]|Collection findById(int|array<int> $id) Return ChildRecordLabel objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildRecordLabel> findById(int|array<int> $id) Return ChildRecordLabel objects filtered by the id column
 * @method     ChildRecordLabel[]|Collection findByAbbr(string|array<string> $abbr) Return ChildRecordLabel objects filtered by the abbr column
 * @psalm-method Collection&\Traversable<ChildRecordLabel> findByAbbr(string|array<string> $abbr) Return ChildRecordLabel objects filtered by the abbr column
 * @method     ChildRecordLabel[]|Collection findByName(string|array<string> $name) Return ChildRecordLabel objects filtered by the name column
 * @psalm-method Collection&\Traversable<ChildRecordLabel> findByName(string|array<string> $name) Return ChildRecordLabel objects filtered by the name column
 *
 * @method     ChildRecordLabel[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildRecordLabel> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class RecordLabelQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\RecordLabelQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\RecordLabel', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildRecordLabelQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildRecordLabelQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildRecordLabelQuery) {
            return $criteria;
        }
        $query = new ChildRecordLabelQuery();
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
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$id, $abbr] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildRecordLabel|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(RecordLabelTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = RecordLabelTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildRecordLabel A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, abbr, name FROM record_label WHERE id = :p0 AND abbr = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildRecordLabel $obj */
            $obj = new ChildRecordLabel();
            $obj->hydrate($row);
            RecordLabelTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildRecordLabel|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
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
        $this->addUsingAlias(RecordLabelTableMap::COL_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(RecordLabelTableMap::COL_ABBR, $key[1], Criteria::EQUAL);

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
        if (empty($keys)) {
            $this->add(null, '1<>1', Criteria::CUSTOM);

            return $this;
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(RecordLabelTableMap::COL_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(RecordLabelTableMap::COL_ABBR, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

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
                $this->addUsingAlias(RecordLabelTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(RecordLabelTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(RecordLabelTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the abbr column
     *
     * Example usage:
     * <code>
     * $query->filterByAbbr('fooValue');   // WHERE abbr = 'fooValue'
     * $query->filterByAbbr('%fooValue%', Criteria::LIKE); // WHERE abbr LIKE '%fooValue%'
     * $query->filterByAbbr(['foo', 'bar']); // WHERE abbr IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $abbr The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAbbr($abbr = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($abbr)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(RecordLabelTableMap::COL_ABBR, $abbr, $comparison);

        return $this;
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * $query->filterByName(['foo', 'bar']); // WHERE name IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $name The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByName($name = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(RecordLabelTableMap::COL_NAME, $name, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\ReleasePool object
     *
     * @param \Propel\Tests\Bookstore\ReleasePool|ObjectCollection $releasePool the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByReleasePool($releasePool, ?string $comparison = null)
    {
        if ($releasePool instanceof \Propel\Tests\Bookstore\ReleasePool) {
            $this
                ->addUsingAlias(RecordLabelTableMap::COL_ID, $releasePool->getRecordLabelId(), $comparison)
                ->addUsingAlias(RecordLabelTableMap::COL_ABBR, $releasePool->getRecordLabelAbbr(), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByReleasePool() only accepts arguments of type \Propel\Tests\Bookstore\ReleasePool');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ReleasePool relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinReleasePool(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ReleasePool');

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
            $this->addJoinObject($join, 'ReleasePool');
        }

        return $this;
    }

    /**
     * Use the ReleasePool relation ReleasePool object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\ReleasePoolQuery A secondary query class using the current class as primary query
     */
    public function useReleasePoolQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinReleasePool($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ReleasePool', '\Propel\Tests\Bookstore\ReleasePoolQuery');
    }

    /**
     * Use the ReleasePool relation ReleasePool object
     *
     * @param callable(\Propel\Tests\Bookstore\ReleasePoolQuery):\Propel\Tests\Bookstore\ReleasePoolQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withReleasePoolQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useReleasePoolQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ReleasePool table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\ReleasePoolQuery The inner query object of the EXISTS statement
     */
    public function useReleasePoolExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\ReleasePoolQuery */
        $q = $this->useExistsQuery('ReleasePool', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ReleasePool table for a NOT EXISTS query.
     *
     * @see useReleasePoolExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ReleasePoolQuery The inner query object of the NOT EXISTS statement
     */
    public function useReleasePoolNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ReleasePoolQuery */
        $q = $this->useExistsQuery('ReleasePool', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ReleasePool table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\ReleasePoolQuery The inner query object of the IN statement
     */
    public function useInReleasePoolQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\ReleasePoolQuery */
        $q = $this->useInQuery('ReleasePool', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ReleasePool table for a NOT IN query.
     *
     * @see useReleasePoolInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ReleasePoolQuery The inner query object of the NOT IN statement
     */
    public function useNotInReleasePoolQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ReleasePoolQuery */
        $q = $this->useInQuery('ReleasePool', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildRecordLabel $recordLabel Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($recordLabel = null)
    {
        if ($recordLabel) {
            $this->addCond('pruneCond0', $this->getAliasedColName(RecordLabelTableMap::COL_ID), $recordLabel->getId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(RecordLabelTableMap::COL_ABBR), $recordLabel->getAbbr(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the record_label table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RecordLabelTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            RecordLabelTableMap::clearInstancePool();
            RecordLabelTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(RecordLabelTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(RecordLabelTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            RecordLabelTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            RecordLabelTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
