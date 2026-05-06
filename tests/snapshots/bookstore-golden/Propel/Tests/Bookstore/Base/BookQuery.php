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
use Propel\Tests\Bookstore\Book as ChildBook;
use Propel\Tests\Bookstore\BookQuery as ChildBookQuery;
use Propel\Tests\Bookstore\Map\BookTableMap;

/**
 * Base class that represents a query for the `book` table.
 *
 * Book Table
 *
 * @method     ChildBookQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildBookQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildBookQuery orderByISBN($order = Criteria::ASC) Order by the isbn column
 * @method     ChildBookQuery orderByPrice($order = Criteria::ASC) Order by the price column
 * @method     ChildBookQuery orderByPublisherId($order = Criteria::ASC) Order by the publisher_id column
 * @method     ChildBookQuery orderByAuthorId($order = Criteria::ASC) Order by the author_id column
 *
 * @method     ChildBookQuery groupById() Group by the id column
 * @method     ChildBookQuery groupByTitle() Group by the title column
 * @method     ChildBookQuery groupByISBN() Group by the isbn column
 * @method     ChildBookQuery groupByPrice() Group by the price column
 * @method     ChildBookQuery groupByPublisherId() Group by the publisher_id column
 * @method     ChildBookQuery groupByAuthorId() Group by the author_id column
 *
 * @method     ChildBookQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildBookQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildBookQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildBookQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildBookQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildBookQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildBookQuery leftJoinPublisher($relationAlias = null) Adds a LEFT JOIN clause to the query using the Publisher relation
 * @method     ChildBookQuery rightJoinPublisher($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Publisher relation
 * @method     ChildBookQuery innerJoinPublisher($relationAlias = null) Adds a INNER JOIN clause to the query using the Publisher relation
 *
 * @method     ChildBookQuery joinWithPublisher($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Publisher relation
 *
 * @method     ChildBookQuery leftJoinWithPublisher() Adds a LEFT JOIN clause and with to the query using the Publisher relation
 * @method     ChildBookQuery rightJoinWithPublisher() Adds a RIGHT JOIN clause and with to the query using the Publisher relation
 * @method     ChildBookQuery innerJoinWithPublisher() Adds a INNER JOIN clause and with to the query using the Publisher relation
 *
 * @method     ChildBookQuery leftJoinAuthor($relationAlias = null) Adds a LEFT JOIN clause to the query using the Author relation
 * @method     ChildBookQuery rightJoinAuthor($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Author relation
 * @method     ChildBookQuery innerJoinAuthor($relationAlias = null) Adds a INNER JOIN clause to the query using the Author relation
 *
 * @method     ChildBookQuery joinWithAuthor($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Author relation
 *
 * @method     ChildBookQuery leftJoinWithAuthor() Adds a LEFT JOIN clause and with to the query using the Author relation
 * @method     ChildBookQuery rightJoinWithAuthor() Adds a RIGHT JOIN clause and with to the query using the Author relation
 * @method     ChildBookQuery innerJoinWithAuthor() Adds a INNER JOIN clause and with to the query using the Author relation
 *
 * @method     ChildBookQuery leftJoinBookSummary($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookSummary relation
 * @method     ChildBookQuery rightJoinBookSummary($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookSummary relation
 * @method     ChildBookQuery innerJoinBookSummary($relationAlias = null) Adds a INNER JOIN clause to the query using the BookSummary relation
 *
 * @method     ChildBookQuery joinWithBookSummary($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookSummary relation
 *
 * @method     ChildBookQuery leftJoinWithBookSummary() Adds a LEFT JOIN clause and with to the query using the BookSummary relation
 * @method     ChildBookQuery rightJoinWithBookSummary() Adds a RIGHT JOIN clause and with to the query using the BookSummary relation
 * @method     ChildBookQuery innerJoinWithBookSummary() Adds a INNER JOIN clause and with to the query using the BookSummary relation
 *
 * @method     ChildBookQuery leftJoinReview($relationAlias = null) Adds a LEFT JOIN clause to the query using the Review relation
 * @method     ChildBookQuery rightJoinReview($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Review relation
 * @method     ChildBookQuery innerJoinReview($relationAlias = null) Adds a INNER JOIN clause to the query using the Review relation
 *
 * @method     ChildBookQuery joinWithReview($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Review relation
 *
 * @method     ChildBookQuery leftJoinWithReview() Adds a LEFT JOIN clause and with to the query using the Review relation
 * @method     ChildBookQuery rightJoinWithReview() Adds a RIGHT JOIN clause and with to the query using the Review relation
 * @method     ChildBookQuery innerJoinWithReview() Adds a INNER JOIN clause and with to the query using the Review relation
 *
 * @method     ChildBookQuery leftJoinMedia($relationAlias = null) Adds a LEFT JOIN clause to the query using the Media relation
 * @method     ChildBookQuery rightJoinMedia($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Media relation
 * @method     ChildBookQuery innerJoinMedia($relationAlias = null) Adds a INNER JOIN clause to the query using the Media relation
 *
 * @method     ChildBookQuery joinWithMedia($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Media relation
 *
 * @method     ChildBookQuery leftJoinWithMedia() Adds a LEFT JOIN clause and with to the query using the Media relation
 * @method     ChildBookQuery rightJoinWithMedia() Adds a RIGHT JOIN clause and with to the query using the Media relation
 * @method     ChildBookQuery innerJoinWithMedia() Adds a INNER JOIN clause and with to the query using the Media relation
 *
 * @method     ChildBookQuery leftJoinBookListRel($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookListRel relation
 * @method     ChildBookQuery rightJoinBookListRel($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookListRel relation
 * @method     ChildBookQuery innerJoinBookListRel($relationAlias = null) Adds a INNER JOIN clause to the query using the BookListRel relation
 *
 * @method     ChildBookQuery joinWithBookListRel($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookListRel relation
 *
 * @method     ChildBookQuery leftJoinWithBookListRel() Adds a LEFT JOIN clause and with to the query using the BookListRel relation
 * @method     ChildBookQuery rightJoinWithBookListRel() Adds a RIGHT JOIN clause and with to the query using the BookListRel relation
 * @method     ChildBookQuery innerJoinWithBookListRel() Adds a INNER JOIN clause and with to the query using the BookListRel relation
 *
 * @method     ChildBookQuery leftJoinBookListFavorite($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookListFavorite relation
 * @method     ChildBookQuery rightJoinBookListFavorite($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookListFavorite relation
 * @method     ChildBookQuery innerJoinBookListFavorite($relationAlias = null) Adds a INNER JOIN clause to the query using the BookListFavorite relation
 *
 * @method     ChildBookQuery joinWithBookListFavorite($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookListFavorite relation
 *
 * @method     ChildBookQuery leftJoinWithBookListFavorite() Adds a LEFT JOIN clause and with to the query using the BookListFavorite relation
 * @method     ChildBookQuery rightJoinWithBookListFavorite() Adds a RIGHT JOIN clause and with to the query using the BookListFavorite relation
 * @method     ChildBookQuery innerJoinWithBookListFavorite() Adds a INNER JOIN clause and with to the query using the BookListFavorite relation
 *
 * @method     ChildBookQuery leftJoinBookOpinion($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookOpinion relation
 * @method     ChildBookQuery rightJoinBookOpinion($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookOpinion relation
 * @method     ChildBookQuery innerJoinBookOpinion($relationAlias = null) Adds a INNER JOIN clause to the query using the BookOpinion relation
 *
 * @method     ChildBookQuery joinWithBookOpinion($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookOpinion relation
 *
 * @method     ChildBookQuery leftJoinWithBookOpinion() Adds a LEFT JOIN clause and with to the query using the BookOpinion relation
 * @method     ChildBookQuery rightJoinWithBookOpinion() Adds a RIGHT JOIN clause and with to the query using the BookOpinion relation
 * @method     ChildBookQuery innerJoinWithBookOpinion() Adds a INNER JOIN clause and with to the query using the BookOpinion relation
 *
 * @method     ChildBookQuery leftJoinReaderFavorite($relationAlias = null) Adds a LEFT JOIN clause to the query using the ReaderFavorite relation
 * @method     ChildBookQuery rightJoinReaderFavorite($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ReaderFavorite relation
 * @method     ChildBookQuery innerJoinReaderFavorite($relationAlias = null) Adds a INNER JOIN clause to the query using the ReaderFavorite relation
 *
 * @method     ChildBookQuery joinWithReaderFavorite($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ReaderFavorite relation
 *
 * @method     ChildBookQuery leftJoinWithReaderFavorite() Adds a LEFT JOIN clause and with to the query using the ReaderFavorite relation
 * @method     ChildBookQuery rightJoinWithReaderFavorite() Adds a RIGHT JOIN clause and with to the query using the ReaderFavorite relation
 * @method     ChildBookQuery innerJoinWithReaderFavorite() Adds a INNER JOIN clause and with to the query using the ReaderFavorite relation
 *
 * @method     ChildBookQuery leftJoinBookstoreContest($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookstoreContest relation
 * @method     ChildBookQuery rightJoinBookstoreContest($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookstoreContest relation
 * @method     ChildBookQuery innerJoinBookstoreContest($relationAlias = null) Adds a INNER JOIN clause to the query using the BookstoreContest relation
 *
 * @method     ChildBookQuery joinWithBookstoreContest($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookstoreContest relation
 *
 * @method     ChildBookQuery leftJoinWithBookstoreContest() Adds a LEFT JOIN clause and with to the query using the BookstoreContest relation
 * @method     ChildBookQuery rightJoinWithBookstoreContest() Adds a RIGHT JOIN clause and with to the query using the BookstoreContest relation
 * @method     ChildBookQuery innerJoinWithBookstoreContest() Adds a INNER JOIN clause and with to the query using the BookstoreContest relation
 *
 * @method     ChildBookQuery leftJoinPolymorphicRelationLog($relationAlias = null) Adds a LEFT JOIN clause to the query using the PolymorphicRelationLog relation
 * @method     ChildBookQuery rightJoinPolymorphicRelationLog($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PolymorphicRelationLog relation
 * @method     ChildBookQuery innerJoinPolymorphicRelationLog($relationAlias = null) Adds a INNER JOIN clause to the query using the PolymorphicRelationLog relation
 *
 * @method     ChildBookQuery joinWithPolymorphicRelationLog($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the PolymorphicRelationLog relation
 *
 * @method     ChildBookQuery leftJoinWithPolymorphicRelationLog() Adds a LEFT JOIN clause and with to the query using the PolymorphicRelationLog relation
 * @method     ChildBookQuery rightJoinWithPolymorphicRelationLog() Adds a RIGHT JOIN clause and with to the query using the PolymorphicRelationLog relation
 * @method     ChildBookQuery innerJoinWithPolymorphicRelationLog() Adds a INNER JOIN clause and with to the query using the PolymorphicRelationLog relation
 *
 * @method     \Propel\Tests\Bookstore\PublisherQuery|\Propel\Tests\Bookstore\AuthorQuery|\Propel\Tests\Bookstore\BookSummaryQuery|\Propel\Tests\Bookstore\ReviewQuery|\Propel\Tests\Bookstore\MediaQuery|\Propel\Tests\Bookstore\BookListRelQuery|\Propel\Tests\Bookstore\BookListFavoriteQuery|\Propel\Tests\Bookstore\BookOpinionQuery|\Propel\Tests\Bookstore\ReaderFavoriteQuery|\Propel\Tests\Bookstore\BookstoreContestQuery|\Propel\Tests\Bookstore\PolymorphicRelationLogQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildBook|null findOne(?ConnectionInterface $con = null) Return the first ChildBook matching the query
 * @method     ChildBook findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildBook matching the query, or a new ChildBook object populated from the query conditions when no match is found
 *
 * @method     ChildBook|null findOneById(int $id) Return the first ChildBook filtered by the id column
 * @method     ChildBook|null findOneByTitle(string $title) Return the first ChildBook filtered by the title column
 * @method     ChildBook|null findOneByISBN(string $isbn) Return the first ChildBook filtered by the isbn column
 * @method     ChildBook|null findOneByPrice(double $price) Return the first ChildBook filtered by the price column
 * @method     ChildBook|null findOneByPublisherId(int $publisher_id) Return the first ChildBook filtered by the publisher_id column
 * @method     ChildBook|null findOneByAuthorId(int $author_id) Return the first ChildBook filtered by the author_id column
 *
 * @method     ChildBook requirePk($key, ?ConnectionInterface $con = null) Return the ChildBook by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOne(?ConnectionInterface $con = null) Return the first ChildBook matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBook requireOneById(int $id) Return the first ChildBook filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByTitle(string $title) Return the first ChildBook filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByISBN(string $isbn) Return the first ChildBook filtered by the isbn column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByPrice(double $price) Return the first ChildBook filtered by the price column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByPublisherId(int $publisher_id) Return the first ChildBook filtered by the publisher_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByAuthorId(int $author_id) Return the first ChildBook filtered by the author_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBook[]|Collection find(?ConnectionInterface $con = null) Return ChildBook objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildBook> find(?ConnectionInterface $con = null) Return ChildBook objects based on current ModelCriteria
 *
 * @method     ChildBook[]|Collection findById(int|array<int> $id) Return ChildBook objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildBook> findById(int|array<int> $id) Return ChildBook objects filtered by the id column
 * @method     ChildBook[]|Collection findByTitle(string|array<string> $title) Return ChildBook objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildBook> findByTitle(string|array<string> $title) Return ChildBook objects filtered by the title column
 * @method     ChildBook[]|Collection findByISBN(string|array<string> $isbn) Return ChildBook objects filtered by the isbn column
 * @psalm-method Collection&\Traversable<ChildBook> findByISBN(string|array<string> $isbn) Return ChildBook objects filtered by the isbn column
 * @method     ChildBook[]|Collection findByPrice(double|array<double> $price) Return ChildBook objects filtered by the price column
 * @psalm-method Collection&\Traversable<ChildBook> findByPrice(double|array<double> $price) Return ChildBook objects filtered by the price column
 * @method     ChildBook[]|Collection findByPublisherId(int|array<int> $publisher_id) Return ChildBook objects filtered by the publisher_id column
 * @psalm-method Collection&\Traversable<ChildBook> findByPublisherId(int|array<int> $publisher_id) Return ChildBook objects filtered by the publisher_id column
 * @method     ChildBook[]|Collection findByAuthorId(int|array<int> $author_id) Return ChildBook objects filtered by the author_id column
 * @psalm-method Collection&\Traversable<ChildBook> findByAuthorId(int|array<int> $author_id) Return ChildBook objects filtered by the author_id column
 *
 * @method     ChildBook[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildBook> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class BookQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\BookQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\Book', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildBookQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildBookQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildBookQuery) {
            return $criteria;
        }
        $query = new ChildBookQuery();
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
     * @return ChildBook|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(BookTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = BookTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildBook A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, isbn, price, publisher_id, author_id FROM book WHERE id = :p0';
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
            /** @var ChildBook $obj */
            $obj = new ChildBook();
            $obj->hydrate($row);
            BookTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildBook|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(BookTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(BookTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(BookTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(BookTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookTableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(BookTableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the isbn column
     *
     * Example usage:
     * <code>
     * $query->filterByISBN('fooValue');   // WHERE isbn = 'fooValue'
     * $query->filterByISBN('%fooValue%', Criteria::LIKE); // WHERE isbn LIKE '%fooValue%'
     * $query->filterByISBN(['foo', 'bar']); // WHERE isbn IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $iSBN The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByISBN($iSBN = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($iSBN)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookTableMap::COL_ISBN, $iSBN, $comparison);

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
                $this->addUsingAlias(BookTableMap::COL_PRICE, $price['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price['max'])) {
                $this->addUsingAlias(BookTableMap::COL_PRICE, $price['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookTableMap::COL_PRICE, $price, $comparison);

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
     * @see       filterByPublisher()
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
                $this->addUsingAlias(BookTableMap::COL_PUBLISHER_ID, $publisherId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($publisherId['max'])) {
                $this->addUsingAlias(BookTableMap::COL_PUBLISHER_ID, $publisherId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookTableMap::COL_PUBLISHER_ID, $publisherId, $comparison);

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
     * @see       filterByAuthor()
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
                $this->addUsingAlias(BookTableMap::COL_AUTHOR_ID, $authorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($authorId['max'])) {
                $this->addUsingAlias(BookTableMap::COL_AUTHOR_ID, $authorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookTableMap::COL_AUTHOR_ID, $authorId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Publisher object
     *
     * @param \Propel\Tests\Bookstore\Publisher|ObjectCollection $publisher The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPublisher($publisher, ?string $comparison = null)
    {
        if ($publisher instanceof \Propel\Tests\Bookstore\Publisher) {
            return $this
                ->addUsingAlias(BookTableMap::COL_PUBLISHER_ID, $publisher->getId(), $comparison);
        } elseif ($publisher instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookTableMap::COL_PUBLISHER_ID, $publisher->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByPublisher() only accepts arguments of type \Propel\Tests\Bookstore\Publisher or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Publisher relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinPublisher(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Publisher');

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
            $this->addJoinObject($join, 'Publisher');
        }

        return $this;
    }

    /**
     * Use the Publisher relation Publisher object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\PublisherQuery A secondary query class using the current class as primary query
     */
    public function usePublisherQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinPublisher($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Publisher', '\Propel\Tests\Bookstore\PublisherQuery');
    }

    /**
     * Use the Publisher relation Publisher object
     *
     * @param callable(\Propel\Tests\Bookstore\PublisherQuery):\Propel\Tests\Bookstore\PublisherQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withPublisherQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->usePublisherQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Publisher table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\PublisherQuery The inner query object of the EXISTS statement
     */
    public function usePublisherExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\PublisherQuery */
        $q = $this->useExistsQuery('Publisher', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Publisher table for a NOT EXISTS query.
     *
     * @see usePublisherExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\PublisherQuery The inner query object of the NOT EXISTS statement
     */
    public function usePublisherNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\PublisherQuery */
        $q = $this->useExistsQuery('Publisher', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Publisher table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\PublisherQuery The inner query object of the IN statement
     */
    public function useInPublisherQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\PublisherQuery */
        $q = $this->useInQuery('Publisher', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Publisher table for a NOT IN query.
     *
     * @see usePublisherInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\PublisherQuery The inner query object of the NOT IN statement
     */
    public function useNotInPublisherQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\PublisherQuery */
        $q = $this->useInQuery('Publisher', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Author object
     *
     * @param \Propel\Tests\Bookstore\Author|ObjectCollection $author The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAuthor($author, ?string $comparison = null)
    {
        if ($author instanceof \Propel\Tests\Bookstore\Author) {
            return $this
                ->addUsingAlias(BookTableMap::COL_AUTHOR_ID, $author->getId(), $comparison);
        } elseif ($author instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookTableMap::COL_AUTHOR_ID, $author->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByAuthor() only accepts arguments of type \Propel\Tests\Bookstore\Author or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Author relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinAuthor(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Author');

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
            $this->addJoinObject($join, 'Author');
        }

        return $this;
    }

    /**
     * Use the Author relation Author object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery A secondary query class using the current class as primary query
     */
    public function useAuthorQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthor($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Author', '\Propel\Tests\Bookstore\AuthorQuery');
    }

    /**
     * Use the Author relation Author object
     *
     * @param callable(\Propel\Tests\Bookstore\AuthorQuery):\Propel\Tests\Bookstore\AuthorQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withAuthorQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useAuthorQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Author table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the EXISTS statement
     */
    public function useAuthorExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useExistsQuery('Author', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Author table for a NOT EXISTS query.
     *
     * @see useAuthorExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the NOT EXISTS statement
     */
    public function useAuthorNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useExistsQuery('Author', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Author table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the IN statement
     */
    public function useInAuthorQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useInQuery('Author', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Author table for a NOT IN query.
     *
     * @see useAuthorInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the NOT IN statement
     */
    public function useNotInAuthorQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useInQuery('Author', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookSummary object
     *
     * @param \Propel\Tests\Bookstore\BookSummary|ObjectCollection $bookSummary the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookSummary($bookSummary, ?string $comparison = null)
    {
        if ($bookSummary instanceof \Propel\Tests\Bookstore\BookSummary) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $bookSummary->getBookId(), $comparison);

            return $this;
        } elseif ($bookSummary instanceof ObjectCollection) {
            $this
                ->useBookSummaryQuery()
                ->filterByPrimaryKeys($bookSummary->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByBookSummary() only accepts arguments of type \Propel\Tests\Bookstore\BookSummary or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookSummary relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookSummary(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookSummary');

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
            $this->addJoinObject($join, 'BookSummary');
        }

        return $this;
    }

    /**
     * Use the BookSummary relation BookSummary object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookSummaryQuery A secondary query class using the current class as primary query
     */
    public function useBookSummaryQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookSummary($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookSummary', '\Propel\Tests\Bookstore\BookSummaryQuery');
    }

    /**
     * Use the BookSummary relation BookSummary object
     *
     * @param callable(\Propel\Tests\Bookstore\BookSummaryQuery):\Propel\Tests\Bookstore\BookSummaryQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookSummaryQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookSummaryQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookSummary table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookSummaryQuery The inner query object of the EXISTS statement
     */
    public function useBookSummaryExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookSummaryQuery */
        $q = $this->useExistsQuery('BookSummary', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookSummary table for a NOT EXISTS query.
     *
     * @see useBookSummaryExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookSummaryQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookSummaryNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookSummaryQuery */
        $q = $this->useExistsQuery('BookSummary', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookSummary table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookSummaryQuery The inner query object of the IN statement
     */
    public function useInBookSummaryQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookSummaryQuery */
        $q = $this->useInQuery('BookSummary', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookSummary table for a NOT IN query.
     *
     * @see useBookSummaryInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookSummaryQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookSummaryQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookSummaryQuery */
        $q = $this->useInQuery('BookSummary', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Review object
     *
     * @param \Propel\Tests\Bookstore\Review|ObjectCollection $review the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByReview($review, ?string $comparison = null)
    {
        if ($review instanceof \Propel\Tests\Bookstore\Review) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $review->getBookId(), $comparison);

            return $this;
        } elseif ($review instanceof ObjectCollection) {
            $this
                ->useReviewQuery()
                ->filterByPrimaryKeys($review->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByReview() only accepts arguments of type \Propel\Tests\Bookstore\Review or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Review relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinReview(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Review');

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
            $this->addJoinObject($join, 'Review');
        }

        return $this;
    }

    /**
     * Use the Review relation Review object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\ReviewQuery A secondary query class using the current class as primary query
     */
    public function useReviewQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinReview($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Review', '\Propel\Tests\Bookstore\ReviewQuery');
    }

    /**
     * Use the Review relation Review object
     *
     * @param callable(\Propel\Tests\Bookstore\ReviewQuery):\Propel\Tests\Bookstore\ReviewQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withReviewQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useReviewQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Review table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\ReviewQuery The inner query object of the EXISTS statement
     */
    public function useReviewExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\ReviewQuery */
        $q = $this->useExistsQuery('Review', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Review table for a NOT EXISTS query.
     *
     * @see useReviewExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ReviewQuery The inner query object of the NOT EXISTS statement
     */
    public function useReviewNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ReviewQuery */
        $q = $this->useExistsQuery('Review', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Review table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\ReviewQuery The inner query object of the IN statement
     */
    public function useInReviewQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\ReviewQuery */
        $q = $this->useInQuery('Review', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Review table for a NOT IN query.
     *
     * @see useReviewInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ReviewQuery The inner query object of the NOT IN statement
     */
    public function useNotInReviewQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ReviewQuery */
        $q = $this->useInQuery('Review', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Media object
     *
     * @param \Propel\Tests\Bookstore\Media|ObjectCollection $media the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByMedia($media, ?string $comparison = null)
    {
        if ($media instanceof \Propel\Tests\Bookstore\Media) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $media->getBookId(), $comparison);

            return $this;
        } elseif ($media instanceof ObjectCollection) {
            $this
                ->useMediaQuery()
                ->filterByPrimaryKeys($media->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByMedia() only accepts arguments of type \Propel\Tests\Bookstore\Media or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Media relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinMedia(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Media');

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
            $this->addJoinObject($join, 'Media');
        }

        return $this;
    }

    /**
     * Use the Media relation Media object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\MediaQuery A secondary query class using the current class as primary query
     */
    public function useMediaQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinMedia($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Media', '\Propel\Tests\Bookstore\MediaQuery');
    }

    /**
     * Use the Media relation Media object
     *
     * @param callable(\Propel\Tests\Bookstore\MediaQuery):\Propel\Tests\Bookstore\MediaQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withMediaQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useMediaQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Media table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\MediaQuery The inner query object of the EXISTS statement
     */
    public function useMediaExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\MediaQuery */
        $q = $this->useExistsQuery('Media', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Media table for a NOT EXISTS query.
     *
     * @see useMediaExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\MediaQuery The inner query object of the NOT EXISTS statement
     */
    public function useMediaNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\MediaQuery */
        $q = $this->useExistsQuery('Media', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Media table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\MediaQuery The inner query object of the IN statement
     */
    public function useInMediaQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\MediaQuery */
        $q = $this->useInQuery('Media', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Media table for a NOT IN query.
     *
     * @see useMediaInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\MediaQuery The inner query object of the NOT IN statement
     */
    public function useNotInMediaQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\MediaQuery */
        $q = $this->useInQuery('Media', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookListRel object
     *
     * @param \Propel\Tests\Bookstore\BookListRel|ObjectCollection $bookListRel the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookListRel($bookListRel, ?string $comparison = null)
    {
        if ($bookListRel instanceof \Propel\Tests\Bookstore\BookListRel) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $bookListRel->getBookId(), $comparison);

            return $this;
        } elseif ($bookListRel instanceof ObjectCollection) {
            $this
                ->useBookListRelQuery()
                ->filterByPrimaryKeys($bookListRel->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByBookListRel() only accepts arguments of type \Propel\Tests\Bookstore\BookListRel or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookListRel relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookListRel(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookListRel');

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
            $this->addJoinObject($join, 'BookListRel');
        }

        return $this;
    }

    /**
     * Use the BookListRel relation BookListRel object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookListRelQuery A secondary query class using the current class as primary query
     */
    public function useBookListRelQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookListRel($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookListRel', '\Propel\Tests\Bookstore\BookListRelQuery');
    }

    /**
     * Use the BookListRel relation BookListRel object
     *
     * @param callable(\Propel\Tests\Bookstore\BookListRelQuery):\Propel\Tests\Bookstore\BookListRelQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookListRelQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookListRelQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookListRel table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookListRelQuery The inner query object of the EXISTS statement
     */
    public function useBookListRelExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookListRelQuery */
        $q = $this->useExistsQuery('BookListRel', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookListRel table for a NOT EXISTS query.
     *
     * @see useBookListRelExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookListRelQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookListRelNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookListRelQuery */
        $q = $this->useExistsQuery('BookListRel', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookListRel table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookListRelQuery The inner query object of the IN statement
     */
    public function useInBookListRelQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookListRelQuery */
        $q = $this->useInQuery('BookListRel', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookListRel table for a NOT IN query.
     *
     * @see useBookListRelInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookListRelQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookListRelQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookListRelQuery */
        $q = $this->useInQuery('BookListRel', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookListFavorite object
     *
     * @param \Propel\Tests\Bookstore\BookListFavorite|ObjectCollection $bookListFavorite the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookListFavorite($bookListFavorite, ?string $comparison = null)
    {
        if ($bookListFavorite instanceof \Propel\Tests\Bookstore\BookListFavorite) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $bookListFavorite->getBookId(), $comparison);

            return $this;
        } elseif ($bookListFavorite instanceof ObjectCollection) {
            $this
                ->useBookListFavoriteQuery()
                ->filterByPrimaryKeys($bookListFavorite->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByBookListFavorite() only accepts arguments of type \Propel\Tests\Bookstore\BookListFavorite or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookListFavorite relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookListFavorite(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookListFavorite');

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
            $this->addJoinObject($join, 'BookListFavorite');
        }

        return $this;
    }

    /**
     * Use the BookListFavorite relation BookListFavorite object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookListFavoriteQuery A secondary query class using the current class as primary query
     */
    public function useBookListFavoriteQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookListFavorite($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookListFavorite', '\Propel\Tests\Bookstore\BookListFavoriteQuery');
    }

    /**
     * Use the BookListFavorite relation BookListFavorite object
     *
     * @param callable(\Propel\Tests\Bookstore\BookListFavoriteQuery):\Propel\Tests\Bookstore\BookListFavoriteQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookListFavoriteQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookListFavoriteQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookListFavorite table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookListFavoriteQuery The inner query object of the EXISTS statement
     */
    public function useBookListFavoriteExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookListFavoriteQuery */
        $q = $this->useExistsQuery('BookListFavorite', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookListFavorite table for a NOT EXISTS query.
     *
     * @see useBookListFavoriteExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookListFavoriteQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookListFavoriteNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookListFavoriteQuery */
        $q = $this->useExistsQuery('BookListFavorite', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookListFavorite table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookListFavoriteQuery The inner query object of the IN statement
     */
    public function useInBookListFavoriteQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookListFavoriteQuery */
        $q = $this->useInQuery('BookListFavorite', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookListFavorite table for a NOT IN query.
     *
     * @see useBookListFavoriteInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookListFavoriteQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookListFavoriteQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookListFavoriteQuery */
        $q = $this->useInQuery('BookListFavorite', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookOpinion object
     *
     * @param \Propel\Tests\Bookstore\BookOpinion|ObjectCollection $bookOpinion the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookOpinion($bookOpinion, ?string $comparison = null)
    {
        if ($bookOpinion instanceof \Propel\Tests\Bookstore\BookOpinion) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $bookOpinion->getBookId(), $comparison);

            return $this;
        } elseif ($bookOpinion instanceof ObjectCollection) {
            $this
                ->useBookOpinionQuery()
                ->filterByPrimaryKeys($bookOpinion->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByBookOpinion() only accepts arguments of type \Propel\Tests\Bookstore\BookOpinion or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookOpinion relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookOpinion(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookOpinion');

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
            $this->addJoinObject($join, 'BookOpinion');
        }

        return $this;
    }

    /**
     * Use the BookOpinion relation BookOpinion object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookOpinionQuery A secondary query class using the current class as primary query
     */
    public function useBookOpinionQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookOpinion($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookOpinion', '\Propel\Tests\Bookstore\BookOpinionQuery');
    }

    /**
     * Use the BookOpinion relation BookOpinion object
     *
     * @param callable(\Propel\Tests\Bookstore\BookOpinionQuery):\Propel\Tests\Bookstore\BookOpinionQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookOpinionQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookOpinionQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookOpinion table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookOpinionQuery The inner query object of the EXISTS statement
     */
    public function useBookOpinionExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookOpinionQuery */
        $q = $this->useExistsQuery('BookOpinion', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookOpinion table for a NOT EXISTS query.
     *
     * @see useBookOpinionExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookOpinionQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookOpinionNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookOpinionQuery */
        $q = $this->useExistsQuery('BookOpinion', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookOpinion table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookOpinionQuery The inner query object of the IN statement
     */
    public function useInBookOpinionQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookOpinionQuery */
        $q = $this->useInQuery('BookOpinion', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookOpinion table for a NOT IN query.
     *
     * @see useBookOpinionInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookOpinionQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookOpinionQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookOpinionQuery */
        $q = $this->useInQuery('BookOpinion', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\ReaderFavorite object
     *
     * @param \Propel\Tests\Bookstore\ReaderFavorite|ObjectCollection $readerFavorite the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByReaderFavorite($readerFavorite, ?string $comparison = null)
    {
        if ($readerFavorite instanceof \Propel\Tests\Bookstore\ReaderFavorite) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $readerFavorite->getBookId(), $comparison);

            return $this;
        } elseif ($readerFavorite instanceof ObjectCollection) {
            $this
                ->useReaderFavoriteQuery()
                ->filterByPrimaryKeys($readerFavorite->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByReaderFavorite() only accepts arguments of type \Propel\Tests\Bookstore\ReaderFavorite or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ReaderFavorite relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinReaderFavorite(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ReaderFavorite');

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
            $this->addJoinObject($join, 'ReaderFavorite');
        }

        return $this;
    }

    /**
     * Use the ReaderFavorite relation ReaderFavorite object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\ReaderFavoriteQuery A secondary query class using the current class as primary query
     */
    public function useReaderFavoriteQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinReaderFavorite($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ReaderFavorite', '\Propel\Tests\Bookstore\ReaderFavoriteQuery');
    }

    /**
     * Use the ReaderFavorite relation ReaderFavorite object
     *
     * @param callable(\Propel\Tests\Bookstore\ReaderFavoriteQuery):\Propel\Tests\Bookstore\ReaderFavoriteQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withReaderFavoriteQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useReaderFavoriteQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ReaderFavorite table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\ReaderFavoriteQuery The inner query object of the EXISTS statement
     */
    public function useReaderFavoriteExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\ReaderFavoriteQuery */
        $q = $this->useExistsQuery('ReaderFavorite', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ReaderFavorite table for a NOT EXISTS query.
     *
     * @see useReaderFavoriteExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ReaderFavoriteQuery The inner query object of the NOT EXISTS statement
     */
    public function useReaderFavoriteNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ReaderFavoriteQuery */
        $q = $this->useExistsQuery('ReaderFavorite', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ReaderFavorite table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\ReaderFavoriteQuery The inner query object of the IN statement
     */
    public function useInReaderFavoriteQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\ReaderFavoriteQuery */
        $q = $this->useInQuery('ReaderFavorite', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ReaderFavorite table for a NOT IN query.
     *
     * @see useReaderFavoriteInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ReaderFavoriteQuery The inner query object of the NOT IN statement
     */
    public function useNotInReaderFavoriteQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ReaderFavoriteQuery */
        $q = $this->useInQuery('ReaderFavorite', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookstoreContest object
     *
     * @param \Propel\Tests\Bookstore\BookstoreContest|ObjectCollection $bookstoreContest the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookstoreContest($bookstoreContest, ?string $comparison = null)
    {
        if ($bookstoreContest instanceof \Propel\Tests\Bookstore\BookstoreContest) {
            $this
                ->addUsingAlias(BookTableMap::COL_ID, $bookstoreContest->getPrizeBookId(), $comparison);

            return $this;
        } elseif ($bookstoreContest instanceof ObjectCollection) {
            $this
                ->useBookstoreContestQuery()
                ->filterByPrimaryKeys($bookstoreContest->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByBookstoreContest() only accepts arguments of type \Propel\Tests\Bookstore\BookstoreContest or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookstoreContest relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookstoreContest(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookstoreContest');

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
            $this->addJoinObject($join, 'BookstoreContest');
        }

        return $this;
    }

    /**
     * Use the BookstoreContest relation BookstoreContest object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestQuery A secondary query class using the current class as primary query
     */
    public function useBookstoreContestQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBookstoreContest($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookstoreContest', '\Propel\Tests\Bookstore\BookstoreContestQuery');
    }

    /**
     * Use the BookstoreContest relation BookstoreContest object
     *
     * @param callable(\Propel\Tests\Bookstore\BookstoreContestQuery):\Propel\Tests\Bookstore\BookstoreContestQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookstoreContestQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useBookstoreContestQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookstoreContest table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestQuery The inner query object of the EXISTS statement
     */
    public function useBookstoreContestExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestQuery */
        $q = $this->useExistsQuery('BookstoreContest', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookstoreContest table for a NOT EXISTS query.
     *
     * @see useBookstoreContestExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookstoreContestNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestQuery */
        $q = $this->useExistsQuery('BookstoreContest', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookstoreContest table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestQuery The inner query object of the IN statement
     */
    public function useInBookstoreContestQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestQuery */
        $q = $this->useInQuery('BookstoreContest', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookstoreContest table for a NOT IN query.
     *
     * @see useBookstoreContestInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreContestQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookstoreContestQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreContestQuery */
        $q = $this->useInQuery('BookstoreContest', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\PolymorphicRelationLog object
     *
     * @param \Propel\Tests\Bookstore\PolymorphicRelationLog|ObjectCollection $polymorphicRelationLog the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPolymorphicRelationLog($polymorphicRelationLog, ?string $comparison = null)
    {
        if ($polymorphicRelationLog instanceof \Propel\Tests\Bookstore\PolymorphicRelationLog) {
            $this
                ->where("'book' = ?", $polymorphicRelationLog->getTargetType(), 2)
                ->addUsingAlias(BookTableMap::COL_ID, $polymorphicRelationLog->getTargetId(), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByPolymorphicRelationLog() only accepts arguments of type \Propel\Tests\Bookstore\PolymorphicRelationLog');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PolymorphicRelationLog relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinPolymorphicRelationLog(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PolymorphicRelationLog');

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
            $this->addJoinObject($join, 'PolymorphicRelationLog');
        }

        return $this;
    }

    /**
     * Use the PolymorphicRelationLog relation PolymorphicRelationLog object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\PolymorphicRelationLogQuery A secondary query class using the current class as primary query
     */
    public function usePolymorphicRelationLogQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinPolymorphicRelationLog($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PolymorphicRelationLog', '\Propel\Tests\Bookstore\PolymorphicRelationLogQuery');
    }

    /**
     * Use the PolymorphicRelationLog relation PolymorphicRelationLog object
     *
     * @param callable(\Propel\Tests\Bookstore\PolymorphicRelationLogQuery):\Propel\Tests\Bookstore\PolymorphicRelationLogQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withPolymorphicRelationLogQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->usePolymorphicRelationLogQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to PolymorphicRelationLog table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\PolymorphicRelationLogQuery The inner query object of the EXISTS statement
     */
    public function usePolymorphicRelationLogExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\PolymorphicRelationLogQuery */
        $q = $this->useExistsQuery('PolymorphicRelationLog', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to PolymorphicRelationLog table for a NOT EXISTS query.
     *
     * @see usePolymorphicRelationLogExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\PolymorphicRelationLogQuery The inner query object of the NOT EXISTS statement
     */
    public function usePolymorphicRelationLogNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\PolymorphicRelationLogQuery */
        $q = $this->useExistsQuery('PolymorphicRelationLog', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to PolymorphicRelationLog table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\PolymorphicRelationLogQuery The inner query object of the IN statement
     */
    public function useInPolymorphicRelationLogQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\PolymorphicRelationLogQuery */
        $q = $this->useInQuery('PolymorphicRelationLog', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to PolymorphicRelationLog table for a NOT IN query.
     *
     * @see usePolymorphicRelationLogInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\PolymorphicRelationLogQuery The inner query object of the NOT IN statement
     */
    public function useNotInPolymorphicRelationLogQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\PolymorphicRelationLogQuery */
        $q = $this->useInQuery('PolymorphicRelationLog', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related BookClubList object
     * using the book_x_list table as cross reference
     *
     * @param BookClubList $bookClubList the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL and Criteria::IN for queries
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookClubList($bookClubList, ?string $comparison = null)
    {
        $this
            ->useBookListRelQuery()
            ->filterByBookClubList($bookClubList, $comparison)
            ->endUse();

        return $this;
    }

    /**
     * Filter the query by a related BookClubList object
     * using the book_club_list_favorite_books table as cross reference
     *
     * @param BookClubList $bookClubList the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL and Criteria::IN for queries
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFavoriteBookClubList($bookClubList, ?string $comparison = null)
    {
        $this
            ->useBookListFavoriteQuery()
            ->filterByFavoriteBookClubList($bookClubList, $comparison)
            ->endUse();

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildBook $book Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($book = null)
    {
        if ($book) {
            $this->addUsingAlias(BookTableMap::COL_ID, $book->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the book table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BookTableMap::clearInstancePool();
            BookTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(BookTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            BookTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            BookTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
