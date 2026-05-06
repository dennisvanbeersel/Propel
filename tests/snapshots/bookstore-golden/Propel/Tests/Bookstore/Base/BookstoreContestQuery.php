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
use Propel\Tests\Bookstore\BookstoreContest as ChildBookstoreContest;
use Propel\Tests\Bookstore\BookstoreContestQuery as ChildBookstoreContestQuery;
use Propel\Tests\Bookstore\Map\BookstoreContestTableMap;

/**
 * Base class that represents a query for the `bookstore_contest` table.
 *
 * @method     ChildBookstoreContestQuery orderByBookstoreId($order = Criteria::ASC) Order by the bookstore_id column
 * @method     ChildBookstoreContestQuery orderByContestId($order = Criteria::ASC) Order by the contest_id column
 * @method     ChildBookstoreContestQuery orderByPrizeBookId($order = Criteria::ASC) Order by the prize_book_id column
 *
 * @method     ChildBookstoreContestQuery groupByBookstoreId() Group by the bookstore_id column
 * @method     ChildBookstoreContestQuery groupByContestId() Group by the contest_id column
 * @method     ChildBookstoreContestQuery groupByPrizeBookId() Group by the prize_book_id column
 *
 * @method     ChildBookstoreContestQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildBookstoreContestQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildBookstoreContestQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildBookstoreContestQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildBookstoreContestQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildBookstoreContestQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildBookstoreContestQuery leftJoinBookstore($relationAlias = null) Adds a LEFT JOIN clause to the query using the Bookstore relation
 * @method     ChildBookstoreContestQuery rightJoinBookstore($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Bookstore relation
 * @method     ChildBookstoreContestQuery innerJoinBookstore($relationAlias = null) Adds a INNER JOIN clause to the query using the Bookstore relation
 *
 * @method     ChildBookstoreContestQuery joinWithBookstore($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Bookstore relation
 *
 * @method     ChildBookstoreContestQuery leftJoinWithBookstore() Adds a LEFT JOIN clause and with to the query using the Bookstore relation
 * @method     ChildBookstoreContestQuery rightJoinWithBookstore() Adds a RIGHT JOIN clause and with to the query using the Bookstore relation
 * @method     ChildBookstoreContestQuery innerJoinWithBookstore() Adds a INNER JOIN clause and with to the query using the Bookstore relation
 *
 * @method     ChildBookstoreContestQuery leftJoinContest($relationAlias = null) Adds a LEFT JOIN clause to the query using the Contest relation
 * @method     ChildBookstoreContestQuery rightJoinContest($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Contest relation
 * @method     ChildBookstoreContestQuery innerJoinContest($relationAlias = null) Adds a INNER JOIN clause to the query using the Contest relation
 *
 * @method     ChildBookstoreContestQuery joinWithContest($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Contest relation
 *
 * @method     ChildBookstoreContestQuery leftJoinWithContest() Adds a LEFT JOIN clause and with to the query using the Contest relation
 * @method     ChildBookstoreContestQuery rightJoinWithContest() Adds a RIGHT JOIN clause and with to the query using the Contest relation
 * @method     ChildBookstoreContestQuery innerJoinWithContest() Adds a INNER JOIN clause and with to the query using the Contest relation
 *
 * @method     ChildBookstoreContestQuery leftJoinWork($relationAlias = null) Adds a LEFT JOIN clause to the query using the Work relation
 * @method     ChildBookstoreContestQuery rightJoinWork($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Work relation
 * @method     ChildBookstoreContestQuery innerJoinWork($relationAlias = null) Adds a INNER JOIN clause to the query using the Work relation
 *
 * @method     ChildBookstoreContestQuery joinWithWork($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Work relation
 *
 * @method     ChildBookstoreContestQuery leftJoinWithWork() Adds a LEFT JOIN clause and with to the query using the Work relation
 * @method     ChildBookstoreContestQuery rightJoinWithWork() Adds a RIGHT JOIN clause and with to the query using the Work relation
 * @method     ChildBookstoreContestQuery innerJoinWithWork() Adds a INNER JOIN clause and with to the query using the Work relation
 *
 * @method     ChildBookstoreContestQuery leftJoinBookstoreContestEntry($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookstoreContestEntry relation
 * @method     ChildBookstoreContestQuery rightJoinBookstoreContestEntry($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookstoreContestEntry relation
 * @method     ChildBookstoreContestQuery innerJoinBookstoreContestEntry($relationAlias = null) Adds a INNER JOIN clause to the query using the BookstoreContestEntry relation
 *
 * @method     ChildBookstoreContestQuery joinWithBookstoreContestEntry($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookstoreContestEntry relation
 *
 * @method     ChildBookstoreContestQuery leftJoinWithBookstoreContestEntry() Adds a LEFT JOIN clause and with to the query using the BookstoreContestEntry relation
 * @method     ChildBookstoreContestQuery rightJoinWithBookstoreContestEntry() Adds a RIGHT JOIN clause and with to the query using the BookstoreContestEntry relation
 * @method     ChildBookstoreContestQuery innerJoinWithBookstoreContestEntry() Adds a INNER JOIN clause and with to the query using the BookstoreContestEntry relation
 *
 * @method     \Propel\Tests\Bookstore\BookstoreQuery|\Propel\Tests\Bookstore\ContestQuery|\Propel\Tests\Bookstore\BookQuery|\Propel\Tests\Bookstore\BookstoreContestEntryQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildBookstoreContest|null findOne(?ConnectionInterface $con = null) Return the first ChildBookstoreContest matching the query
 * @method     ChildBookstoreContest findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildBookstoreContest matching the query, or a new ChildBookstoreContest object populated from the query conditions when no match is found
 *
 * @method     ChildBookstoreContest|null findOneByBookstoreId(int $bookstore_id) Return the first ChildBookstoreContest filtered by the bookstore_id column
 * @method     ChildBookstoreContest|null findOneByContestId(int $contest_id) Return the first ChildBookstoreContest filtered by the contest_id column
 * @method     ChildBookstoreContest|null findOneByPrizeBookId(int $prize_book_id) Return the first ChildBookstoreContest filtered by the prize_book_id column
 *
 * @method     ChildBookstoreContest requirePk($key, ?ConnectionInterface $con = null) Return the ChildBookstoreContest by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreContest requireOne(?ConnectionInterface $con = null) Return the first ChildBookstoreContest matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookstoreContest requireOneByBookstoreId(int $bookstore_id) Return the first ChildBookstoreContest filtered by the bookstore_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreContest requireOneByContestId(int $contest_id) Return the first ChildBookstoreContest filtered by the contest_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreContest requireOneByPrizeBookId(int $prize_book_id) Return the first ChildBookstoreContest filtered by the prize_book_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookstoreContest[]|Collection find(?ConnectionInterface $con = null) Return ChildBookstoreContest objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildBookstoreContest> find(?ConnectionInterface $con = null) Return ChildBookstoreContest objects based on current ModelCriteria
 *
 * @method     ChildBookstoreContest[]|Collection findByBookstoreId(int|array<int> $bookstore_id) Return ChildBookstoreContest objects filtered by the bookstore_id column
 * @psalm-method Collection&\Traversable<ChildBookstoreContest> findByBookstoreId(int|array<int> $bookstore_id) Return ChildBookstoreContest objects filtered by the bookstore_id column
 * @method     ChildBookstoreContest[]|Collection findByContestId(int|array<int> $contest_id) Return ChildBookstoreContest objects filtered by the contest_id column
 * @psalm-method Collection&\Traversable<ChildBookstoreContest> findByContestId(int|array<int> $contest_id) Return ChildBookstoreContest objects filtered by the contest_id column
 * @method     ChildBookstoreContest[]|Collection findByPrizeBookId(int|array<int> $prize_book_id) Return ChildBookstoreContest objects filtered by the prize_book_id column
 * @psalm-method Collection&\Traversable<ChildBookstoreContest> findByPrizeBookId(int|array<int> $prize_book_id) Return ChildBookstoreContest objects filtered by the prize_book_id column
 *
 * @method     ChildBookstoreContest[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildBookstoreContest> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class BookstoreContestQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\BookstoreContestQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\BookstoreContest', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildBookstoreContestQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildBookstoreContestQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildBookstoreContestQuery) {
            return $criteria;
        }
        $query = new ChildBookstoreContestQuery();
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
     * @param array[$bookstore_id, $contest_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildBookstoreContest|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(BookstoreContestTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = BookstoreContestTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildBookstoreContest A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT bookstore_id, contest_id, prize_book_id FROM bookstore_contest WHERE bookstore_id = :p0 AND contest_id = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildBookstoreContest $obj */
            $obj = new ChildBookstoreContest();
            $obj->hydrate($row);
            BookstoreContestTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildBookstoreContest|array|mixed the result, formatted by the current formatter
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
        $this->addUsingAlias(BookstoreContestTableMap::COL_BOOKSTORE_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(BookstoreContestTableMap::COL_CONTEST_ID, $key[1], Criteria::EQUAL);

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
            $cton0 = $this->getNewCriterion(BookstoreContestTableMap::COL_BOOKSTORE_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(BookstoreContestTableMap::COL_CONTEST_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the bookstore_id column
     *
     * Example usage:
     * <code>
     * $query->filterByBookstoreId(1234); // WHERE bookstore_id = 1234
     * $query->filterByBookstoreId(array(12, 34)); // WHERE bookstore_id IN (12, 34)
     * $query->filterByBookstoreId(array('min' => 12)); // WHERE bookstore_id > 12
     * </code>
     *
     * @see       filterByBookstore()
     *
     * @param mixed $bookstoreId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookstoreId($bookstoreId = null, ?string $comparison = null)
    {
        if (is_array($bookstoreId)) {
            $useMinMax = false;
            if (isset($bookstoreId['min'])) {
                $this->addUsingAlias(BookstoreContestTableMap::COL_BOOKSTORE_ID, $bookstoreId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($bookstoreId['max'])) {
                $this->addUsingAlias(BookstoreContestTableMap::COL_BOOKSTORE_ID, $bookstoreId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreContestTableMap::COL_BOOKSTORE_ID, $bookstoreId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the contest_id column
     *
     * Example usage:
     * <code>
     * $query->filterByContestId(1234); // WHERE contest_id = 1234
     * $query->filterByContestId(array(12, 34)); // WHERE contest_id IN (12, 34)
     * $query->filterByContestId(array('min' => 12)); // WHERE contest_id > 12
     * </code>
     *
     * @see       filterByContest()
     *
     * @param mixed $contestId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByContestId($contestId = null, ?string $comparison = null)
    {
        if (is_array($contestId)) {
            $useMinMax = false;
            if (isset($contestId['min'])) {
                $this->addUsingAlias(BookstoreContestTableMap::COL_CONTEST_ID, $contestId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($contestId['max'])) {
                $this->addUsingAlias(BookstoreContestTableMap::COL_CONTEST_ID, $contestId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreContestTableMap::COL_CONTEST_ID, $contestId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the prize_book_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPrizeBookId(1234); // WHERE prize_book_id = 1234
     * $query->filterByPrizeBookId(array(12, 34)); // WHERE prize_book_id IN (12, 34)
     * $query->filterByPrizeBookId(array('min' => 12)); // WHERE prize_book_id > 12
     * </code>
     *
     * @see       filterByWork()
     *
     * @param mixed $prizeBookId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrizeBookId($prizeBookId = null, ?string $comparison = null)
    {
        if (is_array($prizeBookId)) {
            $useMinMax = false;
            if (isset($prizeBookId['min'])) {
                $this->addUsingAlias(BookstoreContestTableMap::COL_PRIZE_BOOK_ID, $prizeBookId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($prizeBookId['max'])) {
                $this->addUsingAlias(BookstoreContestTableMap::COL_PRIZE_BOOK_ID, $prizeBookId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreContestTableMap::COL_PRIZE_BOOK_ID, $prizeBookId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Bookstore object
     *
     * @param \Propel\Tests\Bookstore\Bookstore|ObjectCollection $bookstore The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookstore($bookstore, ?string $comparison = null)
    {
        if ($bookstore instanceof \Propel\Tests\Bookstore\Bookstore) {
            return $this
                ->addUsingAlias(BookstoreContestTableMap::COL_BOOKSTORE_ID, $bookstore->getId(), $comparison);
        } elseif ($bookstore instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookstoreContestTableMap::COL_BOOKSTORE_ID, $bookstore->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByBookstore() only accepts arguments of type \Propel\Tests\Bookstore\Bookstore or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Bookstore relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookstore(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Bookstore');

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
            $this->addJoinObject($join, 'Bookstore');
        }

        return $this;
    }

    /**
     * Use the Bookstore relation Bookstore object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookstoreQuery A secondary query class using the current class as primary query
     */
    public function useBookstoreQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookstore($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Bookstore', '\Propel\Tests\Bookstore\BookstoreQuery');
    }

    /**
     * Use the Bookstore relation Bookstore object
     *
     * @param callable(\Propel\Tests\Bookstore\BookstoreQuery):\Propel\Tests\Bookstore\BookstoreQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookstoreQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookstoreQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Bookstore table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookstoreQuery The inner query object of the EXISTS statement
     */
    public function useBookstoreExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreQuery */
        $q = $this->useExistsQuery('Bookstore', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Bookstore table for a NOT EXISTS query.
     *
     * @see useBookstoreExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookstoreNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreQuery */
        $q = $this->useExistsQuery('Bookstore', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Bookstore table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookstoreQuery The inner query object of the IN statement
     */
    public function useInBookstoreQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreQuery */
        $q = $this->useInQuery('Bookstore', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Bookstore table for a NOT IN query.
     *
     * @see useBookstoreInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookstoreQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreQuery */
        $q = $this->useInQuery('Bookstore', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Contest object
     *
     * @param \Propel\Tests\Bookstore\Contest|ObjectCollection $contest The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByContest($contest, ?string $comparison = null)
    {
        if ($contest instanceof \Propel\Tests\Bookstore\Contest) {
            return $this
                ->addUsingAlias(BookstoreContestTableMap::COL_CONTEST_ID, $contest->getId(), $comparison);
        } elseif ($contest instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookstoreContestTableMap::COL_CONTEST_ID, $contest->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByContest() only accepts arguments of type \Propel\Tests\Bookstore\Contest or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Contest relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinContest(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Contest');

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
            $this->addJoinObject($join, 'Contest');
        }

        return $this;
    }

    /**
     * Use the Contest relation Contest object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\ContestQuery A secondary query class using the current class as primary query
     */
    public function useContestQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinContest($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Contest', '\Propel\Tests\Bookstore\ContestQuery');
    }

    /**
     * Use the Contest relation Contest object
     *
     * @param callable(\Propel\Tests\Bookstore\ContestQuery):\Propel\Tests\Bookstore\ContestQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withContestQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useContestQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Contest table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the EXISTS statement
     */
    public function useContestExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useExistsQuery('Contest', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Contest table for a NOT EXISTS query.
     *
     * @see useContestExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the NOT EXISTS statement
     */
    public function useContestNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useExistsQuery('Contest', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Contest table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the IN statement
     */
    public function useInContestQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useInQuery('Contest', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Contest table for a NOT IN query.
     *
     * @see useContestInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the NOT IN statement
     */
    public function useNotInContestQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useInQuery('Contest', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Book object
     *
     * @param \Propel\Tests\Bookstore\Book|ObjectCollection $book The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByWork($book, ?string $comparison = null)
    {
        if ($book instanceof \Propel\Tests\Bookstore\Book) {
            return $this
                ->addUsingAlias(BookstoreContestTableMap::COL_PRIZE_BOOK_ID, $book->getId(), $comparison);
        } elseif ($book instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookstoreContestTableMap::COL_PRIZE_BOOK_ID, $book->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByWork() only accepts arguments of type \Propel\Tests\Bookstore\Book or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Work relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinWork(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Work');

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
            $this->addJoinObject($join, 'Work');
        }

        return $this;
    }

    /**
     * Use the Work relation Book object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookQuery A secondary query class using the current class as primary query
     */
    public function useWorkQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinWork($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Work', '\Propel\Tests\Bookstore\BookQuery');
    }

    /**
     * Use the Work relation Book object
     *
     * @param callable(\Propel\Tests\Bookstore\BookQuery):\Propel\Tests\Bookstore\BookQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withWorkQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useWorkQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the Work relation to the Book table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the EXISTS statement
     */
    public function useWorkExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useExistsQuery('Work', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the Work relation to the Book table for a NOT EXISTS query.
     *
     * @see useWorkExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the NOT EXISTS statement
     */
    public function useWorkNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useExistsQuery('Work', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the Work relation to the Book table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the IN statement
     */
    public function useInWorkQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useInQuery('Work', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the Work relation to the Book table for a NOT IN query.
     *
     * @see useWorkInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the NOT IN statement
     */
    public function useNotInWorkQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useInQuery('Work', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookstoreContestEntry object
     *
     * @param \Propel\Tests\Bookstore\BookstoreContestEntry|ObjectCollection $bookstoreContestEntry the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookstoreContestEntry($bookstoreContestEntry, ?string $comparison = null)
    {
        if ($bookstoreContestEntry instanceof \Propel\Tests\Bookstore\BookstoreContestEntry) {
            $this
                ->addUsingAlias(BookstoreContestTableMap::COL_BOOKSTORE_ID, $bookstoreContestEntry->getBookstoreId(), $comparison)
                ->addUsingAlias(BookstoreContestTableMap::COL_CONTEST_ID, $bookstoreContestEntry->getContestId(), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByBookstoreContestEntry() only accepts arguments of type \Propel\Tests\Bookstore\BookstoreContestEntry');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookstoreContestEntry relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookstoreContestEntry(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookstoreContestEntry');

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
            $this->addJoinObject($join, 'BookstoreContestEntry');
        }

        return $this;
    }

    /**
     * Use the BookstoreContestEntry relation BookstoreContestEntry object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestEntryQuery A secondary query class using the current class as primary query
     */
    public function useBookstoreContestEntryQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookstoreContestEntry($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookstoreContestEntry', '\Propel\Tests\Bookstore\BookstoreContestEntryQuery');
    }

    /**
     * Use the BookstoreContestEntry relation BookstoreContestEntry object
     *
     * @param callable(\Propel\Tests\Bookstore\BookstoreContestEntryQuery):\Propel\Tests\Bookstore\BookstoreContestEntryQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookstoreContestEntryQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookstoreContestEntryQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookstoreContestEntry table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestEntryQuery The inner query object of the EXISTS statement
     */
    public function useBookstoreContestEntryExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestEntryQuery */
        $q = $this->useExistsQuery('BookstoreContestEntry', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookstoreContestEntry table for a NOT EXISTS query.
     *
     * @see useBookstoreContestEntryExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestEntryQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookstoreContestEntryNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestEntryQuery */
        $q = $this->useExistsQuery('BookstoreContestEntry', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookstoreContestEntry table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestEntryQuery The inner query object of the IN statement
     */
    public function useInBookstoreContestEntryQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestEntryQuery */
        $q = $this->useInQuery('BookstoreContestEntry', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookstoreContestEntry table for a NOT IN query.
     *
     * @see useBookstoreContestEntryInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestEntryQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookstoreContestEntryQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestEntryQuery */
        $q = $this->useInQuery('BookstoreContestEntry', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildBookstoreContest $bookstoreContest Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($bookstoreContest = null)
    {
        if ($bookstoreContest) {
            $this->addCond('pruneCond0', $this->getAliasedColName(BookstoreContestTableMap::COL_BOOKSTORE_ID), $bookstoreContest->getBookstoreId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(BookstoreContestTableMap::COL_CONTEST_ID), $bookstoreContest->getContestId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the bookstore_contest table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreContestTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BookstoreContestTableMap::clearInstancePool();
            BookstoreContestTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreContestTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(BookstoreContestTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            BookstoreContestTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            BookstoreContestTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
