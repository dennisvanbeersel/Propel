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
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup as ChildAggregateMultipleScoreGroup;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery as ChildAggregateMultipleScoreGroupQuery;
use Propel\Tests\Bookstore\Behavior\Map\AggregateMultipleScoreGroupTableMap;

/**
 * Base class that represents a query for the `aggregate_multiple_score_group` table.
 *
 * @method     ChildAggregateMultipleScoreGroupQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByFirstScoreAt($order = Criteria::ASC) Order by the first_score_at column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByLastScoreAt($order = Criteria::ASC) Order by the last_score_at column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByTotalScore($order = Criteria::ASC) Order by the total_score column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByNumberOfScores($order = Criteria::ASC) Order by the number_of_scores column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByAvgScore($order = Criteria::ASC) Order by the avg_score column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByMinScore($order = Criteria::ASC) Order by the min_score column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByMaxScore($order = Criteria::ASC) Order by the max_score column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByTotalBigScore($order = Criteria::ASC) Order by the total_big_score column
 * @method     ChildAggregateMultipleScoreGroupQuery orderByNumberOfBigScores($order = Criteria::ASC) Order by the number_of_big_scores column
 *
 * @method     ChildAggregateMultipleScoreGroupQuery groupById() Group by the id column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByFirstScoreAt() Group by the first_score_at column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByLastScoreAt() Group by the last_score_at column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByTotalScore() Group by the total_score column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByNumberOfScores() Group by the number_of_scores column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByAvgScore() Group by the avg_score column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByMinScore() Group by the min_score column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByMaxScore() Group by the max_score column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByTotalBigScore() Group by the total_big_score column
 * @method     ChildAggregateMultipleScoreGroupQuery groupByNumberOfBigScores() Group by the number_of_big_scores column
 *
 * @method     ChildAggregateMultipleScoreGroupQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildAggregateMultipleScoreGroupQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildAggregateMultipleScoreGroupQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildAggregateMultipleScoreGroupQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildAggregateMultipleScoreGroupQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildAggregateMultipleScoreGroupQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildAggregateMultipleScoreGroupQuery leftJoinAggregateMultipleScore($relationAlias = null) Adds a LEFT JOIN clause to the query using the AggregateMultipleScore relation
 * @method     ChildAggregateMultipleScoreGroupQuery rightJoinAggregateMultipleScore($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AggregateMultipleScore relation
 * @method     ChildAggregateMultipleScoreGroupQuery innerJoinAggregateMultipleScore($relationAlias = null) Adds a INNER JOIN clause to the query using the AggregateMultipleScore relation
 *
 * @method     ChildAggregateMultipleScoreGroupQuery joinWithAggregateMultipleScore($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the AggregateMultipleScore relation
 *
 * @method     ChildAggregateMultipleScoreGroupQuery leftJoinWithAggregateMultipleScore() Adds a LEFT JOIN clause and with to the query using the AggregateMultipleScore relation
 * @method     ChildAggregateMultipleScoreGroupQuery rightJoinWithAggregateMultipleScore() Adds a RIGHT JOIN clause and with to the query using the AggregateMultipleScore relation
 * @method     ChildAggregateMultipleScoreGroupQuery innerJoinWithAggregateMultipleScore() Adds a INNER JOIN clause and with to the query using the AggregateMultipleScore relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildAggregateMultipleScoreGroup|null findOne(?ConnectionInterface $con = null) Return the first ChildAggregateMultipleScoreGroup matching the query
 * @method     ChildAggregateMultipleScoreGroup findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildAggregateMultipleScoreGroup matching the query, or a new ChildAggregateMultipleScoreGroup object populated from the query conditions when no match is found
 *
 * @method     ChildAggregateMultipleScoreGroup|null findOneById(int $id) Return the first ChildAggregateMultipleScoreGroup filtered by the id column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByFirstScoreAt(string $first_score_at) Return the first ChildAggregateMultipleScoreGroup filtered by the first_score_at column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByLastScoreAt(string $last_score_at) Return the first ChildAggregateMultipleScoreGroup filtered by the last_score_at column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByTotalScore(int $total_score) Return the first ChildAggregateMultipleScoreGroup filtered by the total_score column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByNumberOfScores(int $number_of_scores) Return the first ChildAggregateMultipleScoreGroup filtered by the number_of_scores column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByAvgScore(int $avg_score) Return the first ChildAggregateMultipleScoreGroup filtered by the avg_score column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByMinScore(int $min_score) Return the first ChildAggregateMultipleScoreGroup filtered by the min_score column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByMaxScore(int $max_score) Return the first ChildAggregateMultipleScoreGroup filtered by the max_score column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByTotalBigScore(int $total_big_score) Return the first ChildAggregateMultipleScoreGroup filtered by the total_big_score column
 * @method     ChildAggregateMultipleScoreGroup|null findOneByNumberOfBigScores(int $number_of_big_scores) Return the first ChildAggregateMultipleScoreGroup filtered by the number_of_big_scores column
 *
 * @method     ChildAggregateMultipleScoreGroup requirePk($key, ?ConnectionInterface $con = null) Return the ChildAggregateMultipleScoreGroup by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOne(?ConnectionInterface $con = null) Return the first ChildAggregateMultipleScoreGroup matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAggregateMultipleScoreGroup requireOneById(int $id) Return the first ChildAggregateMultipleScoreGroup filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByFirstScoreAt(string $first_score_at) Return the first ChildAggregateMultipleScoreGroup filtered by the first_score_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByLastScoreAt(string $last_score_at) Return the first ChildAggregateMultipleScoreGroup filtered by the last_score_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByTotalScore(int $total_score) Return the first ChildAggregateMultipleScoreGroup filtered by the total_score column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByNumberOfScores(int $number_of_scores) Return the first ChildAggregateMultipleScoreGroup filtered by the number_of_scores column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByAvgScore(int $avg_score) Return the first ChildAggregateMultipleScoreGroup filtered by the avg_score column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByMinScore(int $min_score) Return the first ChildAggregateMultipleScoreGroup filtered by the min_score column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByMaxScore(int $max_score) Return the first ChildAggregateMultipleScoreGroup filtered by the max_score column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByTotalBigScore(int $total_big_score) Return the first ChildAggregateMultipleScoreGroup filtered by the total_big_score column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregateMultipleScoreGroup requireOneByNumberOfBigScores(int $number_of_big_scores) Return the first ChildAggregateMultipleScoreGroup filtered by the number_of_big_scores column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAggregateMultipleScoreGroup[]|Collection find(?ConnectionInterface $con = null) Return ChildAggregateMultipleScoreGroup objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> find(?ConnectionInterface $con = null) Return ChildAggregateMultipleScoreGroup objects based on current ModelCriteria
 *
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findById(int|array<int> $id) Return ChildAggregateMultipleScoreGroup objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findById(int|array<int> $id) Return ChildAggregateMultipleScoreGroup objects filtered by the id column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByFirstScoreAt(string|array<string> $first_score_at) Return ChildAggregateMultipleScoreGroup objects filtered by the first_score_at column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByFirstScoreAt(string|array<string> $first_score_at) Return ChildAggregateMultipleScoreGroup objects filtered by the first_score_at column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByLastScoreAt(string|array<string> $last_score_at) Return ChildAggregateMultipleScoreGroup objects filtered by the last_score_at column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByLastScoreAt(string|array<string> $last_score_at) Return ChildAggregateMultipleScoreGroup objects filtered by the last_score_at column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByTotalScore(int|array<int> $total_score) Return ChildAggregateMultipleScoreGroup objects filtered by the total_score column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByTotalScore(int|array<int> $total_score) Return ChildAggregateMultipleScoreGroup objects filtered by the total_score column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByNumberOfScores(int|array<int> $number_of_scores) Return ChildAggregateMultipleScoreGroup objects filtered by the number_of_scores column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByNumberOfScores(int|array<int> $number_of_scores) Return ChildAggregateMultipleScoreGroup objects filtered by the number_of_scores column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByAvgScore(int|array<int> $avg_score) Return ChildAggregateMultipleScoreGroup objects filtered by the avg_score column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByAvgScore(int|array<int> $avg_score) Return ChildAggregateMultipleScoreGroup objects filtered by the avg_score column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByMinScore(int|array<int> $min_score) Return ChildAggregateMultipleScoreGroup objects filtered by the min_score column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByMinScore(int|array<int> $min_score) Return ChildAggregateMultipleScoreGroup objects filtered by the min_score column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByMaxScore(int|array<int> $max_score) Return ChildAggregateMultipleScoreGroup objects filtered by the max_score column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByMaxScore(int|array<int> $max_score) Return ChildAggregateMultipleScoreGroup objects filtered by the max_score column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByTotalBigScore(int|array<int> $total_big_score) Return ChildAggregateMultipleScoreGroup objects filtered by the total_big_score column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByTotalBigScore(int|array<int> $total_big_score) Return ChildAggregateMultipleScoreGroup objects filtered by the total_big_score column
 * @method     ChildAggregateMultipleScoreGroup[]|Collection findByNumberOfBigScores(int|array<int> $number_of_big_scores) Return ChildAggregateMultipleScoreGroup objects filtered by the number_of_big_scores column
 * @psalm-method Collection&\Traversable<ChildAggregateMultipleScoreGroup> findByNumberOfBigScores(int|array<int> $number_of_big_scores) Return ChildAggregateMultipleScoreGroup objects filtered by the number_of_big_scores column
 *
 * @method     ChildAggregateMultipleScoreGroup[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildAggregateMultipleScoreGroup> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class AggregateMultipleScoreGroupQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\AggregateMultipleScoreGroupQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScoreGroup', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildAggregateMultipleScoreGroupQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildAggregateMultipleScoreGroupQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildAggregateMultipleScoreGroupQuery) {
            return $criteria;
        }
        $query = new ChildAggregateMultipleScoreGroupQuery();
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
     * @return ChildAggregateMultipleScoreGroup|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = AggregateMultipleScoreGroupTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildAggregateMultipleScoreGroup A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, first_score_at, last_score_at, total_score, number_of_scores, avg_score, min_score, max_score, total_big_score, number_of_big_scores FROM aggregate_multiple_score_group WHERE id = :p0';
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
            /** @var ChildAggregateMultipleScoreGroup $obj */
            $obj = new ChildAggregateMultipleScoreGroup();
            $obj->hydrate($row);
            AggregateMultipleScoreGroupTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildAggregateMultipleScoreGroup|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the first_score_at column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstScoreAt('2011-03-14'); // WHERE first_score_at = '2011-03-14'
     * $query->filterByFirstScoreAt('now'); // WHERE first_score_at = '2011-03-14'
     * $query->filterByFirstScoreAt(array('max' => 'yesterday')); // WHERE first_score_at > '2011-03-13'
     * </code>
     *
     * @param mixed $firstScoreAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFirstScoreAt($firstScoreAt = null, ?string $comparison = null)
    {
        if (is_array($firstScoreAt)) {
            $useMinMax = false;
            if (isset($firstScoreAt['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT, $firstScoreAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($firstScoreAt['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT, $firstScoreAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT, $firstScoreAt, $comparison);

        return $this;
    }

    /**
     * Filter the query on the last_score_at column
     *
     * Example usage:
     * <code>
     * $query->filterByLastScoreAt('2011-03-14'); // WHERE last_score_at = '2011-03-14'
     * $query->filterByLastScoreAt('now'); // WHERE last_score_at = '2011-03-14'
     * $query->filterByLastScoreAt(array('max' => 'yesterday')); // WHERE last_score_at > '2011-03-13'
     * </code>
     *
     * @param mixed $lastScoreAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByLastScoreAt($lastScoreAt = null, ?string $comparison = null)
    {
        if (is_array($lastScoreAt)) {
            $useMinMax = false;
            if (isset($lastScoreAt['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT, $lastScoreAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastScoreAt['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT, $lastScoreAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT, $lastScoreAt, $comparison);

        return $this;
    }

    /**
     * Filter the query on the total_score column
     *
     * Example usage:
     * <code>
     * $query->filterByTotalScore(1234); // WHERE total_score = 1234
     * $query->filterByTotalScore(array(12, 34)); // WHERE total_score IN (12, 34)
     * $query->filterByTotalScore(array('min' => 12)); // WHERE total_score > 12
     * </code>
     *
     * @param mixed $totalScore The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTotalScore($totalScore = null, ?string $comparison = null)
    {
        if (is_array($totalScore)) {
            $useMinMax = false;
            if (isset($totalScore['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE, $totalScore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($totalScore['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE, $totalScore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE, $totalScore, $comparison);

        return $this;
    }

    /**
     * Filter the query on the number_of_scores column
     *
     * Example usage:
     * <code>
     * $query->filterByNumberOfScores(1234); // WHERE number_of_scores = 1234
     * $query->filterByNumberOfScores(array(12, 34)); // WHERE number_of_scores IN (12, 34)
     * $query->filterByNumberOfScores(array('min' => 12)); // WHERE number_of_scores > 12
     * </code>
     *
     * @param mixed $numberOfScores The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByNumberOfScores($numberOfScores = null, ?string $comparison = null)
    {
        if (is_array($numberOfScores)) {
            $useMinMax = false;
            if (isset($numberOfScores['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES, $numberOfScores['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($numberOfScores['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES, $numberOfScores['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES, $numberOfScores, $comparison);

        return $this;
    }

    /**
     * Filter the query on the avg_score column
     *
     * Example usage:
     * <code>
     * $query->filterByAvgScore(1234); // WHERE avg_score = 1234
     * $query->filterByAvgScore(array(12, 34)); // WHERE avg_score IN (12, 34)
     * $query->filterByAvgScore(array('min' => 12)); // WHERE avg_score > 12
     * </code>
     *
     * @param mixed $avgScore The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAvgScore($avgScore = null, ?string $comparison = null)
    {
        if (is_array($avgScore)) {
            $useMinMax = false;
            if (isset($avgScore['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE, $avgScore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($avgScore['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE, $avgScore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE, $avgScore, $comparison);

        return $this;
    }

    /**
     * Filter the query on the min_score column
     *
     * Example usage:
     * <code>
     * $query->filterByMinScore(1234); // WHERE min_score = 1234
     * $query->filterByMinScore(array(12, 34)); // WHERE min_score IN (12, 34)
     * $query->filterByMinScore(array('min' => 12)); // WHERE min_score > 12
     * </code>
     *
     * @param mixed $minScore The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByMinScore($minScore = null, ?string $comparison = null)
    {
        if (is_array($minScore)) {
            $useMinMax = false;
            if (isset($minScore['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE, $minScore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($minScore['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE, $minScore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE, $minScore, $comparison);

        return $this;
    }

    /**
     * Filter the query on the max_score column
     *
     * Example usage:
     * <code>
     * $query->filterByMaxScore(1234); // WHERE max_score = 1234
     * $query->filterByMaxScore(array(12, 34)); // WHERE max_score IN (12, 34)
     * $query->filterByMaxScore(array('min' => 12)); // WHERE max_score > 12
     * </code>
     *
     * @param mixed $maxScore The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByMaxScore($maxScore = null, ?string $comparison = null)
    {
        if (is_array($maxScore)) {
            $useMinMax = false;
            if (isset($maxScore['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE, $maxScore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($maxScore['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE, $maxScore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE, $maxScore, $comparison);

        return $this;
    }

    /**
     * Filter the query on the total_big_score column
     *
     * Example usage:
     * <code>
     * $query->filterByTotalBigScore(1234); // WHERE total_big_score = 1234
     * $query->filterByTotalBigScore(array(12, 34)); // WHERE total_big_score IN (12, 34)
     * $query->filterByTotalBigScore(array('min' => 12)); // WHERE total_big_score > 12
     * </code>
     *
     * @param mixed $totalBigScore The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTotalBigScore($totalBigScore = null, ?string $comparison = null)
    {
        if (is_array($totalBigScore)) {
            $useMinMax = false;
            if (isset($totalBigScore['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE, $totalBigScore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($totalBigScore['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE, $totalBigScore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE, $totalBigScore, $comparison);

        return $this;
    }

    /**
     * Filter the query on the number_of_big_scores column
     *
     * Example usage:
     * <code>
     * $query->filterByNumberOfBigScores(1234); // WHERE number_of_big_scores = 1234
     * $query->filterByNumberOfBigScores(array(12, 34)); // WHERE number_of_big_scores IN (12, 34)
     * $query->filterByNumberOfBigScores(array('min' => 12)); // WHERE number_of_big_scores > 12
     * </code>
     *
     * @param mixed $numberOfBigScores The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByNumberOfBigScores($numberOfBigScores = null, ?string $comparison = null)
    {
        if (is_array($numberOfBigScores)) {
            $useMinMax = false;
            if (isset($numberOfBigScores['min'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES, $numberOfBigScores['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($numberOfBigScores['max'])) {
                $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES, $numberOfBigScores['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES, $numberOfBigScores, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\AggregateMultipleScore object
     *
     * @param \Propel\Tests\Bookstore\Behavior\AggregateMultipleScore|ObjectCollection $aggregateMultipleScore the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAggregateMultipleScore($aggregateMultipleScore, ?string $comparison = null)
    {
        if ($aggregateMultipleScore instanceof \Propel\Tests\Bookstore\Behavior\AggregateMultipleScore) {
            $this
                ->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_ID, $aggregateMultipleScore->getScoreGroupId(), $comparison);

            return $this;
        } elseif ($aggregateMultipleScore instanceof ObjectCollection) {
            $this
                ->useAggregateMultipleScoreQuery()
                ->filterByPrimaryKeys($aggregateMultipleScore->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByAggregateMultipleScore() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\AggregateMultipleScore or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AggregateMultipleScore relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinAggregateMultipleScore(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AggregateMultipleScore');

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
            $this->addJoinObject($join, 'AggregateMultipleScore');
        }

        return $this;
    }

    /**
     * Use the AggregateMultipleScore relation AggregateMultipleScore object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery A secondary query class using the current class as primary query
     */
    public function useAggregateMultipleScoreQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAggregateMultipleScore($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AggregateMultipleScore', '\Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery');
    }

    /**
     * Use the AggregateMultipleScore relation AggregateMultipleScore object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery):\Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withAggregateMultipleScoreQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useAggregateMultipleScoreQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to AggregateMultipleScore table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery The inner query object of the EXISTS statement
     */
    public function useAggregateMultipleScoreExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery */
        $q = $this->useExistsQuery('AggregateMultipleScore', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to AggregateMultipleScore table for a NOT EXISTS query.
     *
     * @see useAggregateMultipleScoreExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery The inner query object of the NOT EXISTS statement
     */
    public function useAggregateMultipleScoreNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery */
        $q = $this->useExistsQuery('AggregateMultipleScore', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to AggregateMultipleScore table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery The inner query object of the IN statement
     */
    public function useInAggregateMultipleScoreQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery */
        $q = $this->useInQuery('AggregateMultipleScore', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to AggregateMultipleScore table for a NOT IN query.
     *
     * @see useAggregateMultipleScoreInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery The inner query object of the NOT IN statement
     */
    public function useNotInAggregateMultipleScoreQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery */
        $q = $this->useInQuery('AggregateMultipleScore', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildAggregateMultipleScoreGroup $aggregateMultipleScoreGroup Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($aggregateMultipleScoreGroup = null)
    {
        if ($aggregateMultipleScoreGroup) {
            $this->addUsingAlias(AggregateMultipleScoreGroupTableMap::COL_ID, $aggregateMultipleScoreGroup->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the aggregate_multiple_score_group table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            AggregateMultipleScoreGroupTableMap::clearInstancePool();
            AggregateMultipleScoreGroupTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            AggregateMultipleScoreGroupTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            AggregateMultipleScoreGroupTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
