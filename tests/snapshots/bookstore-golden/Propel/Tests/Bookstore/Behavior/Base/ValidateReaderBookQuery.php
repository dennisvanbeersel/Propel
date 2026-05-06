<?php

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
use Propel\Tests\Bookstore\Behavior\ValidateReaderBook as ChildValidateReaderBook;
use Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery as ChildValidateReaderBookQuery;
use Propel\Tests\Bookstore\Behavior\Map\ValidateReaderBookTableMap;

/**
 * Base class that represents a query for the `validate_reader_book` table.
 *
 * @method     ChildValidateReaderBookQuery orderByReaderId($order = Criteria::ASC) Order by the reader_id column
 * @method     ChildValidateReaderBookQuery orderByBookId($order = Criteria::ASC) Order by the book_id column
 *
 * @method     ChildValidateReaderBookQuery groupByReaderId() Group by the reader_id column
 * @method     ChildValidateReaderBookQuery groupByBookId() Group by the book_id column
 *
 * @method     ChildValidateReaderBookQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildValidateReaderBookQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildValidateReaderBookQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildValidateReaderBookQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildValidateReaderBookQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildValidateReaderBookQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildValidateReaderBookQuery leftJoinValidateReader($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateReader relation
 * @method     ChildValidateReaderBookQuery rightJoinValidateReader($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateReader relation
 * @method     ChildValidateReaderBookQuery innerJoinValidateReader($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateReader relation
 *
 * @method     ChildValidateReaderBookQuery joinWithValidateReader($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateReader relation
 *
 * @method     ChildValidateReaderBookQuery leftJoinWithValidateReader() Adds a LEFT JOIN clause and with to the query using the ValidateReader relation
 * @method     ChildValidateReaderBookQuery rightJoinWithValidateReader() Adds a RIGHT JOIN clause and with to the query using the ValidateReader relation
 * @method     ChildValidateReaderBookQuery innerJoinWithValidateReader() Adds a INNER JOIN clause and with to the query using the ValidateReader relation
 *
 * @method     ChildValidateReaderBookQuery leftJoinValidateBook($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateBook relation
 * @method     ChildValidateReaderBookQuery rightJoinValidateBook($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateBook relation
 * @method     ChildValidateReaderBookQuery innerJoinValidateBook($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateBook relation
 *
 * @method     ChildValidateReaderBookQuery joinWithValidateBook($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateBook relation
 *
 * @method     ChildValidateReaderBookQuery leftJoinWithValidateBook() Adds a LEFT JOIN clause and with to the query using the ValidateBook relation
 * @method     ChildValidateReaderBookQuery rightJoinWithValidateBook() Adds a RIGHT JOIN clause and with to the query using the ValidateBook relation
 * @method     ChildValidateReaderBookQuery innerJoinWithValidateBook() Adds a INNER JOIN clause and with to the query using the ValidateBook relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery|\Propel\Tests\Bookstore\Behavior\ValidateBookQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildValidateReaderBook|null findOne(?ConnectionInterface $con = null) Return the first ChildValidateReaderBook matching the query
 * @method     ChildValidateReaderBook findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildValidateReaderBook matching the query, or a new ChildValidateReaderBook object populated from the query conditions when no match is found
 *
 * @method     ChildValidateReaderBook|null findOneByReaderId(int $reader_id) Return the first ChildValidateReaderBook filtered by the reader_id column
 * @method     ChildValidateReaderBook|null findOneByBookId(int $book_id) Return the first ChildValidateReaderBook filtered by the book_id column
 *
 * @method     ChildValidateReaderBook requirePk($key, ?ConnectionInterface $con = null) Return the ChildValidateReaderBook by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateReaderBook requireOne(?ConnectionInterface $con = null) Return the first ChildValidateReaderBook matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateReaderBook requireOneByReaderId(int $reader_id) Return the first ChildValidateReaderBook filtered by the reader_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateReaderBook requireOneByBookId(int $book_id) Return the first ChildValidateReaderBook filtered by the book_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateReaderBook[]|Collection find(?ConnectionInterface $con = null) Return ChildValidateReaderBook objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildValidateReaderBook> find(?ConnectionInterface $con = null) Return ChildValidateReaderBook objects based on current ModelCriteria
 *
 * @method     ChildValidateReaderBook[]|Collection findByReaderId(int|array<int> $reader_id) Return ChildValidateReaderBook objects filtered by the reader_id column
 * @psalm-method Collection&\Traversable<ChildValidateReaderBook> findByReaderId(int|array<int> $reader_id) Return ChildValidateReaderBook objects filtered by the reader_id column
 * @method     ChildValidateReaderBook[]|Collection findByBookId(int|array<int> $book_id) Return ChildValidateReaderBook objects filtered by the book_id column
 * @psalm-method Collection&\Traversable<ChildValidateReaderBook> findByBookId(int|array<int> $book_id) Return ChildValidateReaderBook objects filtered by the book_id column
 *
 * @method     ChildValidateReaderBook[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildValidateReaderBook> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ValidateReaderBookQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ValidateReaderBookQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateReaderBook', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildValidateReaderBookQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildValidateReaderBookQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildValidateReaderBookQuery) {
            return $criteria;
        }
        $query = new ChildValidateReaderBookQuery();
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
     * @param array[$reader_id, $book_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildValidateReaderBook|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ValidateReaderBookTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ValidateReaderBookTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildValidateReaderBook A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT reader_id, book_id FROM validate_reader_book WHERE reader_id = :p0 AND book_id = :p1';
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
            /** @var ChildValidateReaderBook $obj */
            $obj = new ChildValidateReaderBook();
            $obj->hydrate($row);
            ValidateReaderBookTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildValidateReaderBook|array|mixed the result, formatted by the current formatter
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
        $this->addUsingAlias(ValidateReaderBookTableMap::COL_READER_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(ValidateReaderBookTableMap::COL_BOOK_ID, $key[1], Criteria::EQUAL);

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
            $cton0 = $this->getNewCriterion(ValidateReaderBookTableMap::COL_READER_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(ValidateReaderBookTableMap::COL_BOOK_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the reader_id column
     *
     * Example usage:
     * <code>
     * $query->filterByReaderId(1234); // WHERE reader_id = 1234
     * $query->filterByReaderId(array(12, 34)); // WHERE reader_id IN (12, 34)
     * $query->filterByReaderId(array('min' => 12)); // WHERE reader_id > 12
     * </code>
     *
     * @see       filterByValidateReader()
     *
     * @param mixed $readerId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByReaderId($readerId = null, ?string $comparison = null)
    {
        if (is_array($readerId)) {
            $useMinMax = false;
            if (isset($readerId['min'])) {
                $this->addUsingAlias(ValidateReaderBookTableMap::COL_READER_ID, $readerId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($readerId['max'])) {
                $this->addUsingAlias(ValidateReaderBookTableMap::COL_READER_ID, $readerId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateReaderBookTableMap::COL_READER_ID, $readerId, $comparison);

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
     * @see       filterByValidateBook()
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
                $this->addUsingAlias(ValidateReaderBookTableMap::COL_BOOK_ID, $bookId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($bookId['max'])) {
                $this->addUsingAlias(ValidateReaderBookTableMap::COL_BOOK_ID, $bookId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateReaderBookTableMap::COL_BOOK_ID, $bookId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateReader object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateReader|ObjectCollection $validateReader The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateReader($validateReader, ?string $comparison = null)
    {
        if ($validateReader instanceof \Propel\Tests\Bookstore\Behavior\ValidateReader) {
            return $this
                ->addUsingAlias(ValidateReaderBookTableMap::COL_READER_ID, $validateReader->getId(), $comparison);
        } elseif ($validateReader instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ValidateReaderBookTableMap::COL_READER_ID, $validateReader->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByValidateReader() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateReader or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateReader relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateReader(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateReader');

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
            $this->addJoinObject($join, 'ValidateReader');
        }

        return $this;
    }

    /**
     * Use the ValidateReader relation ValidateReader object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery A secondary query class using the current class as primary query
     */
    public function useValidateReaderQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinValidateReader($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateReader', '\Propel\Tests\Bookstore\Behavior\ValidateReaderQuery');
    }

    /**
     * Use the ValidateReader relation ValidateReader object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateReaderQuery):\Propel\Tests\Bookstore\Behavior\ValidateReaderQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateReaderQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useValidateReaderQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateReader table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery The inner query object of the EXISTS statement
     */
    public function useValidateReaderExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery */
        $q = $this->useExistsQuery('ValidateReader', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateReader table for a NOT EXISTS query.
     *
     * @see useValidateReaderExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateReaderNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery */
        $q = $this->useExistsQuery('ValidateReader', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateReader table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery The inner query object of the IN statement
     */
    public function useInValidateReaderQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery */
        $q = $this->useInQuery('ValidateReader', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateReader table for a NOT IN query.
     *
     * @see useValidateReaderInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateReaderQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderQuery */
        $q = $this->useInQuery('ValidateReader', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateBook object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateBook|ObjectCollection $validateBook The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateBook($validateBook, ?string $comparison = null)
    {
        if ($validateBook instanceof \Propel\Tests\Bookstore\Behavior\ValidateBook) {
            return $this
                ->addUsingAlias(ValidateReaderBookTableMap::COL_BOOK_ID, $validateBook->getId(), $comparison);
        } elseif ($validateBook instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ValidateReaderBookTableMap::COL_BOOK_ID, $validateBook->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByValidateBook() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateBook or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateBook relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateBook(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateBook');

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
            $this->addJoinObject($join, 'ValidateBook');
        }

        return $this;
    }

    /**
     * Use the ValidateBook relation ValidateBook object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateBookQuery A secondary query class using the current class as primary query
     */
    public function useValidateBookQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinValidateBook($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateBook', '\Propel\Tests\Bookstore\Behavior\ValidateBookQuery');
    }

    /**
     * Use the ValidateBook relation ValidateBook object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateBookQuery):\Propel\Tests\Bookstore\Behavior\ValidateBookQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateBookQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useValidateBookQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateBook table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateBookQuery The inner query object of the EXISTS statement
     */
    public function useValidateBookExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateBookQuery */
        $q = $this->useExistsQuery('ValidateBook', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateBook table for a NOT EXISTS query.
     *
     * @see useValidateBookExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateBookQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateBookNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateBookQuery */
        $q = $this->useExistsQuery('ValidateBook', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateBook table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateBookQuery The inner query object of the IN statement
     */
    public function useInValidateBookQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateBookQuery */
        $q = $this->useInQuery('ValidateBook', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateBook table for a NOT IN query.
     *
     * @see useValidateBookInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateBookQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateBookQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateBookQuery */
        $q = $this->useInQuery('ValidateBook', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildValidateReaderBook $validateReaderBook Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($validateReaderBook = null)
    {
        if ($validateReaderBook) {
            $this->addCond('pruneCond0', $this->getAliasedColName(ValidateReaderBookTableMap::COL_READER_ID), $validateReaderBook->getReaderId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(ValidateReaderBookTableMap::COL_BOOK_ID), $validateReaderBook->getBookId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the validate_reader_book table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateReaderBookTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ValidateReaderBookTableMap::clearInstancePool();
            ValidateReaderBookTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateReaderBookTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ValidateReaderBookTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ValidateReaderBookTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ValidateReaderBookTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
