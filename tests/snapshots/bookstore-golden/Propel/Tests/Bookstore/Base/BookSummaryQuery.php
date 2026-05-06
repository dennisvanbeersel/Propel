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
use Propel\Tests\Bookstore\BookSummary as ChildBookSummary;
use Propel\Tests\Bookstore\BookSummaryQuery as ChildBookSummaryQuery;
use Propel\Tests\Bookstore\Map\BookSummaryTableMap;

/**
 * Base class that represents a query for the `book_summary` table.
 *
 * @method     ChildBookSummaryQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildBookSummaryQuery orderByBookId($order = Criteria::ASC) Order by the book_id column
 * @method     ChildBookSummaryQuery orderBySummary($order = Criteria::ASC) Order by the summary column
 *
 * @method     ChildBookSummaryQuery groupById() Group by the id column
 * @method     ChildBookSummaryQuery groupByBookId() Group by the book_id column
 * @method     ChildBookSummaryQuery groupBySummary() Group by the summary column
 *
 * @method     ChildBookSummaryQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildBookSummaryQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildBookSummaryQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildBookSummaryQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildBookSummaryQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildBookSummaryQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildBookSummaryQuery leftJoinSummarizedBook($relationAlias = null) Adds a LEFT JOIN clause to the query using the SummarizedBook relation
 * @method     ChildBookSummaryQuery rightJoinSummarizedBook($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SummarizedBook relation
 * @method     ChildBookSummaryQuery innerJoinSummarizedBook($relationAlias = null) Adds a INNER JOIN clause to the query using the SummarizedBook relation
 *
 * @method     ChildBookSummaryQuery joinWithSummarizedBook($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the SummarizedBook relation
 *
 * @method     ChildBookSummaryQuery leftJoinWithSummarizedBook() Adds a LEFT JOIN clause and with to the query using the SummarizedBook relation
 * @method     ChildBookSummaryQuery rightJoinWithSummarizedBook() Adds a RIGHT JOIN clause and with to the query using the SummarizedBook relation
 * @method     ChildBookSummaryQuery innerJoinWithSummarizedBook() Adds a INNER JOIN clause and with to the query using the SummarizedBook relation
 *
 * @method     \Propel\Tests\Bookstore\BookQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildBookSummary|null findOne(?ConnectionInterface $con = null) Return the first ChildBookSummary matching the query
 * @method     ChildBookSummary findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildBookSummary matching the query, or a new ChildBookSummary object populated from the query conditions when no match is found
 *
 * @method     ChildBookSummary|null findOneById(int $id) Return the first ChildBookSummary filtered by the id column
 * @method     ChildBookSummary|null findOneByBookId(int $book_id) Return the first ChildBookSummary filtered by the book_id column
 * @method     ChildBookSummary|null findOneBySummary(string $summary) Return the first ChildBookSummary filtered by the summary column
 *
 * @method     ChildBookSummary requirePk($key, ?ConnectionInterface $con = null) Return the ChildBookSummary by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookSummary requireOne(?ConnectionInterface $con = null) Return the first ChildBookSummary matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookSummary requireOneById(int $id) Return the first ChildBookSummary filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookSummary requireOneByBookId(int $book_id) Return the first ChildBookSummary filtered by the book_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookSummary requireOneBySummary(string $summary) Return the first ChildBookSummary filtered by the summary column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookSummary[]|Collection find(?ConnectionInterface $con = null) Return ChildBookSummary objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildBookSummary> find(?ConnectionInterface $con = null) Return ChildBookSummary objects based on current ModelCriteria
 *
 * @method     ChildBookSummary[]|Collection findById(int|array<int> $id) Return ChildBookSummary objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildBookSummary> findById(int|array<int> $id) Return ChildBookSummary objects filtered by the id column
 * @method     ChildBookSummary[]|Collection findByBookId(int|array<int> $book_id) Return ChildBookSummary objects filtered by the book_id column
 * @psalm-method Collection&\Traversable<ChildBookSummary> findByBookId(int|array<int> $book_id) Return ChildBookSummary objects filtered by the book_id column
 * @method     ChildBookSummary[]|Collection findBySummary(string|array<string> $summary) Return ChildBookSummary objects filtered by the summary column
 * @psalm-method Collection&\Traversable<ChildBookSummary> findBySummary(string|array<string> $summary) Return ChildBookSummary objects filtered by the summary column
 *
 * @method     ChildBookSummary[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildBookSummary> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class BookSummaryQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\BookSummaryQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\BookSummary', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildBookSummaryQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildBookSummaryQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildBookSummaryQuery) {
            return $criteria;
        }
        $query = new ChildBookSummaryQuery();
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
     * @return ChildBookSummary|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(BookSummaryTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = BookSummaryTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildBookSummary A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, book_id, summary FROM book_summary WHERE id = :p0';
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
            /** @var ChildBookSummary $obj */
            $obj = new ChildBookSummary();
            $obj->hydrate($row);
            BookSummaryTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildBookSummary|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(BookSummaryTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(BookSummaryTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(BookSummaryTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(BookSummaryTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookSummaryTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the book_id column
     *
     * Example usage:
     * <code>
     * $query->filterByBookId(1234); // WHERE book_id = 1234
     * $query->filterByBookId(array(12, 34)); // WHERE book_id IN (12, 34)
     * $query->filterByBookId(array('min' => 12)); // WHERE book_id > 12
     * </code>
     *
     * @see       filterBySummarizedBook()
     *
     * @param mixed $bookId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookId($bookId = null, ?string $comparison = null)
    {
        if (is_array($bookId)) {
            $useMinMax = false;
            if (isset($bookId['min'])) {
                $this->addUsingAlias(BookSummaryTableMap::COL_BOOK_ID, $bookId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($bookId['max'])) {
                $this->addUsingAlias(BookSummaryTableMap::COL_BOOK_ID, $bookId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookSummaryTableMap::COL_BOOK_ID, $bookId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the summary column
     *
     * Example usage:
     * <code>
     * $query->filterBySummary('fooValue');   // WHERE summary = 'fooValue'
     * $query->filterBySummary('%fooValue%', Criteria::LIKE); // WHERE summary LIKE '%fooValue%'
     * $query->filterBySummary(['foo', 'bar']); // WHERE summary IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $summary The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySummary($summary = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($summary)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookSummaryTableMap::COL_SUMMARY, $summary, $comparison);

        return $this;
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
    public function filterBySummarizedBook($book, ?string $comparison = null)
    {
        if ($book instanceof \Propel\Tests\Bookstore\Book) {
            return $this
                ->addUsingAlias(BookSummaryTableMap::COL_BOOK_ID, $book->getId(), $comparison);
        } elseif ($book instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookSummaryTableMap::COL_BOOK_ID, $book->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterBySummarizedBook() only accepts arguments of type \Propel\Tests\Bookstore\Book or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SummarizedBook relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinSummarizedBook(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SummarizedBook');

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
            $this->addJoinObject($join, 'SummarizedBook');
        }

        return $this;
    }

    /**
     * Use the SummarizedBook relation Book object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookQuery A secondary query class using the current class as primary query
     */
    public function useSummarizedBookQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinSummarizedBook($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SummarizedBook', '\Propel\Tests\Bookstore\BookQuery');
    }

    /**
     * Use the SummarizedBook relation Book object
     *
     * @param callable(\Propel\Tests\Bookstore\BookQuery):\Propel\Tests\Bookstore\BookQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withSummarizedBookQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useSummarizedBookQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the SummarizedBook relation to the Book table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the EXISTS statement
     */
    public function useSummarizedBookExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useExistsQuery('SummarizedBook', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the SummarizedBook relation to the Book table for a NOT EXISTS query.
     *
     * @see useSummarizedBookExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the NOT EXISTS statement
     */
    public function useSummarizedBookNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useExistsQuery('SummarizedBook', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the SummarizedBook relation to the Book table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the IN statement
     */
    public function useInSummarizedBookQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useInQuery('SummarizedBook', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the SummarizedBook relation to the Book table for a NOT IN query.
     *
     * @see useSummarizedBookInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookQuery The inner query object of the NOT IN statement
     */
    public function useNotInSummarizedBookQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookQuery */
        $q = $this->useInQuery('SummarizedBook', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildBookSummary $bookSummary Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($bookSummary = null)
    {
        if ($bookSummary) {
            $this->addUsingAlias(BookSummaryTableMap::COL_ID, $bookSummary->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the book_summary table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookSummaryTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BookSummaryTableMap::clearInstancePool();
            BookSummaryTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(BookSummaryTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(BookSummaryTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            BookSummaryTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            BookSummaryTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
