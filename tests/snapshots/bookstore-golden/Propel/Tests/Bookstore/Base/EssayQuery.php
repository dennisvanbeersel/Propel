<?php

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
use Propel\Tests\Bookstore\Essay as ChildEssay;
use Propel\Tests\Bookstore\EssayQuery as ChildEssayQuery;
use Propel\Tests\Bookstore\Map\EssayTableMap;

/**
 * Base class that represents a query for the `essay` table.
 *
 * @method     ChildEssayQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildEssayQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildEssayQuery orderByFirstAuthorId($order = Criteria::ASC) Order by the first_author_id column
 * @method     ChildEssayQuery orderBySecondAuthorId($order = Criteria::ASC) Order by the second_author_id column
 * @method     ChildEssayQuery orderBySecondTitle($order = Criteria::ASC) Order by the subtitle column
 * @method     ChildEssayQuery orderByNextEssayId($order = Criteria::ASC) Order by the next_essay_id column
 *
 * @method     ChildEssayQuery groupById() Group by the id column
 * @method     ChildEssayQuery groupByTitle() Group by the title column
 * @method     ChildEssayQuery groupByFirstAuthorId() Group by the first_author_id column
 * @method     ChildEssayQuery groupBySecondAuthorId() Group by the second_author_id column
 * @method     ChildEssayQuery groupBySecondTitle() Group by the subtitle column
 * @method     ChildEssayQuery groupByNextEssayId() Group by the next_essay_id column
 *
 * @method     ChildEssayQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildEssayQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildEssayQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildEssayQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildEssayQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildEssayQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildEssayQuery leftJoinFirstAuthor($relationAlias = null) Adds a LEFT JOIN clause to the query using the FirstAuthor relation
 * @method     ChildEssayQuery rightJoinFirstAuthor($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FirstAuthor relation
 * @method     ChildEssayQuery innerJoinFirstAuthor($relationAlias = null) Adds a INNER JOIN clause to the query using the FirstAuthor relation
 *
 * @method     ChildEssayQuery joinWithFirstAuthor($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the FirstAuthor relation
 *
 * @method     ChildEssayQuery leftJoinWithFirstAuthor() Adds a LEFT JOIN clause and with to the query using the FirstAuthor relation
 * @method     ChildEssayQuery rightJoinWithFirstAuthor() Adds a RIGHT JOIN clause and with to the query using the FirstAuthor relation
 * @method     ChildEssayQuery innerJoinWithFirstAuthor() Adds a INNER JOIN clause and with to the query using the FirstAuthor relation
 *
 * @method     ChildEssayQuery leftJoinSecondAuthor($relationAlias = null) Adds a LEFT JOIN clause to the query using the SecondAuthor relation
 * @method     ChildEssayQuery rightJoinSecondAuthor($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SecondAuthor relation
 * @method     ChildEssayQuery innerJoinSecondAuthor($relationAlias = null) Adds a INNER JOIN clause to the query using the SecondAuthor relation
 *
 * @method     ChildEssayQuery joinWithSecondAuthor($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the SecondAuthor relation
 *
 * @method     ChildEssayQuery leftJoinWithSecondAuthor() Adds a LEFT JOIN clause and with to the query using the SecondAuthor relation
 * @method     ChildEssayQuery rightJoinWithSecondAuthor() Adds a RIGHT JOIN clause and with to the query using the SecondAuthor relation
 * @method     ChildEssayQuery innerJoinWithSecondAuthor() Adds a INNER JOIN clause and with to the query using the SecondAuthor relation
 *
 * @method     ChildEssayQuery leftJoinEssayRelatedByNextEssayId($relationAlias = null) Adds a LEFT JOIN clause to the query using the EssayRelatedByNextEssayId relation
 * @method     ChildEssayQuery rightJoinEssayRelatedByNextEssayId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the EssayRelatedByNextEssayId relation
 * @method     ChildEssayQuery innerJoinEssayRelatedByNextEssayId($relationAlias = null) Adds a INNER JOIN clause to the query using the EssayRelatedByNextEssayId relation
 *
 * @method     ChildEssayQuery joinWithEssayRelatedByNextEssayId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the EssayRelatedByNextEssayId relation
 *
 * @method     ChildEssayQuery leftJoinWithEssayRelatedByNextEssayId() Adds a LEFT JOIN clause and with to the query using the EssayRelatedByNextEssayId relation
 * @method     ChildEssayQuery rightJoinWithEssayRelatedByNextEssayId() Adds a RIGHT JOIN clause and with to the query using the EssayRelatedByNextEssayId relation
 * @method     ChildEssayQuery innerJoinWithEssayRelatedByNextEssayId() Adds a INNER JOIN clause and with to the query using the EssayRelatedByNextEssayId relation
 *
 * @method     ChildEssayQuery leftJoinEssayRelatedById($relationAlias = null) Adds a LEFT JOIN clause to the query using the EssayRelatedById relation
 * @method     ChildEssayQuery rightJoinEssayRelatedById($relationAlias = null) Adds a RIGHT JOIN clause to the query using the EssayRelatedById relation
 * @method     ChildEssayQuery innerJoinEssayRelatedById($relationAlias = null) Adds a INNER JOIN clause to the query using the EssayRelatedById relation
 *
 * @method     ChildEssayQuery joinWithEssayRelatedById($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the EssayRelatedById relation
 *
 * @method     ChildEssayQuery leftJoinWithEssayRelatedById() Adds a LEFT JOIN clause and with to the query using the EssayRelatedById relation
 * @method     ChildEssayQuery rightJoinWithEssayRelatedById() Adds a RIGHT JOIN clause and with to the query using the EssayRelatedById relation
 * @method     ChildEssayQuery innerJoinWithEssayRelatedById() Adds a INNER JOIN clause and with to the query using the EssayRelatedById relation
 *
 * @method     \Propel\Tests\Bookstore\AuthorQuery|\Propel\Tests\Bookstore\AuthorQuery|\Propel\Tests\Bookstore\EssayQuery|\Propel\Tests\Bookstore\EssayQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildEssay|null findOne(?ConnectionInterface $con = null) Return the first ChildEssay matching the query
 * @method     ChildEssay findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildEssay matching the query, or a new ChildEssay object populated from the query conditions when no match is found
 *
 * @method     ChildEssay|null findOneById(int $id) Return the first ChildEssay filtered by the id column
 * @method     ChildEssay|null findOneByTitle(string $title) Return the first ChildEssay filtered by the title column
 * @method     ChildEssay|null findOneByFirstAuthorId(int $first_author_id) Return the first ChildEssay filtered by the first_author_id column
 * @method     ChildEssay|null findOneBySecondAuthorId(int $second_author_id) Return the first ChildEssay filtered by the second_author_id column
 * @method     ChildEssay|null findOneBySecondTitle(string $subtitle) Return the first ChildEssay filtered by the subtitle column
 * @method     ChildEssay|null findOneByNextEssayId(int $next_essay_id) Return the first ChildEssay filtered by the next_essay_id column
 *
 * @method     ChildEssay requirePk($key, ?ConnectionInterface $con = null) Return the ChildEssay by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildEssay requireOne(?ConnectionInterface $con = null) Return the first ChildEssay matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildEssay requireOneById(int $id) Return the first ChildEssay filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildEssay requireOneByTitle(string $title) Return the first ChildEssay filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildEssay requireOneByFirstAuthorId(int $first_author_id) Return the first ChildEssay filtered by the first_author_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildEssay requireOneBySecondAuthorId(int $second_author_id) Return the first ChildEssay filtered by the second_author_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildEssay requireOneBySecondTitle(string $subtitle) Return the first ChildEssay filtered by the subtitle column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildEssay requireOneByNextEssayId(int $next_essay_id) Return the first ChildEssay filtered by the next_essay_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildEssay[]|Collection find(?ConnectionInterface $con = null) Return ChildEssay objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildEssay> find(?ConnectionInterface $con = null) Return ChildEssay objects based on current ModelCriteria
 *
 * @method     ChildEssay[]|Collection findById(int|array<int> $id) Return ChildEssay objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildEssay> findById(int|array<int> $id) Return ChildEssay objects filtered by the id column
 * @method     ChildEssay[]|Collection findByTitle(string|array<string> $title) Return ChildEssay objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildEssay> findByTitle(string|array<string> $title) Return ChildEssay objects filtered by the title column
 * @method     ChildEssay[]|Collection findByFirstAuthorId(int|array<int> $first_author_id) Return ChildEssay objects filtered by the first_author_id column
 * @psalm-method Collection&\Traversable<ChildEssay> findByFirstAuthorId(int|array<int> $first_author_id) Return ChildEssay objects filtered by the first_author_id column
 * @method     ChildEssay[]|Collection findBySecondAuthorId(int|array<int> $second_author_id) Return ChildEssay objects filtered by the second_author_id column
 * @psalm-method Collection&\Traversable<ChildEssay> findBySecondAuthorId(int|array<int> $second_author_id) Return ChildEssay objects filtered by the second_author_id column
 * @method     ChildEssay[]|Collection findBySecondTitle(string|array<string> $subtitle) Return ChildEssay objects filtered by the subtitle column
 * @psalm-method Collection&\Traversable<ChildEssay> findBySecondTitle(string|array<string> $subtitle) Return ChildEssay objects filtered by the subtitle column
 * @method     ChildEssay[]|Collection findByNextEssayId(int|array<int> $next_essay_id) Return ChildEssay objects filtered by the next_essay_id column
 * @psalm-method Collection&\Traversable<ChildEssay> findByNextEssayId(int|array<int> $next_essay_id) Return ChildEssay objects filtered by the next_essay_id column
 *
 * @method     ChildEssay[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildEssay> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class EssayQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\EssayQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\Essay', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildEssayQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildEssayQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildEssayQuery) {
            return $criteria;
        }
        $query = new ChildEssayQuery();
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
     * @return ChildEssay|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(EssayTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = EssayTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildEssay A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, first_author_id, second_author_id, subtitle, next_essay_id FROM essay WHERE id = :p0';
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
            /** @var ChildEssay $obj */
            $obj = new ChildEssay();
            $obj->hydrate($row);
            EssayTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildEssay|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(EssayTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(EssayTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(EssayTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(EssayTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(EssayTableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(EssayTableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the first_author_id column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstAuthorId(1234); // WHERE first_author_id = 1234
     * $query->filterByFirstAuthorId(array(12, 34)); // WHERE first_author_id IN (12, 34)
     * $query->filterByFirstAuthorId(array('min' => 12)); // WHERE first_author_id > 12
     * </code>
     *
     * @see       filterByFirstAuthor()
     *
     * @param mixed $firstAuthorId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFirstAuthorId($firstAuthorId = null, ?string $comparison = null)
    {
        if (is_array($firstAuthorId)) {
            $useMinMax = false;
            if (isset($firstAuthorId['min'])) {
                $this->addUsingAlias(EssayTableMap::COL_FIRST_AUTHOR_ID, $firstAuthorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($firstAuthorId['max'])) {
                $this->addUsingAlias(EssayTableMap::COL_FIRST_AUTHOR_ID, $firstAuthorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(EssayTableMap::COL_FIRST_AUTHOR_ID, $firstAuthorId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the second_author_id column
     *
     * Example usage:
     * <code>
     * $query->filterBySecondAuthorId(1234); // WHERE second_author_id = 1234
     * $query->filterBySecondAuthorId(array(12, 34)); // WHERE second_author_id IN (12, 34)
     * $query->filterBySecondAuthorId(array('min' => 12)); // WHERE second_author_id > 12
     * </code>
     *
     * @see       filterBySecondAuthor()
     *
     * @param mixed $secondAuthorId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySecondAuthorId($secondAuthorId = null, ?string $comparison = null)
    {
        if (is_array($secondAuthorId)) {
            $useMinMax = false;
            if (isset($secondAuthorId['min'])) {
                $this->addUsingAlias(EssayTableMap::COL_SECOND_AUTHOR_ID, $secondAuthorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($secondAuthorId['max'])) {
                $this->addUsingAlias(EssayTableMap::COL_SECOND_AUTHOR_ID, $secondAuthorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(EssayTableMap::COL_SECOND_AUTHOR_ID, $secondAuthorId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the subtitle column
     *
     * Example usage:
     * <code>
     * $query->filterBySecondTitle('fooValue');   // WHERE subtitle = 'fooValue'
     * $query->filterBySecondTitle('%fooValue%', Criteria::LIKE); // WHERE subtitle LIKE '%fooValue%'
     * $query->filterBySecondTitle(['foo', 'bar']); // WHERE subtitle IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $secondTitle The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySecondTitle($secondTitle = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($secondTitle)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(EssayTableMap::COL_SUBTITLE, $secondTitle, $comparison);

        return $this;
    }

    /**
     * Filter the query on the next_essay_id column
     *
     * Example usage:
     * <code>
     * $query->filterByNextEssayId(1234); // WHERE next_essay_id = 1234
     * $query->filterByNextEssayId(array(12, 34)); // WHERE next_essay_id IN (12, 34)
     * $query->filterByNextEssayId(array('min' => 12)); // WHERE next_essay_id > 12
     * </code>
     *
     * @see       filterByEssayRelatedByNextEssayId()
     *
     * @param mixed $nextEssayId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByNextEssayId($nextEssayId = null, ?string $comparison = null)
    {
        if (is_array($nextEssayId)) {
            $useMinMax = false;
            if (isset($nextEssayId['min'])) {
                $this->addUsingAlias(EssayTableMap::COL_NEXT_ESSAY_ID, $nextEssayId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($nextEssayId['max'])) {
                $this->addUsingAlias(EssayTableMap::COL_NEXT_ESSAY_ID, $nextEssayId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(EssayTableMap::COL_NEXT_ESSAY_ID, $nextEssayId, $comparison);

        return $this;
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
    public function filterByFirstAuthor($author, ?string $comparison = null)
    {
        if ($author instanceof \Propel\Tests\Bookstore\Author) {
            return $this
                ->addUsingAlias(EssayTableMap::COL_FIRST_AUTHOR_ID, $author->getId(), $comparison);
        } elseif ($author instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(EssayTableMap::COL_FIRST_AUTHOR_ID, $author->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByFirstAuthor() only accepts arguments of type \Propel\Tests\Bookstore\Author or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FirstAuthor relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinFirstAuthor(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FirstAuthor');

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
            $this->addJoinObject($join, 'FirstAuthor');
        }

        return $this;
    }

    /**
     * Use the FirstAuthor relation Author object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery A secondary query class using the current class as primary query
     */
    public function useFirstAuthorQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFirstAuthor($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FirstAuthor', '\Propel\Tests\Bookstore\AuthorQuery');
    }

    /**
     * Use the FirstAuthor relation Author object
     *
     * @param callable(\Propel\Tests\Bookstore\AuthorQuery):\Propel\Tests\Bookstore\AuthorQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withFirstAuthorQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useFirstAuthorQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the FirstAuthor relation to the Author table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the EXISTS statement
     */
    public function useFirstAuthorExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useExistsQuery('FirstAuthor', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the FirstAuthor relation to the Author table for a NOT EXISTS query.
     *
     * @see useFirstAuthorExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the NOT EXISTS statement
     */
    public function useFirstAuthorNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useExistsQuery('FirstAuthor', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the FirstAuthor relation to the Author table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the IN statement
     */
    public function useInFirstAuthorQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useInQuery('FirstAuthor', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the FirstAuthor relation to the Author table for a NOT IN query.
     *
     * @see useFirstAuthorInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the NOT IN statement
     */
    public function useNotInFirstAuthorQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useInQuery('FirstAuthor', $modelAlias, $queryClass, 'NOT IN');
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
    public function filterBySecondAuthor($author, ?string $comparison = null)
    {
        if ($author instanceof \Propel\Tests\Bookstore\Author) {
            return $this
                ->addUsingAlias(EssayTableMap::COL_SECOND_AUTHOR_ID, $author->getId(), $comparison);
        } elseif ($author instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(EssayTableMap::COL_SECOND_AUTHOR_ID, $author->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterBySecondAuthor() only accepts arguments of type \Propel\Tests\Bookstore\Author or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SecondAuthor relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinSecondAuthor(?string $relationAlias = null, ?string $joinType = 'INNER JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SecondAuthor');

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
            $this->addJoinObject($join, 'SecondAuthor');
        }

        return $this;
    }

    /**
     * Use the SecondAuthor relation Author object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery A secondary query class using the current class as primary query
     */
    public function useSecondAuthorQuery(?string $relationAlias = null, string $joinType = 'INNER JOIN')
    {
        return $this
            ->joinSecondAuthor($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SecondAuthor', '\Propel\Tests\Bookstore\AuthorQuery');
    }

    /**
     * Use the SecondAuthor relation Author object
     *
     * @param callable(\Propel\Tests\Bookstore\AuthorQuery):\Propel\Tests\Bookstore\AuthorQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withSecondAuthorQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = 'INNER JOIN'
    ) {
        $relatedQuery = $this->useSecondAuthorQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the SecondAuthor relation to the Author table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the EXISTS statement
     */
    public function useSecondAuthorExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useExistsQuery('SecondAuthor', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the SecondAuthor relation to the Author table for a NOT EXISTS query.
     *
     * @see useSecondAuthorExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the NOT EXISTS statement
     */
    public function useSecondAuthorNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useExistsQuery('SecondAuthor', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the SecondAuthor relation to the Author table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the IN statement
     */
    public function useInSecondAuthorQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useInQuery('SecondAuthor', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the SecondAuthor relation to the Author table for a NOT IN query.
     *
     * @see useSecondAuthorInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AuthorQuery The inner query object of the NOT IN statement
     */
    public function useNotInSecondAuthorQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AuthorQuery */
        $q = $this->useInQuery('SecondAuthor', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Essay object
     *
     * @param \Propel\Tests\Bookstore\Essay|ObjectCollection $essay The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByEssayRelatedByNextEssayId($essay, ?string $comparison = null)
    {
        if ($essay instanceof \Propel\Tests\Bookstore\Essay) {
            return $this
                ->addUsingAlias(EssayTableMap::COL_NEXT_ESSAY_ID, $essay->getId(), $comparison);
        } elseif ($essay instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(EssayTableMap::COL_NEXT_ESSAY_ID, $essay->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByEssayRelatedByNextEssayId() only accepts arguments of type \Propel\Tests\Bookstore\Essay or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the EssayRelatedByNextEssayId relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinEssayRelatedByNextEssayId(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('EssayRelatedByNextEssayId');

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
            $this->addJoinObject($join, 'EssayRelatedByNextEssayId');
        }

        return $this;
    }

    /**
     * Use the EssayRelatedByNextEssayId relation Essay object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\EssayQuery A secondary query class using the current class as primary query
     */
    public function useEssayRelatedByNextEssayIdQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinEssayRelatedByNextEssayId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'EssayRelatedByNextEssayId', '\Propel\Tests\Bookstore\EssayQuery');
    }

    /**
     * Use the EssayRelatedByNextEssayId relation Essay object
     *
     * @param callable(\Propel\Tests\Bookstore\EssayQuery):\Propel\Tests\Bookstore\EssayQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withEssayRelatedByNextEssayIdQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useEssayRelatedByNextEssayIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the EssayRelatedByNextEssayId relation to the Essay table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the EXISTS statement
     */
    public function useEssayRelatedByNextEssayIdExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useExistsQuery('EssayRelatedByNextEssayId', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the EssayRelatedByNextEssayId relation to the Essay table for a NOT EXISTS query.
     *
     * @see useEssayRelatedByNextEssayIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the NOT EXISTS statement
     */
    public function useEssayRelatedByNextEssayIdNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useExistsQuery('EssayRelatedByNextEssayId', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the EssayRelatedByNextEssayId relation to the Essay table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the IN statement
     */
    public function useInEssayRelatedByNextEssayIdQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useInQuery('EssayRelatedByNextEssayId', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the EssayRelatedByNextEssayId relation to the Essay table for a NOT IN query.
     *
     * @see useEssayRelatedByNextEssayIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the NOT IN statement
     */
    public function useNotInEssayRelatedByNextEssayIdQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useInQuery('EssayRelatedByNextEssayId', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Essay object
     *
     * @param \Propel\Tests\Bookstore\Essay|ObjectCollection $essay the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByEssayRelatedById($essay, ?string $comparison = null)
    {
        if ($essay instanceof \Propel\Tests\Bookstore\Essay) {
            $this
                ->addUsingAlias(EssayTableMap::COL_ID, $essay->getNextEssayId(), $comparison);

            return $this;
        } elseif ($essay instanceof ObjectCollection) {
            $this
                ->useEssayRelatedByIdQuery()
                ->filterByPrimaryKeys($essay->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByEssayRelatedById() only accepts arguments of type \Propel\Tests\Bookstore\Essay or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the EssayRelatedById relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinEssayRelatedById(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('EssayRelatedById');

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
            $this->addJoinObject($join, 'EssayRelatedById');
        }

        return $this;
    }

    /**
     * Use the EssayRelatedById relation Essay object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\EssayQuery A secondary query class using the current class as primary query
     */
    public function useEssayRelatedByIdQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinEssayRelatedById($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'EssayRelatedById', '\Propel\Tests\Bookstore\EssayQuery');
    }

    /**
     * Use the EssayRelatedById relation Essay object
     *
     * @param callable(\Propel\Tests\Bookstore\EssayQuery):\Propel\Tests\Bookstore\EssayQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withEssayRelatedByIdQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useEssayRelatedByIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the EssayRelatedById relation to the Essay table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the EXISTS statement
     */
    public function useEssayRelatedByIdExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useExistsQuery('EssayRelatedById', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the EssayRelatedById relation to the Essay table for a NOT EXISTS query.
     *
     * @see useEssayRelatedByIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the NOT EXISTS statement
     */
    public function useEssayRelatedByIdNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useExistsQuery('EssayRelatedById', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the EssayRelatedById relation to the Essay table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the IN statement
     */
    public function useInEssayRelatedByIdQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useInQuery('EssayRelatedById', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the EssayRelatedById relation to the Essay table for a NOT IN query.
     *
     * @see useEssayRelatedByIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\EssayQuery The inner query object of the NOT IN statement
     */
    public function useNotInEssayRelatedByIdQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\EssayQuery */
        $q = $this->useInQuery('EssayRelatedById', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildEssay $essay Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($essay = null)
    {
        if ($essay) {
            $this->addUsingAlias(EssayTableMap::COL_ID, $essay->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the essay table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(EssayTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            EssayTableMap::clearInstancePool();
            EssayTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(EssayTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(EssayTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            EssayTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            EssayTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
