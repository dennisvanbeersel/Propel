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
use Propel\Tests\Bookstore\CompositeEssay as ChildCompositeEssay;
use Propel\Tests\Bookstore\CompositeEssayQuery as ChildCompositeEssayQuery;
use Propel\Tests\Bookstore\Map\CompositeEssayTableMap;

/**
 * Base class that represents a query for the `composite_essay` table.
 *
 * @method     ChildCompositeEssayQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildCompositeEssayQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildCompositeEssayQuery orderByFirstEssayId($order = Criteria::ASC) Order by the first_essay_id column
 * @method     ChildCompositeEssayQuery orderBySecondEssayId($order = Criteria::ASC) Order by the second_essay_id column
 *
 * @method     ChildCompositeEssayQuery groupById() Group by the id column
 * @method     ChildCompositeEssayQuery groupByTitle() Group by the title column
 * @method     ChildCompositeEssayQuery groupByFirstEssayId() Group by the first_essay_id column
 * @method     ChildCompositeEssayQuery groupBySecondEssayId() Group by the second_essay_id column
 *
 * @method     ChildCompositeEssayQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildCompositeEssayQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildCompositeEssayQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildCompositeEssayQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildCompositeEssayQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildCompositeEssayQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildCompositeEssayQuery leftJoinfirstEssay($relationAlias = null) Adds a LEFT JOIN clause to the query using the firstEssay relation
 * @method     ChildCompositeEssayQuery rightJoinfirstEssay($relationAlias = null) Adds a RIGHT JOIN clause to the query using the firstEssay relation
 * @method     ChildCompositeEssayQuery innerJoinfirstEssay($relationAlias = null) Adds a INNER JOIN clause to the query using the firstEssay relation
 *
 * @method     ChildCompositeEssayQuery joinWithfirstEssay($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the firstEssay relation
 *
 * @method     ChildCompositeEssayQuery leftJoinWithfirstEssay() Adds a LEFT JOIN clause and with to the query using the firstEssay relation
 * @method     ChildCompositeEssayQuery rightJoinWithfirstEssay() Adds a RIGHT JOIN clause and with to the query using the firstEssay relation
 * @method     ChildCompositeEssayQuery innerJoinWithfirstEssay() Adds a INNER JOIN clause and with to the query using the firstEssay relation
 *
 * @method     ChildCompositeEssayQuery leftJoinsecondEssay($relationAlias = null) Adds a LEFT JOIN clause to the query using the secondEssay relation
 * @method     ChildCompositeEssayQuery rightJoinsecondEssay($relationAlias = null) Adds a RIGHT JOIN clause to the query using the secondEssay relation
 * @method     ChildCompositeEssayQuery innerJoinsecondEssay($relationAlias = null) Adds a INNER JOIN clause to the query using the secondEssay relation
 *
 * @method     ChildCompositeEssayQuery joinWithsecondEssay($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the secondEssay relation
 *
 * @method     ChildCompositeEssayQuery leftJoinWithsecondEssay() Adds a LEFT JOIN clause and with to the query using the secondEssay relation
 * @method     ChildCompositeEssayQuery rightJoinWithsecondEssay() Adds a RIGHT JOIN clause and with to the query using the secondEssay relation
 * @method     ChildCompositeEssayQuery innerJoinWithsecondEssay() Adds a INNER JOIN clause and with to the query using the secondEssay relation
 *
 * @method     ChildCompositeEssayQuery leftJoinCompositeEssayRelatedById0($relationAlias = null) Adds a LEFT JOIN clause to the query using the CompositeEssayRelatedById0 relation
 * @method     ChildCompositeEssayQuery rightJoinCompositeEssayRelatedById0($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CompositeEssayRelatedById0 relation
 * @method     ChildCompositeEssayQuery innerJoinCompositeEssayRelatedById0($relationAlias = null) Adds a INNER JOIN clause to the query using the CompositeEssayRelatedById0 relation
 *
 * @method     ChildCompositeEssayQuery joinWithCompositeEssayRelatedById0($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the CompositeEssayRelatedById0 relation
 *
 * @method     ChildCompositeEssayQuery leftJoinWithCompositeEssayRelatedById0() Adds a LEFT JOIN clause and with to the query using the CompositeEssayRelatedById0 relation
 * @method     ChildCompositeEssayQuery rightJoinWithCompositeEssayRelatedById0() Adds a RIGHT JOIN clause and with to the query using the CompositeEssayRelatedById0 relation
 * @method     ChildCompositeEssayQuery innerJoinWithCompositeEssayRelatedById0() Adds a INNER JOIN clause and with to the query using the CompositeEssayRelatedById0 relation
 *
 * @method     ChildCompositeEssayQuery leftJoinCompositeEssayRelatedById1($relationAlias = null) Adds a LEFT JOIN clause to the query using the CompositeEssayRelatedById1 relation
 * @method     ChildCompositeEssayQuery rightJoinCompositeEssayRelatedById1($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CompositeEssayRelatedById1 relation
 * @method     ChildCompositeEssayQuery innerJoinCompositeEssayRelatedById1($relationAlias = null) Adds a INNER JOIN clause to the query using the CompositeEssayRelatedById1 relation
 *
 * @method     ChildCompositeEssayQuery joinWithCompositeEssayRelatedById1($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the CompositeEssayRelatedById1 relation
 *
 * @method     ChildCompositeEssayQuery leftJoinWithCompositeEssayRelatedById1() Adds a LEFT JOIN clause and with to the query using the CompositeEssayRelatedById1 relation
 * @method     ChildCompositeEssayQuery rightJoinWithCompositeEssayRelatedById1() Adds a RIGHT JOIN clause and with to the query using the CompositeEssayRelatedById1 relation
 * @method     ChildCompositeEssayQuery innerJoinWithCompositeEssayRelatedById1() Adds a INNER JOIN clause and with to the query using the CompositeEssayRelatedById1 relation
 *
 * @method     \Propel\Tests\Bookstore\CompositeEssayQuery|\Propel\Tests\Bookstore\CompositeEssayQuery|\Propel\Tests\Bookstore\CompositeEssayQuery|\Propel\Tests\Bookstore\CompositeEssayQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildCompositeEssay|null findOne(?ConnectionInterface $con = null) Return the first ChildCompositeEssay matching the query
 * @method     ChildCompositeEssay findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildCompositeEssay matching the query, or a new ChildCompositeEssay object populated from the query conditions when no match is found
 *
 * @method     ChildCompositeEssay|null findOneById(int $id) Return the first ChildCompositeEssay filtered by the id column
 * @method     ChildCompositeEssay|null findOneByTitle(string $title) Return the first ChildCompositeEssay filtered by the title column
 * @method     ChildCompositeEssay|null findOneByFirstEssayId(int $first_essay_id) Return the first ChildCompositeEssay filtered by the first_essay_id column
 * @method     ChildCompositeEssay|null findOneBySecondEssayId(int $second_essay_id) Return the first ChildCompositeEssay filtered by the second_essay_id column
 *
 * @method     ChildCompositeEssay requirePk($key, ?ConnectionInterface $con = null) Return the ChildCompositeEssay by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildCompositeEssay requireOne(?ConnectionInterface $con = null) Return the first ChildCompositeEssay matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildCompositeEssay requireOneById(int $id) Return the first ChildCompositeEssay filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildCompositeEssay requireOneByTitle(string $title) Return the first ChildCompositeEssay filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildCompositeEssay requireOneByFirstEssayId(int $first_essay_id) Return the first ChildCompositeEssay filtered by the first_essay_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildCompositeEssay requireOneBySecondEssayId(int $second_essay_id) Return the first ChildCompositeEssay filtered by the second_essay_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildCompositeEssay[]|Collection find(?ConnectionInterface $con = null) Return ChildCompositeEssay objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildCompositeEssay> find(?ConnectionInterface $con = null) Return ChildCompositeEssay objects based on current ModelCriteria
 *
 * @method     ChildCompositeEssay[]|Collection findById(int|array<int> $id) Return ChildCompositeEssay objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildCompositeEssay> findById(int|array<int> $id) Return ChildCompositeEssay objects filtered by the id column
 * @method     ChildCompositeEssay[]|Collection findByTitle(string|array<string> $title) Return ChildCompositeEssay objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildCompositeEssay> findByTitle(string|array<string> $title) Return ChildCompositeEssay objects filtered by the title column
 * @method     ChildCompositeEssay[]|Collection findByFirstEssayId(int|array<int> $first_essay_id) Return ChildCompositeEssay objects filtered by the first_essay_id column
 * @psalm-method Collection&\Traversable<ChildCompositeEssay> findByFirstEssayId(int|array<int> $first_essay_id) Return ChildCompositeEssay objects filtered by the first_essay_id column
 * @method     ChildCompositeEssay[]|Collection findBySecondEssayId(int|array<int> $second_essay_id) Return ChildCompositeEssay objects filtered by the second_essay_id column
 * @psalm-method Collection&\Traversable<ChildCompositeEssay> findBySecondEssayId(int|array<int> $second_essay_id) Return ChildCompositeEssay objects filtered by the second_essay_id column
 *
 * @method     ChildCompositeEssay[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildCompositeEssay> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class CompositeEssayQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\CompositeEssayQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\CompositeEssay', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildCompositeEssayQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildCompositeEssayQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildCompositeEssayQuery) {
            return $criteria;
        }
        $query = new ChildCompositeEssayQuery();
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
     * @return ChildCompositeEssay|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(CompositeEssayTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = CompositeEssayTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildCompositeEssay A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, first_essay_id, second_essay_id FROM composite_essay WHERE id = :p0';
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
            /** @var ChildCompositeEssay $obj */
            $obj = new ChildCompositeEssay();
            $obj->hydrate($row);
            CompositeEssayTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildCompositeEssay|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(CompositeEssayTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(CompositeEssayTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(CompositeEssayTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(CompositeEssayTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(CompositeEssayTableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(CompositeEssayTableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the first_essay_id column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstEssayId(1234); // WHERE first_essay_id = 1234
     * $query->filterByFirstEssayId(array(12, 34)); // WHERE first_essay_id IN (12, 34)
     * $query->filterByFirstEssayId(array('min' => 12)); // WHERE first_essay_id > 12
     * </code>
     *
     * @see       filterByfirstEssay()
     *
     * @param mixed $firstEssayId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFirstEssayId($firstEssayId = null, ?string $comparison = null)
    {
        if (is_array($firstEssayId)) {
            $useMinMax = false;
            if (isset($firstEssayId['min'])) {
                $this->addUsingAlias(CompositeEssayTableMap::COL_FIRST_ESSAY_ID, $firstEssayId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($firstEssayId['max'])) {
                $this->addUsingAlias(CompositeEssayTableMap::COL_FIRST_ESSAY_ID, $firstEssayId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(CompositeEssayTableMap::COL_FIRST_ESSAY_ID, $firstEssayId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the second_essay_id column
     *
     * Example usage:
     * <code>
     * $query->filterBySecondEssayId(1234); // WHERE second_essay_id = 1234
     * $query->filterBySecondEssayId(array(12, 34)); // WHERE second_essay_id IN (12, 34)
     * $query->filterBySecondEssayId(array('min' => 12)); // WHERE second_essay_id > 12
     * </code>
     *
     * @see       filterBysecondEssay()
     *
     * @param mixed $secondEssayId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySecondEssayId($secondEssayId = null, ?string $comparison = null)
    {
        if (is_array($secondEssayId)) {
            $useMinMax = false;
            if (isset($secondEssayId['min'])) {
                $this->addUsingAlias(CompositeEssayTableMap::COL_SECOND_ESSAY_ID, $secondEssayId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($secondEssayId['max'])) {
                $this->addUsingAlias(CompositeEssayTableMap::COL_SECOND_ESSAY_ID, $secondEssayId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(CompositeEssayTableMap::COL_SECOND_ESSAY_ID, $secondEssayId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\CompositeEssay object
     *
     * @param \Propel\Tests\Bookstore\CompositeEssay|ObjectCollection $compositeEssay The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByfirstEssay($compositeEssay, ?string $comparison = null)
    {
        if ($compositeEssay instanceof \Propel\Tests\Bookstore\CompositeEssay) {
            return $this
                ->addUsingAlias(CompositeEssayTableMap::COL_FIRST_ESSAY_ID, $compositeEssay->getId(), $comparison);
        } elseif ($compositeEssay instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(CompositeEssayTableMap::COL_FIRST_ESSAY_ID, $compositeEssay->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByfirstEssay() only accepts arguments of type \Propel\Tests\Bookstore\CompositeEssay or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the firstEssay relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinfirstEssay(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('firstEssay');

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
            $this->addJoinObject($join, 'firstEssay');
        }

        return $this;
    }

    /**
     * Use the firstEssay relation CompositeEssay object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery A secondary query class using the current class as primary query
     */
    public function usefirstEssayQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinfirstEssay($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'firstEssay', '\Propel\Tests\Bookstore\CompositeEssayQuery');
    }

    /**
     * Use the firstEssay relation CompositeEssay object
     *
     * @param callable(\Propel\Tests\Bookstore\CompositeEssayQuery):\Propel\Tests\Bookstore\CompositeEssayQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withfirstEssayQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->usefirstEssayQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the firstEssay relation to the CompositeEssay table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the EXISTS statement
     */
    public function usefirstEssayExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('firstEssay', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the firstEssay relation to the CompositeEssay table for a NOT EXISTS query.
     *
     * @see usefirstEssayExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT EXISTS statement
     */
    public function usefirstEssayNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('firstEssay', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the firstEssay relation to the CompositeEssay table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the IN statement
     */
    public function useInfirstEssayQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('firstEssay', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the firstEssay relation to the CompositeEssay table for a NOT IN query.
     *
     * @see usefirstEssayInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT IN statement
     */
    public function useNotInfirstEssayQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('firstEssay', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\CompositeEssay object
     *
     * @param \Propel\Tests\Bookstore\CompositeEssay|ObjectCollection $compositeEssay The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBysecondEssay($compositeEssay, ?string $comparison = null)
    {
        if ($compositeEssay instanceof \Propel\Tests\Bookstore\CompositeEssay) {
            return $this
                ->addUsingAlias(CompositeEssayTableMap::COL_SECOND_ESSAY_ID, $compositeEssay->getId(), $comparison);
        } elseif ($compositeEssay instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(CompositeEssayTableMap::COL_SECOND_ESSAY_ID, $compositeEssay->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterBysecondEssay() only accepts arguments of type \Propel\Tests\Bookstore\CompositeEssay or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the secondEssay relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinsecondEssay(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('secondEssay');

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
            $this->addJoinObject($join, 'secondEssay');
        }

        return $this;
    }

    /**
     * Use the secondEssay relation CompositeEssay object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery A secondary query class using the current class as primary query
     */
    public function usesecondEssayQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinsecondEssay($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'secondEssay', '\Propel\Tests\Bookstore\CompositeEssayQuery');
    }

    /**
     * Use the secondEssay relation CompositeEssay object
     *
     * @param callable(\Propel\Tests\Bookstore\CompositeEssayQuery):\Propel\Tests\Bookstore\CompositeEssayQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withsecondEssayQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->usesecondEssayQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the secondEssay relation to the CompositeEssay table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the EXISTS statement
     */
    public function usesecondEssayExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('secondEssay', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the secondEssay relation to the CompositeEssay table for a NOT EXISTS query.
     *
     * @see usesecondEssayExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT EXISTS statement
     */
    public function usesecondEssayNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('secondEssay', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the secondEssay relation to the CompositeEssay table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the IN statement
     */
    public function useInsecondEssayQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('secondEssay', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the secondEssay relation to the CompositeEssay table for a NOT IN query.
     *
     * @see usesecondEssayInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT IN statement
     */
    public function useNotInsecondEssayQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('secondEssay', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\CompositeEssay object
     *
     * @param \Propel\Tests\Bookstore\CompositeEssay|ObjectCollection $compositeEssay the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCompositeEssayRelatedById0($compositeEssay, ?string $comparison = null)
    {
        if ($compositeEssay instanceof \Propel\Tests\Bookstore\CompositeEssay) {
            $this
                ->addUsingAlias(CompositeEssayTableMap::COL_ID, $compositeEssay->getFirstEssayId(), $comparison);

            return $this;
        } elseif ($compositeEssay instanceof ObjectCollection) {
            $this
                ->useCompositeEssayRelatedById0Query()
                ->filterByPrimaryKeys($compositeEssay->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByCompositeEssayRelatedById0() only accepts arguments of type \Propel\Tests\Bookstore\CompositeEssay or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CompositeEssayRelatedById0 relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinCompositeEssayRelatedById0(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CompositeEssayRelatedById0');

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
            $this->addJoinObject($join, 'CompositeEssayRelatedById0');
        }

        return $this;
    }

    /**
     * Use the CompositeEssayRelatedById0 relation CompositeEssay object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery A secondary query class using the current class as primary query
     */
    public function useCompositeEssayRelatedById0Query(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinCompositeEssayRelatedById0($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CompositeEssayRelatedById0', '\Propel\Tests\Bookstore\CompositeEssayQuery');
    }

    /**
     * Use the CompositeEssayRelatedById0 relation CompositeEssay object
     *
     * @param callable(\Propel\Tests\Bookstore\CompositeEssayQuery):\Propel\Tests\Bookstore\CompositeEssayQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withCompositeEssayRelatedById0Query(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useCompositeEssayRelatedById0Query(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the CompositeEssayRelatedById0 relation to the CompositeEssay table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the EXISTS statement
     */
    public function useCompositeEssayRelatedById0ExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('CompositeEssayRelatedById0', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the CompositeEssayRelatedById0 relation to the CompositeEssay table for a NOT EXISTS query.
     *
     * @see useCompositeEssayRelatedById0ExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT EXISTS statement
     */
    public function useCompositeEssayRelatedById0NotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('CompositeEssayRelatedById0', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the CompositeEssayRelatedById0 relation to the CompositeEssay table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the IN statement
     */
    public function useInCompositeEssayRelatedById0Query(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('CompositeEssayRelatedById0', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the CompositeEssayRelatedById0 relation to the CompositeEssay table for a NOT IN query.
     *
     * @see useCompositeEssayRelatedById0InQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT IN statement
     */
    public function useNotInCompositeEssayRelatedById0Query(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('CompositeEssayRelatedById0', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\CompositeEssay object
     *
     * @param \Propel\Tests\Bookstore\CompositeEssay|ObjectCollection $compositeEssay the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCompositeEssayRelatedById1($compositeEssay, ?string $comparison = null)
    {
        if ($compositeEssay instanceof \Propel\Tests\Bookstore\CompositeEssay) {
            $this
                ->addUsingAlias(CompositeEssayTableMap::COL_ID, $compositeEssay->getSecondEssayId(), $comparison);

            return $this;
        } elseif ($compositeEssay instanceof ObjectCollection) {
            $this
                ->useCompositeEssayRelatedById1Query()
                ->filterByPrimaryKeys($compositeEssay->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByCompositeEssayRelatedById1() only accepts arguments of type \Propel\Tests\Bookstore\CompositeEssay or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CompositeEssayRelatedById1 relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinCompositeEssayRelatedById1(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CompositeEssayRelatedById1');

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
            $this->addJoinObject($join, 'CompositeEssayRelatedById1');
        }

        return $this;
    }

    /**
     * Use the CompositeEssayRelatedById1 relation CompositeEssay object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery A secondary query class using the current class as primary query
     */
    public function useCompositeEssayRelatedById1Query(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinCompositeEssayRelatedById1($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CompositeEssayRelatedById1', '\Propel\Tests\Bookstore\CompositeEssayQuery');
    }

    /**
     * Use the CompositeEssayRelatedById1 relation CompositeEssay object
     *
     * @param callable(\Propel\Tests\Bookstore\CompositeEssayQuery):\Propel\Tests\Bookstore\CompositeEssayQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withCompositeEssayRelatedById1Query(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useCompositeEssayRelatedById1Query(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the CompositeEssayRelatedById1 relation to the CompositeEssay table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the EXISTS statement
     */
    public function useCompositeEssayRelatedById1ExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('CompositeEssayRelatedById1', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the CompositeEssayRelatedById1 relation to the CompositeEssay table for a NOT EXISTS query.
     *
     * @see useCompositeEssayRelatedById1ExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT EXISTS statement
     */
    public function useCompositeEssayRelatedById1NotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useExistsQuery('CompositeEssayRelatedById1', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the CompositeEssayRelatedById1 relation to the CompositeEssay table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the IN statement
     */
    public function useInCompositeEssayRelatedById1Query(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('CompositeEssayRelatedById1', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the CompositeEssayRelatedById1 relation to the CompositeEssay table for a NOT IN query.
     *
     * @see useCompositeEssayRelatedById1InQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CompositeEssayQuery The inner query object of the NOT IN statement
     */
    public function useNotInCompositeEssayRelatedById1Query(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CompositeEssayQuery */
        $q = $this->useInQuery('CompositeEssayRelatedById1', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildCompositeEssay $compositeEssay Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($compositeEssay = null)
    {
        if ($compositeEssay) {
            $this->addUsingAlias(CompositeEssayTableMap::COL_ID, $compositeEssay->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the composite_essay table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(CompositeEssayTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            CompositeEssayTableMap::clearInstancePool();
            CompositeEssayTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(CompositeEssayTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(CompositeEssayTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            CompositeEssayTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            CompositeEssayTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
