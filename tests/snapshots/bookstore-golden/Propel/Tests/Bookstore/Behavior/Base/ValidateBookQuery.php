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
use Propel\Tests\Bookstore\Behavior\ValidateBook as ChildValidateBook;
use Propel\Tests\Bookstore\Behavior\ValidateBookQuery as ChildValidateBookQuery;
use Propel\Tests\Bookstore\Behavior\Map\ValidateBookTableMap;

/**
 * Base class that represents a query for the `validate_book` table.
 *
 * Book Table
 *
 * @method     ChildValidateBookQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildValidateBookQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildValidateBookQuery orderByIsbn($order = Criteria::ASC) Order by the isbn column
 * @method     ChildValidateBookQuery orderByPrice($order = Criteria::ASC) Order by the price column
 * @method     ChildValidateBookQuery orderByPublisherId($order = Criteria::ASC) Order by the publisher_id column
 * @method     ChildValidateBookQuery orderByAuthorId($order = Criteria::ASC) Order by the author_id column
 *
 * @method     ChildValidateBookQuery groupById() Group by the id column
 * @method     ChildValidateBookQuery groupByTitle() Group by the title column
 * @method     ChildValidateBookQuery groupByIsbn() Group by the isbn column
 * @method     ChildValidateBookQuery groupByPrice() Group by the price column
 * @method     ChildValidateBookQuery groupByPublisherId() Group by the publisher_id column
 * @method     ChildValidateBookQuery groupByAuthorId() Group by the author_id column
 *
 * @method     ChildValidateBookQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildValidateBookQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildValidateBookQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildValidateBookQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildValidateBookQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildValidateBookQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildValidateBookQuery leftJoinValidatePublisher($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidatePublisher relation
 * @method     ChildValidateBookQuery rightJoinValidatePublisher($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidatePublisher relation
 * @method     ChildValidateBookQuery innerJoinValidatePublisher($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidatePublisher relation
 *
 * @method     ChildValidateBookQuery joinWithValidatePublisher($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidatePublisher relation
 *
 * @method     ChildValidateBookQuery leftJoinWithValidatePublisher() Adds a LEFT JOIN clause and with to the query using the ValidatePublisher relation
 * @method     ChildValidateBookQuery rightJoinWithValidatePublisher() Adds a RIGHT JOIN clause and with to the query using the ValidatePublisher relation
 * @method     ChildValidateBookQuery innerJoinWithValidatePublisher() Adds a INNER JOIN clause and with to the query using the ValidatePublisher relation
 *
 * @method     ChildValidateBookQuery leftJoinValidateAuthor($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateAuthor relation
 * @method     ChildValidateBookQuery rightJoinValidateAuthor($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateAuthor relation
 * @method     ChildValidateBookQuery innerJoinValidateAuthor($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateAuthor relation
 *
 * @method     ChildValidateBookQuery joinWithValidateAuthor($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateAuthor relation
 *
 * @method     ChildValidateBookQuery leftJoinWithValidateAuthor() Adds a LEFT JOIN clause and with to the query using the ValidateAuthor relation
 * @method     ChildValidateBookQuery rightJoinWithValidateAuthor() Adds a RIGHT JOIN clause and with to the query using the ValidateAuthor relation
 * @method     ChildValidateBookQuery innerJoinWithValidateAuthor() Adds a INNER JOIN clause and with to the query using the ValidateAuthor relation
 *
 * @method     ChildValidateBookQuery leftJoinValidateReaderBook($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateReaderBook relation
 * @method     ChildValidateBookQuery rightJoinValidateReaderBook($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateReaderBook relation
 * @method     ChildValidateBookQuery innerJoinValidateReaderBook($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateReaderBook relation
 *
 * @method     ChildValidateBookQuery joinWithValidateReaderBook($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateReaderBook relation
 *
 * @method     ChildValidateBookQuery leftJoinWithValidateReaderBook() Adds a LEFT JOIN clause and with to the query using the ValidateReaderBook relation
 * @method     ChildValidateBookQuery rightJoinWithValidateReaderBook() Adds a RIGHT JOIN clause and with to the query using the ValidateReaderBook relation
 * @method     ChildValidateBookQuery innerJoinWithValidateReaderBook() Adds a INNER JOIN clause and with to the query using the ValidateReaderBook relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery|\Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery|\Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildValidateBook|null findOne(?ConnectionInterface $con = null) Return the first ChildValidateBook matching the query
 * @method     ChildValidateBook findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildValidateBook matching the query, or a new ChildValidateBook object populated from the query conditions when no match is found
 *
 * @method     ChildValidateBook|null findOneById(int $id) Return the first ChildValidateBook filtered by the id column
 * @method     ChildValidateBook|null findOneByTitle(string $title) Return the first ChildValidateBook filtered by the title column
 * @method     ChildValidateBook|null findOneByIsbn(string $isbn) Return the first ChildValidateBook filtered by the isbn column
 * @method     ChildValidateBook|null findOneByPrice(double $price) Return the first ChildValidateBook filtered by the price column
 * @method     ChildValidateBook|null findOneByPublisherId(int $publisher_id) Return the first ChildValidateBook filtered by the publisher_id column
 * @method     ChildValidateBook|null findOneByAuthorId(int $author_id) Return the first ChildValidateBook filtered by the author_id column
 *
 * @method     ChildValidateBook requirePk($key, ?ConnectionInterface $con = null) Return the ChildValidateBook by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateBook requireOne(?ConnectionInterface $con = null) Return the first ChildValidateBook matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateBook requireOneById(int $id) Return the first ChildValidateBook filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateBook requireOneByTitle(string $title) Return the first ChildValidateBook filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateBook requireOneByIsbn(string $isbn) Return the first ChildValidateBook filtered by the isbn column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateBook requireOneByPrice(double $price) Return the first ChildValidateBook filtered by the price column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateBook requireOneByPublisherId(int $publisher_id) Return the first ChildValidateBook filtered by the publisher_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateBook requireOneByAuthorId(int $author_id) Return the first ChildValidateBook filtered by the author_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateBook[]|Collection find(?ConnectionInterface $con = null) Return ChildValidateBook objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildValidateBook> find(?ConnectionInterface $con = null) Return ChildValidateBook objects based on current ModelCriteria
 *
 * @method     ChildValidateBook[]|Collection findById(int|array<int> $id) Return ChildValidateBook objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildValidateBook> findById(int|array<int> $id) Return ChildValidateBook objects filtered by the id column
 * @method     ChildValidateBook[]|Collection findByTitle(string|array<string> $title) Return ChildValidateBook objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildValidateBook> findByTitle(string|array<string> $title) Return ChildValidateBook objects filtered by the title column
 * @method     ChildValidateBook[]|Collection findByIsbn(string|array<string> $isbn) Return ChildValidateBook objects filtered by the isbn column
 * @psalm-method Collection&\Traversable<ChildValidateBook> findByIsbn(string|array<string> $isbn) Return ChildValidateBook objects filtered by the isbn column
 * @method     ChildValidateBook[]|Collection findByPrice(double|array<double> $price) Return ChildValidateBook objects filtered by the price column
 * @psalm-method Collection&\Traversable<ChildValidateBook> findByPrice(double|array<double> $price) Return ChildValidateBook objects filtered by the price column
 * @method     ChildValidateBook[]|Collection findByPublisherId(int|array<int> $publisher_id) Return ChildValidateBook objects filtered by the publisher_id column
 * @psalm-method Collection&\Traversable<ChildValidateBook> findByPublisherId(int|array<int> $publisher_id) Return ChildValidateBook objects filtered by the publisher_id column
 * @method     ChildValidateBook[]|Collection findByAuthorId(int|array<int> $author_id) Return ChildValidateBook objects filtered by the author_id column
 * @psalm-method Collection&\Traversable<ChildValidateBook> findByAuthorId(int|array<int> $author_id) Return ChildValidateBook objects filtered by the author_id column
 *
 * @method     ChildValidateBook[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildValidateBook> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ValidateBookQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ValidateBookQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateBook', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildValidateBookQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildValidateBookQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildValidateBookQuery) {
            return $criteria;
        }
        $query = new ChildValidateBookQuery();
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
     * @return ChildValidateBook|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ValidateBookTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ValidateBookTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildValidateBook A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, isbn, price, publisher_id, author_id FROM validate_book WHERE id = :p0';
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
            /** @var ChildValidateBook $obj */
            $obj = new ChildValidateBook();
            $obj->hydrate($row);
            ValidateBookTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildValidateBook|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(ValidateBookTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(ValidateBookTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(ValidateBookTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ValidateBookTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateBookTableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(ValidateBookTableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the isbn column
     *
     * Example usage:
     * <code>
     * $query->filterByIsbn('fooValue');   // WHERE isbn = 'fooValue'
     * $query->filterByIsbn('%fooValue%', Criteria::LIKE); // WHERE isbn LIKE '%fooValue%'
     * $query->filterByIsbn(['foo', 'bar']); // WHERE isbn IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $isbn The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByIsbn($isbn = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($isbn)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateBookTableMap::COL_ISBN, $isbn, $comparison);

        return $this;
    }

    /**
     * Filter the query on the price column
     *
     * Example usage:
     * <code>
     * $query->filterByPrice(1234); // WHERE price = 1234
     * $query->filterByPrice(array(12, 34)); // WHERE price IN (12, 34)
     * $query->filterByPrice(array('min' => 12)); // WHERE price > 12
     * </code>
     *
     * @param mixed $price The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrice($price = null, ?string $comparison = null)
    {
        if (is_array($price)) {
            $useMinMax = false;
            if (isset($price['min'])) {
                $this->addUsingAlias(ValidateBookTableMap::COL_PRICE, $price['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price['max'])) {
                $this->addUsingAlias(ValidateBookTableMap::COL_PRICE, $price['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateBookTableMap::COL_PRICE, $price, $comparison);

        return $this;
    }

    /**
     * Filter the query on the publisher_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPublisherId(1234); // WHERE publisher_id = 1234
     * $query->filterByPublisherId(array(12, 34)); // WHERE publisher_id IN (12, 34)
     * $query->filterByPublisherId(array('min' => 12)); // WHERE publisher_id > 12
     * </code>
     *
     * @see       filterByValidatePublisher()
     *
     * @param mixed $publisherId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPublisherId($publisherId = null, ?string $comparison = null)
    {
        if (is_array($publisherId)) {
            $useMinMax = false;
            if (isset($publisherId['min'])) {
                $this->addUsingAlias(ValidateBookTableMap::COL_PUBLISHER_ID, $publisherId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($publisherId['max'])) {
                $this->addUsingAlias(ValidateBookTableMap::COL_PUBLISHER_ID, $publisherId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateBookTableMap::COL_PUBLISHER_ID, $publisherId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the author_id column
     *
     * Example usage:
     * <code>
     * $query->filterByAuthorId(1234); // WHERE author_id = 1234
     * $query->filterByAuthorId(array(12, 34)); // WHERE author_id IN (12, 34)
     * $query->filterByAuthorId(array('min' => 12)); // WHERE author_id > 12
     * </code>
     *
     * @see       filterByValidateAuthor()
     *
     * @param mixed $authorId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAuthorId($authorId = null, ?string $comparison = null)
    {
        if (is_array($authorId)) {
            $useMinMax = false;
            if (isset($authorId['min'])) {
                $this->addUsingAlias(ValidateBookTableMap::COL_AUTHOR_ID, $authorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($authorId['max'])) {
                $this->addUsingAlias(ValidateBookTableMap::COL_AUTHOR_ID, $authorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateBookTableMap::COL_AUTHOR_ID, $authorId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidatePublisher object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidatePublisher|ObjectCollection $validatePublisher The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidatePublisher($validatePublisher, ?string $comparison = null)
    {
        if ($validatePublisher instanceof \Propel\Tests\Bookstore\Behavior\ValidatePublisher) {
            return $this
                ->addUsingAlias(ValidateBookTableMap::COL_PUBLISHER_ID, $validatePublisher->getId(), $comparison);
        } elseif ($validatePublisher instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ValidateBookTableMap::COL_PUBLISHER_ID, $validatePublisher->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByValidatePublisher() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidatePublisher or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidatePublisher relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidatePublisher(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidatePublisher');

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
            $this->addJoinObject($join, 'ValidatePublisher');
        }

        return $this;
    }

    /**
     * Use the ValidatePublisher relation ValidatePublisher object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery A secondary query class using the current class as primary query
     */
    public function useValidatePublisherQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinValidatePublisher($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidatePublisher', '\Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery');
    }

    /**
     * Use the ValidatePublisher relation ValidatePublisher object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery):\Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidatePublisherQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useValidatePublisherQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidatePublisher table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery The inner query object of the EXISTS statement
     */
    public function useValidatePublisherExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery */
        $q = $this->useExistsQuery('ValidatePublisher', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidatePublisher table for a NOT EXISTS query.
     *
     * @see useValidatePublisherExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidatePublisherNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery */
        $q = $this->useExistsQuery('ValidatePublisher', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidatePublisher table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery The inner query object of the IN statement
     */
    public function useInValidatePublisherQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery */
        $q = $this->useInQuery('ValidatePublisher', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidatePublisher table for a NOT IN query.
     *
     * @see useValidatePublisherInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidatePublisherQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery */
        $q = $this->useInQuery('ValidatePublisher', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateAuthor object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateAuthor|ObjectCollection $validateAuthor The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateAuthor($validateAuthor, ?string $comparison = null)
    {
        if ($validateAuthor instanceof \Propel\Tests\Bookstore\Behavior\ValidateAuthor) {
            return $this
                ->addUsingAlias(ValidateBookTableMap::COL_AUTHOR_ID, $validateAuthor->getId(), $comparison);
        } elseif ($validateAuthor instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ValidateBookTableMap::COL_AUTHOR_ID, $validateAuthor->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByValidateAuthor() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateAuthor or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateAuthor relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateAuthor(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateAuthor');

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
            $this->addJoinObject($join, 'ValidateAuthor');
        }

        return $this;
    }

    /**
     * Use the ValidateAuthor relation ValidateAuthor object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery A secondary query class using the current class as primary query
     */
    public function useValidateAuthorQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinValidateAuthor($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateAuthor', '\Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery');
    }

    /**
     * Use the ValidateAuthor relation ValidateAuthor object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery):\Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateAuthorQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useValidateAuthorQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateAuthor table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery The inner query object of the EXISTS statement
     */
    public function useValidateAuthorExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery */
        $q = $this->useExistsQuery('ValidateAuthor', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateAuthor table for a NOT EXISTS query.
     *
     * @see useValidateAuthorExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateAuthorNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery */
        $q = $this->useExistsQuery('ValidateAuthor', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateAuthor table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery The inner query object of the IN statement
     */
    public function useInValidateAuthorQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery */
        $q = $this->useInQuery('ValidateAuthor', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateAuthor table for a NOT IN query.
     *
     * @see useValidateAuthorInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateAuthorQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateAuthorQuery */
        $q = $this->useInQuery('ValidateAuthor', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateReaderBook object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateReaderBook|ObjectCollection $validateReaderBook the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateReaderBook($validateReaderBook, ?string $comparison = null)
    {
        if ($validateReaderBook instanceof \Propel\Tests\Bookstore\Behavior\ValidateReaderBook) {
            $this
                ->addUsingAlias(ValidateBookTableMap::COL_ID, $validateReaderBook->getBookId(), $comparison);

            return $this;
        } elseif ($validateReaderBook instanceof ObjectCollection) {
            $this
                ->useValidateReaderBookQuery()
                ->filterByPrimaryKeys($validateReaderBook->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByValidateReaderBook() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateReaderBook or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateReaderBook relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateReaderBook(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateReaderBook');

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
            $this->addJoinObject($join, 'ValidateReaderBook');
        }

        return $this;
    }

    /**
     * Use the ValidateReaderBook relation ValidateReaderBook object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery A secondary query class using the current class as primary query
     */
    public function useValidateReaderBookQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinValidateReaderBook($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateReaderBook', '\Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery');
    }

    /**
     * Use the ValidateReaderBook relation ValidateReaderBook object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery):\Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateReaderBookQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useValidateReaderBookQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateReaderBook table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the EXISTS statement
     */
    public function useValidateReaderBookExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useExistsQuery('ValidateReaderBook', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateReaderBook table for a NOT EXISTS query.
     *
     * @see useValidateReaderBookExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateReaderBookNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useExistsQuery('ValidateReaderBook', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateReaderBook table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the IN statement
     */
    public function useInValidateReaderBookQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useInQuery('ValidateReaderBook', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateReaderBook table for a NOT IN query.
     *
     * @see useValidateReaderBookInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateReaderBookQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useInQuery('ValidateReaderBook', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related ValidateReader object
     * using the validate_reader_book table as cross reference
     *
     * @param ValidateReader $validateReader the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL and Criteria::IN for queries
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateReader($validateReader, ?string $comparison = null)
    {
        $this
            ->useValidateReaderBookQuery()
            ->filterByValidateReader($validateReader, $comparison)
            ->endUse();

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildValidateBook $validateBook Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($validateBook = null)
    {
        if ($validateBook) {
            $this->addUsingAlias(ValidateBookTableMap::COL_ID, $validateBook->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the validate_book table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateBookTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ValidateBookTableMap::clearInstancePool();
            ValidateBookTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateBookTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ValidateBookTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ValidateBookTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ValidateBookTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
