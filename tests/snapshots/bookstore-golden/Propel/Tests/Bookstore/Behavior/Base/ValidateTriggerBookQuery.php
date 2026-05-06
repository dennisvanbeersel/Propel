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
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBook as ChildValidateTriggerBook;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery as ChildValidateTriggerBookI18nQuery;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBookQuery as ChildValidateTriggerBookQuery;
use Propel\Tests\Bookstore\Behavior\Map\ValidateTriggerBookTableMap;

/**
 * Base class that represents a query for the `validate_trigger_book` table.
 *
 * Book Table
 *
 * @method     ChildValidateTriggerBookQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildValidateTriggerBookQuery orderByISBN($order = Criteria::ASC) Order by the isbn column
 * @method     ChildValidateTriggerBookQuery orderByPrice($order = Criteria::ASC) Order by the price column
 * @method     ChildValidateTriggerBookQuery orderByPublisherId($order = Criteria::ASC) Order by the publisher_id column
 * @method     ChildValidateTriggerBookQuery orderByAuthorId($order = Criteria::ASC) Order by the author_id column
 * @method     ChildValidateTriggerBookQuery orderByDescendantClass($order = Criteria::ASC) Order by the descendant_class column
 *
 * @method     ChildValidateTriggerBookQuery groupById() Group by the id column
 * @method     ChildValidateTriggerBookQuery groupByISBN() Group by the isbn column
 * @method     ChildValidateTriggerBookQuery groupByPrice() Group by the price column
 * @method     ChildValidateTriggerBookQuery groupByPublisherId() Group by the publisher_id column
 * @method     ChildValidateTriggerBookQuery groupByAuthorId() Group by the author_id column
 * @method     ChildValidateTriggerBookQuery groupByDescendantClass() Group by the descendant_class column
 *
 * @method     ChildValidateTriggerBookQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildValidateTriggerBookQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildValidateTriggerBookQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildValidateTriggerBookQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildValidateTriggerBookQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildValidateTriggerBookQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildValidateTriggerBookQuery leftJoinValidateTriggerFiction($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateTriggerFiction relation
 * @method     ChildValidateTriggerBookQuery rightJoinValidateTriggerFiction($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateTriggerFiction relation
 * @method     ChildValidateTriggerBookQuery innerJoinValidateTriggerFiction($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateTriggerFiction relation
 *
 * @method     ChildValidateTriggerBookQuery joinWithValidateTriggerFiction($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateTriggerFiction relation
 *
 * @method     ChildValidateTriggerBookQuery leftJoinWithValidateTriggerFiction() Adds a LEFT JOIN clause and with to the query using the ValidateTriggerFiction relation
 * @method     ChildValidateTriggerBookQuery rightJoinWithValidateTriggerFiction() Adds a RIGHT JOIN clause and with to the query using the ValidateTriggerFiction relation
 * @method     ChildValidateTriggerBookQuery innerJoinWithValidateTriggerFiction() Adds a INNER JOIN clause and with to the query using the ValidateTriggerFiction relation
 *
 * @method     ChildValidateTriggerBookQuery leftJoinValidateTriggerComic($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateTriggerComic relation
 * @method     ChildValidateTriggerBookQuery rightJoinValidateTriggerComic($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateTriggerComic relation
 * @method     ChildValidateTriggerBookQuery innerJoinValidateTriggerComic($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateTriggerComic relation
 *
 * @method     ChildValidateTriggerBookQuery joinWithValidateTriggerComic($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateTriggerComic relation
 *
 * @method     ChildValidateTriggerBookQuery leftJoinWithValidateTriggerComic() Adds a LEFT JOIN clause and with to the query using the ValidateTriggerComic relation
 * @method     ChildValidateTriggerBookQuery rightJoinWithValidateTriggerComic() Adds a RIGHT JOIN clause and with to the query using the ValidateTriggerComic relation
 * @method     ChildValidateTriggerBookQuery innerJoinWithValidateTriggerComic() Adds a INNER JOIN clause and with to the query using the ValidateTriggerComic relation
 *
 * @method     ChildValidateTriggerBookQuery leftJoinValidateTriggerBookI18n($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateTriggerBookI18n relation
 * @method     ChildValidateTriggerBookQuery rightJoinValidateTriggerBookI18n($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateTriggerBookI18n relation
 * @method     ChildValidateTriggerBookQuery innerJoinValidateTriggerBookI18n($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateTriggerBookI18n relation
 *
 * @method     ChildValidateTriggerBookQuery joinWithValidateTriggerBookI18n($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateTriggerBookI18n relation
 *
 * @method     ChildValidateTriggerBookQuery leftJoinWithValidateTriggerBookI18n() Adds a LEFT JOIN clause and with to the query using the ValidateTriggerBookI18n relation
 * @method     ChildValidateTriggerBookQuery rightJoinWithValidateTriggerBookI18n() Adds a RIGHT JOIN clause and with to the query using the ValidateTriggerBookI18n relation
 * @method     ChildValidateTriggerBookQuery innerJoinWithValidateTriggerBookI18n() Adds a INNER JOIN clause and with to the query using the ValidateTriggerBookI18n relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery|\Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery|\Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildValidateTriggerBook|null findOne(?ConnectionInterface $con = null) Return the first ChildValidateTriggerBook matching the query
 * @method     ChildValidateTriggerBook findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildValidateTriggerBook matching the query, or a new ChildValidateTriggerBook object populated from the query conditions when no match is found
 *
 * @method     ChildValidateTriggerBook|null findOneById(int $id) Return the first ChildValidateTriggerBook filtered by the id column
 * @method     ChildValidateTriggerBook|null findOneByISBN(string $isbn) Return the first ChildValidateTriggerBook filtered by the isbn column
 * @method     ChildValidateTriggerBook|null findOneByPrice(double $price) Return the first ChildValidateTriggerBook filtered by the price column
 * @method     ChildValidateTriggerBook|null findOneByPublisherId(int $publisher_id) Return the first ChildValidateTriggerBook filtered by the publisher_id column
 * @method     ChildValidateTriggerBook|null findOneByAuthorId(int $author_id) Return the first ChildValidateTriggerBook filtered by the author_id column
 * @method     ChildValidateTriggerBook|null findOneByDescendantClass(string $descendant_class) Return the first ChildValidateTriggerBook filtered by the descendant_class column
 *
 * @method     ChildValidateTriggerBook requirePk($key, ?ConnectionInterface $con = null) Return the ChildValidateTriggerBook by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateTriggerBook requireOne(?ConnectionInterface $con = null) Return the first ChildValidateTriggerBook matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateTriggerBook requireOneById(int $id) Return the first ChildValidateTriggerBook filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateTriggerBook requireOneByISBN(string $isbn) Return the first ChildValidateTriggerBook filtered by the isbn column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateTriggerBook requireOneByPrice(double $price) Return the first ChildValidateTriggerBook filtered by the price column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateTriggerBook requireOneByPublisherId(int $publisher_id) Return the first ChildValidateTriggerBook filtered by the publisher_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateTriggerBook requireOneByAuthorId(int $author_id) Return the first ChildValidateTriggerBook filtered by the author_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateTriggerBook requireOneByDescendantClass(string $descendant_class) Return the first ChildValidateTriggerBook filtered by the descendant_class column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateTriggerBook[]|Collection find(?ConnectionInterface $con = null) Return ChildValidateTriggerBook objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildValidateTriggerBook> find(?ConnectionInterface $con = null) Return ChildValidateTriggerBook objects based on current ModelCriteria
 *
 * @method     ChildValidateTriggerBook[]|Collection findById(int|array<int> $id) Return ChildValidateTriggerBook objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildValidateTriggerBook> findById(int|array<int> $id) Return ChildValidateTriggerBook objects filtered by the id column
 * @method     ChildValidateTriggerBook[]|Collection findByISBN(string|array<string> $isbn) Return ChildValidateTriggerBook objects filtered by the isbn column
 * @psalm-method Collection&\Traversable<ChildValidateTriggerBook> findByISBN(string|array<string> $isbn) Return ChildValidateTriggerBook objects filtered by the isbn column
 * @method     ChildValidateTriggerBook[]|Collection findByPrice(double|array<double> $price) Return ChildValidateTriggerBook objects filtered by the price column
 * @psalm-method Collection&\Traversable<ChildValidateTriggerBook> findByPrice(double|array<double> $price) Return ChildValidateTriggerBook objects filtered by the price column
 * @method     ChildValidateTriggerBook[]|Collection findByPublisherId(int|array<int> $publisher_id) Return ChildValidateTriggerBook objects filtered by the publisher_id column
 * @psalm-method Collection&\Traversable<ChildValidateTriggerBook> findByPublisherId(int|array<int> $publisher_id) Return ChildValidateTriggerBook objects filtered by the publisher_id column
 * @method     ChildValidateTriggerBook[]|Collection findByAuthorId(int|array<int> $author_id) Return ChildValidateTriggerBook objects filtered by the author_id column
 * @psalm-method Collection&\Traversable<ChildValidateTriggerBook> findByAuthorId(int|array<int> $author_id) Return ChildValidateTriggerBook objects filtered by the author_id column
 * @method     ChildValidateTriggerBook[]|Collection findByDescendantClass(string|array<string> $descendant_class) Return ChildValidateTriggerBook objects filtered by the descendant_class column
 * @psalm-method Collection&\Traversable<ChildValidateTriggerBook> findByDescendantClass(string|array<string> $descendant_class) Return ChildValidateTriggerBook objects filtered by the descendant_class column
 *
 * @method     ChildValidateTriggerBook[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildValidateTriggerBook> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ValidateTriggerBookQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ValidateTriggerBookQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerBook', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildValidateTriggerBookQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildValidateTriggerBookQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildValidateTriggerBookQuery) {
            return $criteria;
        }
        $query = new ChildValidateTriggerBookQuery();
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
     * @return ChildValidateTriggerBook|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ValidateTriggerBookTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildValidateTriggerBook A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, isbn, price, publisher_id, author_id, descendant_class FROM validate_trigger_book WHERE id = :p0';
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
            /** @var ChildValidateTriggerBook $obj */
            $obj = new ChildValidateTriggerBook();
            $obj->hydrate($row);
            ValidateTriggerBookTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildValidateTriggerBook|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_ISBN, $iSBN, $comparison);

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
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_PRICE, $price['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price['max'])) {
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_PRICE, $price['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_PRICE, $price, $comparison);

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
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_PUBLISHER_ID, $publisherId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($publisherId['max'])) {
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_PUBLISHER_ID, $publisherId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_PUBLISHER_ID, $publisherId, $comparison);

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
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_AUTHOR_ID, $authorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($authorId['max'])) {
                $this->addUsingAlias(ValidateTriggerBookTableMap::COL_AUTHOR_ID, $authorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_AUTHOR_ID, $authorId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the descendant_class column
     *
     * Example usage:
     * <code>
     * $query->filterByDescendantClass('fooValue');   // WHERE descendant_class = 'fooValue'
     * $query->filterByDescendantClass('%fooValue%', Criteria::LIKE); // WHERE descendant_class LIKE '%fooValue%'
     * $query->filterByDescendantClass(['foo', 'bar']); // WHERE descendant_class IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $descendantClass The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByDescendantClass($descendantClass = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($descendantClass)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS, $descendantClass, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateTriggerFiction object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateTriggerFiction|ObjectCollection $validateTriggerFiction the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateTriggerFiction($validateTriggerFiction, ?string $comparison = null)
    {
        if ($validateTriggerFiction instanceof \Propel\Tests\Bookstore\Behavior\ValidateTriggerFiction) {
            $this
                ->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $validateTriggerFiction->getId(), $comparison);

            return $this;
        } elseif ($validateTriggerFiction instanceof ObjectCollection) {
            $this
                ->useValidateTriggerFictionQuery()
                ->filterByPrimaryKeys($validateTriggerFiction->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByValidateTriggerFiction() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateTriggerFiction or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateTriggerFiction relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateTriggerFiction(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateTriggerFiction');

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
            $this->addJoinObject($join, 'ValidateTriggerFiction');
        }

        return $this;
    }

    /**
     * Use the ValidateTriggerFiction relation ValidateTriggerFiction object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery A secondary query class using the current class as primary query
     */
    public function useValidateTriggerFictionQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinValidateTriggerFiction($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateTriggerFiction', '\Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery');
    }

    /**
     * Use the ValidateTriggerFiction relation ValidateTriggerFiction object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery):\Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateTriggerFictionQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useValidateTriggerFictionQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateTriggerFiction table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery The inner query object of the EXISTS statement
     */
    public function useValidateTriggerFictionExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery */
        $q = $this->useExistsQuery('ValidateTriggerFiction', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerFiction table for a NOT EXISTS query.
     *
     * @see useValidateTriggerFictionExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateTriggerFictionNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery */
        $q = $this->useExistsQuery('ValidateTriggerFiction', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerFiction table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery The inner query object of the IN statement
     */
    public function useInValidateTriggerFictionQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery */
        $q = $this->useInQuery('ValidateTriggerFiction', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerFiction table for a NOT IN query.
     *
     * @see useValidateTriggerFictionInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateTriggerFictionQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery */
        $q = $this->useInQuery('ValidateTriggerFiction', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateTriggerComic object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateTriggerComic|ObjectCollection $validateTriggerComic the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateTriggerComic($validateTriggerComic, ?string $comparison = null)
    {
        if ($validateTriggerComic instanceof \Propel\Tests\Bookstore\Behavior\ValidateTriggerComic) {
            $this
                ->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $validateTriggerComic->getId(), $comparison);

            return $this;
        } elseif ($validateTriggerComic instanceof ObjectCollection) {
            $this
                ->useValidateTriggerComicQuery()
                ->filterByPrimaryKeys($validateTriggerComic->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByValidateTriggerComic() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateTriggerComic or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateTriggerComic relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateTriggerComic(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateTriggerComic');

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
            $this->addJoinObject($join, 'ValidateTriggerComic');
        }

        return $this;
    }

    /**
     * Use the ValidateTriggerComic relation ValidateTriggerComic object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery A secondary query class using the current class as primary query
     */
    public function useValidateTriggerComicQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinValidateTriggerComic($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateTriggerComic', '\Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery');
    }

    /**
     * Use the ValidateTriggerComic relation ValidateTriggerComic object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery):\Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateTriggerComicQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useValidateTriggerComicQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateTriggerComic table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery The inner query object of the EXISTS statement
     */
    public function useValidateTriggerComicExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery */
        $q = $this->useExistsQuery('ValidateTriggerComic', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerComic table for a NOT EXISTS query.
     *
     * @see useValidateTriggerComicExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateTriggerComicNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery */
        $q = $this->useExistsQuery('ValidateTriggerComic', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerComic table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery The inner query object of the IN statement
     */
    public function useInValidateTriggerComicQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery */
        $q = $this->useInQuery('ValidateTriggerComic', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerComic table for a NOT IN query.
     *
     * @see useValidateTriggerComicInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateTriggerComicQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery */
        $q = $this->useInQuery('ValidateTriggerComic', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18n object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18n|ObjectCollection $validateTriggerBookI18n the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateTriggerBookI18n($validateTriggerBookI18n, ?string $comparison = null)
    {
        if ($validateTriggerBookI18n instanceof \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18n) {
            $this
                ->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $validateTriggerBookI18n->getId(), $comparison);

            return $this;
        } elseif ($validateTriggerBookI18n instanceof ObjectCollection) {
            $this
                ->useValidateTriggerBookI18nQuery()
                ->filterByPrimaryKeys($validateTriggerBookI18n->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByValidateTriggerBookI18n() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18n or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateTriggerBookI18n relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateTriggerBookI18n(?string $relationAlias = null, ?string $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateTriggerBookI18n');

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
            $this->addJoinObject($join, 'ValidateTriggerBookI18n');
        }

        return $this;
    }

    /**
     * Use the ValidateTriggerBookI18n relation ValidateTriggerBookI18n object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery A secondary query class using the current class as primary query
     */
    public function useValidateTriggerBookI18nQuery(?string $relationAlias = null, string $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinValidateTriggerBookI18n($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateTriggerBookI18n', '\Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery');
    }

    /**
     * Use the ValidateTriggerBookI18n relation ValidateTriggerBookI18n object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery):\Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateTriggerBookI18nQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = 'LEFT JOIN'
    ) {
        $relatedQuery = $this->useValidateTriggerBookI18nQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateTriggerBookI18n table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery The inner query object of the EXISTS statement
     */
    public function useValidateTriggerBookI18nExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery */
        $q = $this->useExistsQuery('ValidateTriggerBookI18n', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerBookI18n table for a NOT EXISTS query.
     *
     * @see useValidateTriggerBookI18nExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateTriggerBookI18nNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery */
        $q = $this->useExistsQuery('ValidateTriggerBookI18n', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerBookI18n table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery The inner query object of the IN statement
     */
    public function useInValidateTriggerBookI18nQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery */
        $q = $this->useInQuery('ValidateTriggerBookI18n', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateTriggerBookI18n table for a NOT IN query.
     *
     * @see useValidateTriggerBookI18nInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateTriggerBookI18nQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery */
        $q = $this->useInQuery('ValidateTriggerBookI18n', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildValidateTriggerBook $validateTriggerBook Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($validateTriggerBook = null)
    {
        if ($validateTriggerBook) {
            $this->addUsingAlias(ValidateTriggerBookTableMap::COL_ID, $validateTriggerBook->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the validate_trigger_book table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ValidateTriggerBookTableMap::clearInstancePool();
            ValidateTriggerBookTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ValidateTriggerBookTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ValidateTriggerBookTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ValidateTriggerBookTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // i18n behavior

    /**
     * Adds a JOIN clause to the query using the i18n relation
     *
     * @param string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param string $relationAlias optional alias for the relation
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return ChildValidateTriggerBookQuery The current query, for fluid interface
     */
    public function joinI18n($locale = 'en_US', $relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $relationName = $relationAlias ? $relationAlias : 'ValidateTriggerBookI18n';

        return $this
            ->joinValidateTriggerBookI18n($relationAlias, $joinType)
            ->addJoinCondition($relationName, $relationName . '.Locale = ?', $locale);
    }

    /**
     * Adds a JOIN clause to the query and hydrates the related I18n object.
     * Shortcut for $c->joinI18n($locale)->with()
     *
     * @param string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return $this The current query, for fluid interface
     */
    public function joinWithI18n($locale = 'en_US', $joinType = Criteria::LEFT_JOIN)
    {
        $this
            ->joinI18n($locale, null, $joinType)
            ->with('ValidateTriggerBookI18n');
        $this->with['ValidateTriggerBookI18n']->setIsWithOneToMany(false);

        return $this;
    }

    /**
     * Use the I18n relation query object
     *
     * @see       useQuery()
     *
     * @param string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param string $relationAlias optional alias for the relation
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return ChildValidateTriggerBookI18nQuery A secondary query class using the current class as primary query
     */
    public function useI18nQuery($locale = 'en_US', $relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinI18n($locale, $relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateTriggerBookI18n', '\Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery');
    }

}
