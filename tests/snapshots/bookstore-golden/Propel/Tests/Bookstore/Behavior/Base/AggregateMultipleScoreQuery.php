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
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScore as ChildAggregateMultipleScore;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery as ChildAggregateMultipleScoreQuery;
use Propel\Tests\Bookstore\Behavior\Map\AggregateMultipleScoreTableMap;

/**
 * Base class that represents a query for the `aggregate_multiple_score` table.
 *
 * @method     ChildAggregateMultipleScoreQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildAggregateMultipleScoreQuery orderByScore($order = Criteria::ASC) Order by the score column
 * @method     ChildAggregateMultipleScoreQuery orderByScoredAt($order = Criteria::ASC) Order by the scored_at column
 * @method     ChildAggregateMultipleScoreQuery orderByScoreGroupId($order = Criteria::ASC) Order by the score_group_id column
 *
 * @method     ChildAggregateMultipleScoreQuery groupById() Group by the id column
 * @method     ChildAggregateMultipleScoreQuery groupByScore() Group by the score column
 * @method     ChildAggregateMultipleScoreQuery groupByScoredAt() Group by the scored_at column
 * @method     ChildAggregateMultipleScoreQuery groupByScoreGroupId() Group by the score_group_id column
 *
 * @method     ChildAggregateMultipleScoreQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildAggregateMultipleScoreQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildAggregateMultipleScoreQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildAggregateMultipleScoreQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildAggregateMultipleScoreQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildAggregateMultipleScoreQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildAggregateMultipleScoreQuery leftJoinAggregateMultipleScoreGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AggregateMultipleScoreGroup relation
 * @method     ChildAggregateMultipleScoreQuery rightJoinAggregateMultipleScoreGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AggregateMultipleScoreGroup relation
 * @method     ChildAggregateMultipleScoreQuery innerJoinAggregateMultipleScoreGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AggregateMultipleScoreGroup relation
 *
 * @method     ChildAggregateMultipleScoreQuery joinWithAggregateMultipleScoreGroup($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the AggregateMultipleScoreGroup relation
 *
 * @method     ChildAggregateMultipleScoreQuery leftJoinWithAggregateMultipleScoreGroup() Adds a LEFT JOIN clause and with to the query using the AggregateMultipleScoreGroup relation
 * @method     ChildAggregateMultipleScoreQuery rightJoinWithAggregateMultipleScoreGroup() Adds a RIGHT JOIN clause and with to the query using the AggregateMultipleScoreGroup relation
 * @method     ChildAggregateMultipleScoreQuery innerJoinWithAggregateMultipleScoreGroup() Adds a INNER JOIN clause and with to the query using the AggregateMultipleScoreGroup relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildAggregateMultipleScore|null findOne(?ConnectionInterface $con = null) Return the first ChildAggregateMultipleScore matching the query
 * @method     ChildAggregateMultipleScore findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildAggregateMultipleScore matching the query, or a new ChildAggregateMultipleScore object populated from the query conditions when no match is found
 *
 * @method     ChildAggregateMultipleScore|null findOneById(int $id) Return the first ChildAggregateMultipleScore filtered by the id column
 * @method     ChildAggregateMultipleScore|null findOneByScore(int $score) Return the first ChildAggregateMultipleScore filtered by the score column
 * @method     ChildAggregateMultipleScore|null findOneByScoredAt(string $scored_at) Return the first ChildAggregateMultipleScore filtered by the scored_at column
 * @method     ChildAggregateMultipleScore|null findOneByScoreGroupId(int $score_group_id) Return the first ChildAggregateMultipleScore filtered by the score_group_id column
 *
 * @method     ChildAggregateMultipleScore requirePk($key, ?ConnectionInterface $con = null) Return the ChildAggregateMultipleScore by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScore requireOne(?ConnectionInterface $con = null) Return the first ChildAggregateMultipleScore matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAggregateMultipleScore requireOneById(int $id) Return the first ChildAggregateMultipleScore filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScore requireOneByScore(int $score) Return the first ChildAggregateMultipleScore filtered by the score column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScore requireOneByScoredAt(string $scored_at) Return the first ChildAggregateMultipleScore filtered by the scored_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScore requireOneByScoreGroupId(int $score_group_id) Return the first ChildAggregateMultipleScore filtered by the score_group_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAggregateMultipleScore[]|Collection find(?ConnectionInterface $con = null) Return ChildAggregateMultipleScore objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScore> find(?ConnectionInterface $con = null) Return ChildAggregateMultipleScore objects based on current ModelCriteria
 *
 * @method     ChildAggregateMultipleScore[]|Collection findById(int|array<int> $id) Return ChildAggregateMultipleScore objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScore> findById(int|array<int> $id) Return ChildAggregateMultipleScore objects filtered by the id column
 * @method     ChildAggregateMultipleScore[]|Collection findByScore(int|array<int> $score) Return ChildAggregateMultipleScore objects filtered by the score column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScore> findByScore(int|array<int> $score) Return ChildAggregateMultipleScore objects filtered by the score column
 * @method     ChildAggregateMultipleScore[]|Collection findByScoredAt(string|array<string> $scored_at) Return ChildAggregateMultipleScore objects filtered by the scored_at column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScore> findByScoredAt(string|array<string> $scored_at) Return ChildAggregateMultipleScore objects filtered by the scored_at column
 * @method     ChildAggregateMultipleScore[]|Collection findByScoreGroupId(int|array<int> $score_group_id) Return ChildAggregateMultipleScore objects filtered by the score_group_id column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScore> findByScoreGroupId(int|array<int> $score_group_id) Return ChildAggregateMultipleScore objects filtered by the score_group_id column
 *
 * @method     ChildAggregateMultipleScore[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildAggregateMultipleScore> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class AggregateMultipleScoreQuery extends ModelCriteria
{

    // aggregate_multiple_columns_relation_score_group_aggregates behavior
    /**
     * @var array|null
     */
    protected $aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores;

    // aggregate_multiple_columns_relation_another_score_group_aggregates behavior
    /**
     * @var array|null
     */
    protected $aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s;
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\AggregateMultipleScoreQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScore', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildAggregateMultipleScoreQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildAggregateMultipleScoreQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof static) {
            return $criteria;
        }
        if ($criteria instanceof ChildAggregateMultipleScoreQuery) {
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
     * @return ChildAggregateMultipleScore|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(AggregateMultipleScoreTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = AggregateMultipleScoreTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildAggregateMultipleScore A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, score, scored_at, score_group_id FROM aggregate_multiple_score WHERE id = :p0';
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
            /** @var ChildAggregateMultipleScore $obj */
            $obj = new ChildAggregateMultipleScore();
            $obj->hydrate($row);
            AggregateMultipleScoreTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildAggregateMultipleScore|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the score column
     *
     * Example usage:
     * <code>
     * $query->filterByScore(1234); // WHERE score = 1234
     * $query->filterByScore(array(12, 34)); // WHERE score IN (12, 34)
     * $query->filterByScore(array('min' => 12)); // WHERE score > 12
     * </code>
     *
     * @param mixed $score The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByScore($score = null, ?string $comparison = null)
    {
        if (is_array($score)) {
            $useMinMax = false;
            if (isset($score['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE, $score['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($score['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE, $score['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE, $score, $comparison);

        return $this;
    }

    /**
     * Filter the query on the scored_at column
     *
     * Example usage:
     * <code>
     * $query->filterByScoredAt('2011-03-14'); // WHERE scored_at = '2011-03-14'
     * $query->filterByScoredAt('now'); // WHERE scored_at = '2011-03-14'
     * $query->filterByScoredAt(array('max' => 'yesterday')); // WHERE scored_at > '2011-03-13'
     * </code>
     *
     * @param mixed $scoredAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByScoredAt($scoredAt = null, ?string $comparison = null)
    {
        if (is_array($scoredAt)) {
            $useMinMax = false;
            if (isset($scoredAt['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORED_AT, $scoredAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($scoredAt['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORED_AT, $scoredAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORED_AT, $scoredAt, $comparison);

        return $this;
    }

    /**
     * Filter the query on the score_group_id column
     *
     * Example usage:
     * <code>
     * $query->filterByScoreGroupId(1234); // WHERE score_group_id = 1234
     * $query->filterByScoreGroupId(array(12, 34)); // WHERE score_group_id IN (12, 34)
     * $query->filterByScoreGroupId(array('min' => 12)); // WHERE score_group_id > 12
     * </code>
     *
     * @see       filterByAggregateMultipleScoreGroup()
     *
     * @param mixed $scoreGroupId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByScoreGroupId($scoreGroupId = null, ?string $comparison = null)
    {
        if (is_array($scoreGroupId)) {
            $useMinMax = false;
            if (isset($scoreGroupId['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID, $scoreGroupId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($scoreGroupId['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID, $scoreGroupId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID, $scoreGroupId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup object
     *
     * @param \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup|ObjectCollection $aggregateMultipleScoreGroup The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAggregateMultipleScoreGroup($aggregateMultipleScoreGroup, ?string $comparison = null)
    {
        if ($aggregateMultipleScoreGroup instanceof \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup) {
            return $this
                ->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID, $aggregateMultipleScoreGroup->getId(), $comparison);
        } elseif ($aggregateMultipleScoreGroup instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID, $aggregateMultipleScoreGroup->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByAggregateMultipleScoreGroup() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AggregateMultipleScoreGroup relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinAggregateMultipleScoreGroup(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AggregateMultipleScoreGroup');

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
            $this->addJoinObject($join, 'AggregateMultipleScoreGroup');
        }

        return $this;
    }

    /**
     * Use the AggregateMultipleScoreGroup relation AggregateMultipleScoreGroup object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery A secondary query class using the current class as primary query
     */
    public function useAggregateMultipleScoreGroupQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAggregateMultipleScoreGroup($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AggregateMultipleScoreGroup', '\Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery');
    }

    /**
     * Use the AggregateMultipleScoreGroup relation AggregateMultipleScoreGroup object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery):\Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withAggregateMultipleScoreGroupQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useAggregateMultipleScoreGroupQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to AggregateMultipleScoreGroup table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery The inner query object of the EXISTS statement
     */
    public function useAggregateMultipleScoreGroupExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery */
        $q = $this->useExistsQuery('AggregateMultipleScoreGroup', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to AggregateMultipleScoreGroup table for a NOT EXISTS query.
     *
     * @see useAggregateMultipleScoreGroupExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery The inner query object of the NOT EXISTS statement
     */
    public function useAggregateMultipleScoreGroupNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery */
        $q = $this->useExistsQuery('AggregateMultipleScoreGroup', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to AggregateMultipleScoreGroup table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery The inner query object of the IN statement
     */
    public function useInAggregateMultipleScoreGroupQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery */
        $q = $this->useInQuery('AggregateMultipleScoreGroup', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to AggregateMultipleScoreGroup table for a NOT IN query.
     *
     * @see useAggregateMultipleScoreGroupInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery The inner query object of the NOT IN statement
     */
    public function useNotInAggregateMultipleScoreGroupQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery */
        $q = $this->useInQuery('AggregateMultipleScoreGroup', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildAggregateMultipleScore $aggregateMultipleScore Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($aggregateMultipleScore = null)
    {
        if ($aggregateMultipleScore) {
            $this->addUsingAlias(AggregateMultipleScoreTableMap::COL_ID, $aggregateMultipleScore->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Code to execute before every DELETE statement
     *
     * @param ConnectionInterface $con The connection object used by the query
     * @return int|null
     */
    protected function basePreDelete(ConnectionInterface $con): ?int
    {
        // aggregate_multiple_columns_relation_score_group_aggregates behavior
        $this->findRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores($con);
        // aggregate_multiple_columns_relation_another_score_group_aggregates behavior
        $this->findRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s($con);

        return $this->preDelete($con);
    }

    /**
     * Code to execute after every DELETE statement
     *
     * @param int $affectedRows the number of deleted rows
     * @param ConnectionInterface $con The connection object used by the query
     * @return int|null
     */
    protected function basePostDelete(int $affectedRows, ConnectionInterface $con): ?int
    {
        // aggregate_multiple_columns_relation_score_group_aggregates behavior
        $this->updateRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores($con);
        // aggregate_multiple_columns_relation_another_score_group_aggregates behavior
        $this->updateRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s($con);

        return $this->postDelete($affectedRows, $con);
    }

    /**
     * Code to execute before every UPDATE statement
     *
     * @param array $values The associative array of columns and values for the update
     * @param ConnectionInterface $con The connection object used by the query
     * @param bool $forceIndividualSaves If false (default), the resulting call is a Criteria::doUpdate(), otherwise it is a series of save() calls on all the found objects
     *
     * @return int|null
     */
    protected function basePreUpdate(&$values, ConnectionInterface $con, $forceIndividualSaves = false): ?int
    {
        // aggregate_multiple_columns_relation_score_group_aggregates behavior
        $this->findRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores($con);
        // aggregate_multiple_columns_relation_another_score_group_aggregates behavior
        $this->findRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s($con);

        return $this->preUpdate($values, $con, $forceIndividualSaves);
    }

    /**
     * Code to execute after every UPDATE statement
     *
     * @param int $affectedRows the number of updated rows
     * @param ConnectionInterface $con The connection object used by the query
     *
     * @return int|null
     */
    protected function basePostUpdate($affectedRows, ConnectionInterface $con): ?int
    {
        // aggregate_multiple_columns_relation_score_group_aggregates behavior
        $this->updateRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores($con);
        // aggregate_multiple_columns_relation_another_score_group_aggregates behavior
        $this->updateRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s($con);

        return $this->postUpdate($affectedRows, $con);
    }

    /**
     * Deletes all rows from the aggregate_multiple_score table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            AggregateMultipleScoreTableMap::clearInstancePool();
            AggregateMultipleScoreTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(AggregateMultipleScoreTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            AggregateMultipleScoreTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            AggregateMultipleScoreTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // aggregate_multiple_columns_relation_score_group_aggregates behavior

    /**
     * Finds the related AggregateMultipleScoreGroup objects and keep them for later
     *
     * @param ConnectionInterface $con A connection object
     */
    protected function findRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores($con)
    {
        $criteria = clone $this;
        if ($this->useAliasInSQL) {
            $alias = $this->getModelAlias();
            $criteria->removeAlias($alias);
        } else {
            $alias = '';
        }
        $this->aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores = \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery::create()
            ->joinAggregateMultipleScore($alias)
            ->mergeWith($criteria)
            ->find($con);
    }

    protected function updateRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores($con)
    {
        foreach ($this->aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores as $aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore) {
            $aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore->updateAggregatedColumnsFromAggregateMultipleScore($con);
        }
        $this->aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScores = [];
    }

    // aggregate_multiple_columns_relation_another_score_group_aggregates behavior

    /**
     * Finds the related AggregateMultipleScoreGroup objects and keep them for later
     *
     * @param ConnectionInterface $con A connection object
     */
    protected function findRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s($con)
    {
        $criteria = clone $this;
        if ($this->useAliasInSQL) {
            $alias = $this->getModelAlias();
            $criteria->removeAlias($alias);
        } else {
            $alias = '';
        }
        $this->aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s = \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery::create()
            ->joinAggregateMultipleScore($alias)
            ->mergeWith($criteria)
            ->find($con);
    }

    protected function updateRelatedAggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s($con)
    {
        foreach ($this->aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s as $aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1) {
            $aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1->updateAggregatedColumnsFromAggregateMultipleScore1($con);
        }
        $this->aggregateMultipleScoreGroupAggregatedColumnsFromAggregateMultipleScore1s = [];
    }

}
