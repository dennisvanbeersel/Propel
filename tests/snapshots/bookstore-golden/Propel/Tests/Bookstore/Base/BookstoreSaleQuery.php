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
use Propel\Tests\Bookstore\BookstoreSale as ChildBookstoreSale;
use Propel\Tests\Bookstore\BookstoreSaleQuery as ChildBookstoreSaleQuery;
use Propel\Tests\Bookstore\Map\BookstoreSaleTableMap;

/**
 * Base class that represents a query for the `bookstore_sale` table.
 *
 * @method     ChildBookstoreSaleQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildBookstoreSaleQuery orderByBookstoreId($order = Criteria::ASC) Order by the bookstore_id column
 * @method     ChildBookstoreSaleQuery orderByPublisherId($order = Criteria::ASC) Order by the publisher_id column
 * @method     ChildBookstoreSaleQuery orderBySaleName($order = Criteria::ASC) Order by the sale_name column
 * @method     ChildBookstoreSaleQuery orderByDiscount($order = Criteria::ASC) Order by the discount column
 *
 * @method     ChildBookstoreSaleQuery groupById() Group by the id column
 * @method     ChildBookstoreSaleQuery groupByBookstoreId() Group by the bookstore_id column
 * @method     ChildBookstoreSaleQuery groupByPublisherId() Group by the publisher_id column
 * @method     ChildBookstoreSaleQuery groupBySaleName() Group by the sale_name column
 * @method     ChildBookstoreSaleQuery groupByDiscount() Group by the discount column
 *
 * @method     ChildBookstoreSaleQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildBookstoreSaleQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildBookstoreSaleQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildBookstoreSaleQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildBookstoreSaleQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildBookstoreSaleQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildBookstoreSaleQuery leftJoinBookstore($relationAlias = null) Adds a LEFT JOIN clause to the query using the Bookstore relation
 * @method     ChildBookstoreSaleQuery rightJoinBookstore($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Bookstore relation
 * @method     ChildBookstoreSaleQuery innerJoinBookstore($relationAlias = null) Adds a INNER JOIN clause to the query using the Bookstore relation
 *
 * @method     ChildBookstoreSaleQuery joinWithBookstore($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Bookstore relation
 *
 * @method     ChildBookstoreSaleQuery leftJoinWithBookstore() Adds a LEFT JOIN clause and with to the query using the Bookstore relation
 * @method     ChildBookstoreSaleQuery rightJoinWithBookstore() Adds a RIGHT JOIN clause and with to the query using the Bookstore relation
 * @method     ChildBookstoreSaleQuery innerJoinWithBookstore() Adds a INNER JOIN clause and with to the query using the Bookstore relation
 *
 * @method     ChildBookstoreSaleQuery leftJoinPublisher($relationAlias = null) Adds a LEFT JOIN clause to the query using the Publisher relation
 * @method     ChildBookstoreSaleQuery rightJoinPublisher($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Publisher relation
 * @method     ChildBookstoreSaleQuery innerJoinPublisher($relationAlias = null) Adds a INNER JOIN clause to the query using the Publisher relation
 *
 * @method     ChildBookstoreSaleQuery joinWithPublisher($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Publisher relation
 *
 * @method     ChildBookstoreSaleQuery leftJoinWithPublisher() Adds a LEFT JOIN clause and with to the query using the Publisher relation
 * @method     ChildBookstoreSaleQuery rightJoinWithPublisher() Adds a RIGHT JOIN clause and with to the query using the Publisher relation
 * @method     ChildBookstoreSaleQuery innerJoinWithPublisher() Adds a INNER JOIN clause and with to the query using the Publisher relation
 *
 * @method     \Propel\Tests\Bookstore\BookstoreQuery|\Propel\Tests\Bookstore\PublisherQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildBookstoreSale|null findOne(?ConnectionInterface $con = null) Return the first ChildBookstoreSale matching the query
 * @method     ChildBookstoreSale findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildBookstoreSale matching the query, or a new ChildBookstoreSale object populated from the query conditions when no match is found
 *
 * @method     ChildBookstoreSale|null findOneById(int $id) Return the first ChildBookstoreSale filtered by the id column
 * @method     ChildBookstoreSale|null findOneByBookstoreId(int $bookstore_id) Return the first ChildBookstoreSale filtered by the bookstore_id column
 * @method     ChildBookstoreSale|null findOneByPublisherId(int $publisher_id) Return the first ChildBookstoreSale filtered by the publisher_id column
 * @method     ChildBookstoreSale|null findOneBySaleName(string $sale_name) Return the first ChildBookstoreSale filtered by the sale_name column
 * @method     ChildBookstoreSale|null findOneByDiscount(int $discount) Return the first ChildBookstoreSale filtered by the discount column
 *
 * @method     ChildBookstoreSale requirePk($key, ?ConnectionInterface $con = null) Return the ChildBookstoreSale by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreSale requireOne(?ConnectionInterface $con = null) Return the first ChildBookstoreSale matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookstoreSale requireOneById(int $id) Return the first ChildBookstoreSale filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreSale requireOneByBookstoreId(int $bookstore_id) Return the first ChildBookstoreSale filtered by the bookstore_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreSale requireOneByPublisherId(int $publisher_id) Return the first ChildBookstoreSale filtered by the publisher_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreSale requireOneBySaleName(string $sale_name) Return the first ChildBookstoreSale filtered by the sale_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreSale requireOneByDiscount(int $discount) Return the first ChildBookstoreSale filtered by the discount column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookstoreSale[]|Collection find(?ConnectionInterface $con = null) Return ChildBookstoreSale objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildBookstoreSale> find(?ConnectionInterface $con = null) Return ChildBookstoreSale objects based on current ModelCriteria
 *
 * @method     ChildBookstoreSale[]|Collection findById(int|array<int> $id) Return ChildBookstoreSale objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildBookstoreSale> findById(int|array<int> $id) Return ChildBookstoreSale objects filtered by the id column
 * @method     ChildBookstoreSale[]|Collection findByBookstoreId(int|array<int> $bookstore_id) Return ChildBookstoreSale objects filtered by the bookstore_id column
 * @psalm-method Collection&\Traversable<ChildBookstoreSale> findByBookstoreId(int|array<int> $bookstore_id) Return ChildBookstoreSale objects filtered by the bookstore_id column
 * @method     ChildBookstoreSale[]|Collection findByPublisherId(int|array<int> $publisher_id) Return ChildBookstoreSale objects filtered by the publisher_id column
 * @psalm-method Collection&\Traversable<ChildBookstoreSale> findByPublisherId(int|array<int> $publisher_id) Return ChildBookstoreSale objects filtered by the publisher_id column
 * @method     ChildBookstoreSale[]|Collection findBySaleName(string|array<string> $sale_name) Return ChildBookstoreSale objects filtered by the sale_name column
 * @psalm-method Collection&\Traversable<ChildBookstoreSale> findBySaleName(string|array<string> $sale_name) Return ChildBookstoreSale objects filtered by the sale_name column
 * @method     ChildBookstoreSale[]|Collection findByDiscount(int|array<int> $discount) Return ChildBookstoreSale objects filtered by the discount column
 * @psalm-method Collection&\Traversable<ChildBookstoreSale> findByDiscount(int|array<int> $discount) Return ChildBookstoreSale objects filtered by the discount column
 *
 * @method     ChildBookstoreSale[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildBookstoreSale> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class BookstoreSaleQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\BookstoreSaleQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\BookstoreSale', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildBookstoreSaleQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildBookstoreSaleQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildBookstoreSaleQuery) {
            return $criteria;
        }
        $query = new ChildBookstoreSaleQuery();
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
     * @return ChildBookstoreSale|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(BookstoreSaleTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = BookstoreSaleTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildBookstoreSale A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, bookstore_id, publisher_id, sale_name, discount FROM bookstore_sale WHERE id = :p0';
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
            /** @var ChildBookstoreSale $obj */
            $obj = new ChildBookstoreSale();
            $obj->hydrate($row);
            BookstoreSaleTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildBookstoreSale|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(BookstoreSaleTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(BookstoreSaleTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(BookstoreSaleTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(BookstoreSaleTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreSaleTableMap::COL_ID, $id, $comparison);

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
                $this->addUsingAlias(BookstoreSaleTableMap::COL_BOOKSTORE_ID, $bookstoreId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($bookstoreId['max'])) {
                $this->addUsingAlias(BookstoreSaleTableMap::COL_BOOKSTORE_ID, $bookstoreId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreSaleTableMap::COL_BOOKSTORE_ID, $bookstoreId, $comparison);

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
                $this->addUsingAlias(BookstoreSaleTableMap::COL_PUBLISHER_ID, $publisherId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($publisherId['max'])) {
                $this->addUsingAlias(BookstoreSaleTableMap::COL_PUBLISHER_ID, $publisherId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreSaleTableMap::COL_PUBLISHER_ID, $publisherId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the sale_name column
     *
     * Example usage:
     * <code>
     * $query->filterBySaleName('fooValue');   // WHERE sale_name = 'fooValue'
     * $query->filterBySaleName('%fooValue%', Criteria::LIKE); // WHERE sale_name LIKE '%fooValue%'
     * $query->filterBySaleName(['foo', 'bar']); // WHERE sale_name IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $saleName The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySaleName($saleName = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($saleName)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreSaleTableMap::COL_SALE_NAME, $saleName, $comparison);

        return $this;
    }

    /**
     * Filter the query on the discount column
     *
     * Example usage:
     * <code>
     * $query->filterByDiscount(1234); // WHERE discount = 1234
     * $query->filterByDiscount(array(12, 34)); // WHERE discount IN (12, 34)
     * $query->filterByDiscount(array('min' => 12)); // WHERE discount > 12
     * </code>
     *
     * @param mixed $discount The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByDiscount($discount = null, ?string $comparison = null)
    {
        if (is_array($discount)) {
            $useMinMax = false;
            if (isset($discount['min'])) {
                $this->addUsingAlias(BookstoreSaleTableMap::COL_DISCOUNT, $discount['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($discount['max'])) {
                $this->addUsingAlias(BookstoreSaleTableMap::COL_DISCOUNT, $discount['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreSaleTableMap::COL_DISCOUNT, $discount, $comparison);

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
                ->addUsingAlias(BookstoreSaleTableMap::COL_BOOKSTORE_ID, $bookstore->getId(), $comparison);
        } elseif ($bookstore instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookstoreSaleTableMap::COL_BOOKSTORE_ID, $bookstore->toKeyValue('PrimaryKey', 'Id'), $comparison);

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
    public function joinBookstore(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
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
    public function useBookstoreQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
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
        ?string $joinType = Criteria::LEFT_JOIN
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
                ->addUsingAlias(BookstoreSaleTableMap::COL_PUBLISHER_ID, $publisher->getId(), $comparison);
        } elseif ($publisher instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookstoreSaleTableMap::COL_PUBLISHER_ID, $publisher->toKeyValue('PrimaryKey', 'Id'), $comparison);

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
     * Exclude object from result
     *
     * @param ChildBookstoreSale $bookstoreSale Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($bookstoreSale = null)
    {
        if ($bookstoreSale) {
            $this->addUsingAlias(BookstoreSaleTableMap::COL_ID, $bookstoreSale->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the bookstore_sale table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreSaleTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BookstoreSaleTableMap::clearInstancePool();
            BookstoreSaleTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreSaleTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(BookstoreSaleTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            BookstoreSaleTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            BookstoreSaleTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
