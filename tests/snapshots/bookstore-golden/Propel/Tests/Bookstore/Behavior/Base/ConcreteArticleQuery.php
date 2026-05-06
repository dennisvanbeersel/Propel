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
use Propel\Tests\Bookstore\Behavior\ConcreteArticle as ChildConcreteArticle;
use Propel\Tests\Bookstore\Behavior\ConcreteArticleQuery as ChildConcreteArticleQuery;
use Propel\Tests\Bookstore\Behavior\ConcreteContentQuery as ChildConcreteContentQuery;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteArticleTableMap;

/**
 * Base class that represents a query for the `concrete_article` table.
 *
 * @method     ChildConcreteArticleQuery orderByBody($order = Criteria::ASC) Order by the body column
 * @method     ChildConcreteArticleQuery orderByAuthorId($order = Criteria::ASC) Order by the author_id column
 * @method     ChildConcreteArticleQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildConcreteArticleQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildConcreteArticleQuery orderByCategoryId($order = Criteria::ASC) Order by the category_id column
 * @method     ChildConcreteArticleQuery orderByDescendantClass($order = Criteria::ASC) Order by the descendant_class column
 *
 * @method     ChildConcreteArticleQuery groupByBody() Group by the body column
 * @method     ChildConcreteArticleQuery groupByAuthorId() Group by the author_id column
 * @method     ChildConcreteArticleQuery groupById() Group by the id column
 * @method     ChildConcreteArticleQuery groupByTitle() Group by the title column
 * @method     ChildConcreteArticleQuery groupByCategoryId() Group by the category_id column
 * @method     ChildConcreteArticleQuery groupByDescendantClass() Group by the descendant_class column
 *
 * @method     ChildConcreteArticleQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildConcreteArticleQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildConcreteArticleQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildConcreteArticleQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildConcreteArticleQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildConcreteArticleQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildConcreteArticleQuery leftJoinConcreteAuthor($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConcreteAuthor relation
 * @method     ChildConcreteArticleQuery rightJoinConcreteAuthor($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConcreteAuthor relation
 * @method     ChildConcreteArticleQuery innerJoinConcreteAuthor($relationAlias = null) Adds a INNER JOIN clause to the query using the ConcreteAuthor relation
 *
 * @method     ChildConcreteArticleQuery joinWithConcreteAuthor($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ConcreteAuthor relation
 *
 * @method     ChildConcreteArticleQuery leftJoinWithConcreteAuthor() Adds a LEFT JOIN clause and with to the query using the ConcreteAuthor relation
 * @method     ChildConcreteArticleQuery rightJoinWithConcreteAuthor() Adds a RIGHT JOIN clause and with to the query using the ConcreteAuthor relation
 * @method     ChildConcreteArticleQuery innerJoinWithConcreteAuthor() Adds a INNER JOIN clause and with to the query using the ConcreteAuthor relation
 *
 * @method     ChildConcreteArticleQuery leftJoinConcreteContent($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConcreteContent relation
 * @method     ChildConcreteArticleQuery rightJoinConcreteContent($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConcreteContent relation
 * @method     ChildConcreteArticleQuery innerJoinConcreteContent($relationAlias = null) Adds a INNER JOIN clause to the query using the ConcreteContent relation
 *
 * @method     ChildConcreteArticleQuery joinWithConcreteContent($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ConcreteContent relation
 *
 * @method     ChildConcreteArticleQuery leftJoinWithConcreteContent() Adds a LEFT JOIN clause and with to the query using the ConcreteContent relation
 * @method     ChildConcreteArticleQuery rightJoinWithConcreteContent() Adds a RIGHT JOIN clause and with to the query using the ConcreteContent relation
 * @method     ChildConcreteArticleQuery innerJoinWithConcreteContent() Adds a INNER JOIN clause and with to the query using the ConcreteContent relation
 *
 * @method     ChildConcreteArticleQuery leftJoinConcreteCategory($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConcreteCategory relation
 * @method     ChildConcreteArticleQuery rightJoinConcreteCategory($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConcreteCategory relation
 * @method     ChildConcreteArticleQuery innerJoinConcreteCategory($relationAlias = null) Adds a INNER JOIN clause to the query using the ConcreteCategory relation
 *
 * @method     ChildConcreteArticleQuery joinWithConcreteCategory($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ConcreteCategory relation
 *
 * @method     ChildConcreteArticleQuery leftJoinWithConcreteCategory() Adds a LEFT JOIN clause and with to the query using the ConcreteCategory relation
 * @method     ChildConcreteArticleQuery rightJoinWithConcreteCategory() Adds a RIGHT JOIN clause and with to the query using the ConcreteCategory relation
 * @method     ChildConcreteArticleQuery innerJoinWithConcreteCategory() Adds a INNER JOIN clause and with to the query using the ConcreteCategory relation
 *
 * @method     ChildConcreteArticleQuery leftJoinConcreteNews($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConcreteNews relation
 * @method     ChildConcreteArticleQuery rightJoinConcreteNews($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConcreteNews relation
 * @method     ChildConcreteArticleQuery innerJoinConcreteNews($relationAlias = null) Adds a INNER JOIN clause to the query using the ConcreteNews relation
 *
 * @method     ChildConcreteArticleQuery joinWithConcreteNews($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ConcreteNews relation
 *
 * @method     ChildConcreteArticleQuery leftJoinWithConcreteNews() Adds a LEFT JOIN clause and with to the query using the ConcreteNews relation
 * @method     ChildConcreteArticleQuery rightJoinWithConcreteNews() Adds a RIGHT JOIN clause and with to the query using the ConcreteNews relation
 * @method     ChildConcreteArticleQuery innerJoinWithConcreteNews() Adds a INNER JOIN clause and with to the query using the ConcreteNews relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery|\Propel\Tests\Bookstore\Behavior\ConcreteContentQuery|\Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery|\Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildConcreteArticle|null findOne(?ConnectionInterface $con = null) Return the first ChildConcreteArticle matching the query
 * @method     ChildConcreteArticle findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildConcreteArticle matching the query, or a new ChildConcreteArticle object populated from the query conditions when no match is found
 *
 * @method     ChildConcreteArticle|null findOneByBody(string $body) Return the first ChildConcreteArticle filtered by the body column
 * @method     ChildConcreteArticle|null findOneByAuthorId(int $author_id) Return the first ChildConcreteArticle filtered by the author_id column
 * @method     ChildConcreteArticle|null findOneById(int $id) Return the first ChildConcreteArticle filtered by the id column
 * @method     ChildConcreteArticle|null findOneByTitle(string $title) Return the first ChildConcreteArticle filtered by the title column
 * @method     ChildConcreteArticle|null findOneByCategoryId(int $category_id) Return the first ChildConcreteArticle filtered by the category_id column
 * @method     ChildConcreteArticle|null findOneByDescendantClass(string $descendant_class) Return the first ChildConcreteArticle filtered by the descendant_class column
 *
 * @method     ChildConcreteArticle requirePk($key, ?ConnectionInterface $con = null) Return the ChildConcreteArticle by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteArticle requireOne(?ConnectionInterface $con = null) Return the first ChildConcreteArticle matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildConcreteArticle requireOneByBody(string $body) Return the first ChildConcreteArticle filtered by the body column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteArticle requireOneByAuthorId(int $author_id) Return the first ChildConcreteArticle filtered by the author_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteArticle requireOneById(int $id) Return the first ChildConcreteArticle filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteArticle requireOneByTitle(string $title) Return the first ChildConcreteArticle filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteArticle requireOneByCategoryId(int $category_id) Return the first ChildConcreteArticle filtered by the category_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteArticle requireOneByDescendantClass(string $descendant_class) Return the first ChildConcreteArticle filtered by the descendant_class column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildConcreteArticle[]|Collection find(?ConnectionInterface $con = null) Return ChildConcreteArticle objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildConcreteArticle> find(?ConnectionInterface $con = null) Return ChildConcreteArticle objects based on current ModelCriteria
 *
 * @method     ChildConcreteArticle[]|Collection findByBody(string|array<string> $body) Return ChildConcreteArticle objects filtered by the body column
 * @psalm-method Collection&\Traversable<ChildConcreteArticle> findByBody(string|array<string> $body) Return ChildConcreteArticle objects filtered by the body column
 * @method     ChildConcreteArticle[]|Collection findByAuthorId(int|array<int> $author_id) Return ChildConcreteArticle objects filtered by the author_id column
 * @psalm-method Collection&\Traversable<ChildConcreteArticle> findByAuthorId(int|array<int> $author_id) Return ChildConcreteArticle objects filtered by the author_id column
 * @method     ChildConcreteArticle[]|Collection findById(int|array<int> $id) Return ChildConcreteArticle objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildConcreteArticle> findById(int|array<int> $id) Return ChildConcreteArticle objects filtered by the id column
 * @method     ChildConcreteArticle[]|Collection findByTitle(string|array<string> $title) Return ChildConcreteArticle objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildConcreteArticle> findByTitle(string|array<string> $title) Return ChildConcreteArticle objects filtered by the title column
 * @method     ChildConcreteArticle[]|Collection findByCategoryId(int|array<int> $category_id) Return ChildConcreteArticle objects filtered by the category_id column
 * @psalm-method Collection&\Traversable<ChildConcreteArticle> findByCategoryId(int|array<int> $category_id) Return ChildConcreteArticle objects filtered by the category_id column
 * @method     ChildConcreteArticle[]|Collection findByDescendantClass(string|array<string> $descendant_class) Return ChildConcreteArticle objects filtered by the descendant_class column
 * @psalm-method Collection&\Traversable<ChildConcreteArticle> findByDescendantClass(string|array<string> $descendant_class) Return ChildConcreteArticle objects filtered by the descendant_class column
 *
 * @method     ChildConcreteArticle[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildConcreteArticle> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ConcreteArticleQuery extends ChildConcreteContentQuery
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ConcreteArticleQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteArticle', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildConcreteArticleQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildConcreteArticleQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildConcreteArticleQuery) {
            return $criteria;
        }
        $query = new ChildConcreteArticleQuery();
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
     * @return ChildConcreteArticle|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ConcreteArticleTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ConcreteArticleTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildConcreteArticle A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT body, author_id, id, title, category_id, descendant_class FROM concrete_article WHERE id = :p0';
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
            /** @var ChildConcreteArticle $obj */
            $obj = new ChildConcreteArticle();
            $obj->hydrate($row);
            ConcreteArticleTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildConcreteArticle|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(ConcreteArticleTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(ConcreteArticleTableMap::COL_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the body column
     *
     * Example usage:
     * <code>
     * $query->filterByBody('fooValue');   // WHERE body = 'fooValue'
     * $query->filterByBody('%fooValue%', Criteria::LIKE); // WHERE body LIKE '%fooValue%'
     * $query->filterByBody(['foo', 'bar']); // WHERE body IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $body The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBody($body = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($body)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteArticleTableMap::COL_BODY, $body, $comparison);

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
     * @see       filterByConcreteAuthor()
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
                $this->addUsingAlias(ConcreteArticleTableMap::COL_AUTHOR_ID, $authorId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($authorId['max'])) {
                $this->addUsingAlias(ConcreteArticleTableMap::COL_AUTHOR_ID, $authorId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteArticleTableMap::COL_AUTHOR_ID, $authorId, $comparison);

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
     * @see       filterByConcreteContent()
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
                $this->addUsingAlias(ConcreteArticleTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ConcreteArticleTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteArticleTableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(ConcreteArticleTableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the category_id column
     *
     * Example usage:
     * <code>
     * $query->filterByCategoryId(1234); // WHERE category_id = 1234
     * $query->filterByCategoryId(array(12, 34)); // WHERE category_id IN (12, 34)
     * $query->filterByCategoryId(array('min' => 12)); // WHERE category_id > 12
     * </code>
     *
     * @see       filterByConcreteCategory()
     *
     * @param mixed $categoryId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCategoryId($categoryId = null, ?string $comparison = null)
    {
        if (is_array($categoryId)) {
            $useMinMax = false;
            if (isset($categoryId['min'])) {
                $this->addUsingAlias(ConcreteArticleTableMap::COL_CATEGORY_ID, $categoryId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($categoryId['max'])) {
                $this->addUsingAlias(ConcreteArticleTableMap::COL_CATEGORY_ID, $categoryId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteArticleTableMap::COL_CATEGORY_ID, $categoryId, $comparison);

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

        $this->addUsingAlias(ConcreteArticleTableMap::COL_DESCENDANT_CLASS, $descendantClass, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ConcreteAuthor object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ConcreteAuthor|ObjectCollection $concreteAuthor The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByConcreteAuthor($concreteAuthor, ?string $comparison = null)
    {
        if ($concreteAuthor instanceof \Propel\Tests\Bookstore\Behavior\ConcreteAuthor) {
            return $this
                ->addUsingAlias(ConcreteArticleTableMap::COL_AUTHOR_ID, $concreteAuthor->getId(), $comparison);
        } elseif ($concreteAuthor instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ConcreteArticleTableMap::COL_AUTHOR_ID, $concreteAuthor->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByConcreteAuthor() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ConcreteAuthor or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConcreteAuthor relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinConcreteAuthor(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConcreteAuthor');

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
            $this->addJoinObject($join, 'ConcreteAuthor');
        }

        return $this;
    }

    /**
     * Use the ConcreteAuthor relation ConcreteAuthor object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery A secondary query class using the current class as primary query
     */
    public function useConcreteAuthorQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinConcreteAuthor($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConcreteAuthor', '\Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery');
    }

    /**
     * Use the ConcreteAuthor relation ConcreteAuthor object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery):\Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withConcreteAuthorQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useConcreteAuthorQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ConcreteAuthor table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery The inner query object of the EXISTS statement
     */
    public function useConcreteAuthorExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery */
        $q = $this->useExistsQuery('ConcreteAuthor', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ConcreteAuthor table for a NOT EXISTS query.
     *
     * @see useConcreteAuthorExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery The inner query object of the NOT EXISTS statement
     */
    public function useConcreteAuthorNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery */
        $q = $this->useExistsQuery('ConcreteAuthor', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ConcreteAuthor table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery The inner query object of the IN statement
     */
    public function useInConcreteAuthorQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery */
        $q = $this->useInQuery('ConcreteAuthor', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ConcreteAuthor table for a NOT IN query.
     *
     * @see useConcreteAuthorInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery The inner query object of the NOT IN statement
     */
    public function useNotInConcreteAuthorQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteAuthorQuery */
        $q = $this->useInQuery('ConcreteAuthor', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ConcreteContent object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ConcreteContent|ObjectCollection $concreteContent The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByConcreteContent($concreteContent, ?string $comparison = null)
    {
        if ($concreteContent instanceof \Propel\Tests\Bookstore\Behavior\ConcreteContent) {
            return $this
                ->addUsingAlias(ConcreteArticleTableMap::COL_ID, $concreteContent->getId(), $comparison);
        } elseif ($concreteContent instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ConcreteArticleTableMap::COL_ID, $concreteContent->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByConcreteContent() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ConcreteContent or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConcreteContent relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinConcreteContent(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConcreteContent');

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
            $this->addJoinObject($join, 'ConcreteContent');
        }

        return $this;
    }

    /**
     * Use the ConcreteContent relation ConcreteContent object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery A secondary query class using the current class as primary query
     */
    public function useConcreteContentQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinConcreteContent($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConcreteContent', '\Propel\Tests\Bookstore\Behavior\ConcreteContentQuery');
    }

    /**
     * Use the ConcreteContent relation ConcreteContent object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ConcreteContentQuery):\Propel\Tests\Bookstore\Behavior\ConcreteContentQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withConcreteContentQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useConcreteContentQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ConcreteContent table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery The inner query object of the EXISTS statement
     */
    public function useConcreteContentExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery */
        $q = $this->useExistsQuery('ConcreteContent', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ConcreteContent table for a NOT EXISTS query.
     *
     * @see useConcreteContentExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery The inner query object of the NOT EXISTS statement
     */
    public function useConcreteContentNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery */
        $q = $this->useExistsQuery('ConcreteContent', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ConcreteContent table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery The inner query object of the IN statement
     */
    public function useInConcreteContentQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery */
        $q = $this->useInQuery('ConcreteContent', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ConcreteContent table for a NOT IN query.
     *
     * @see useConcreteContentInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery The inner query object of the NOT IN statement
     */
    public function useNotInConcreteContentQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery */
        $q = $this->useInQuery('ConcreteContent', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ConcreteCategory object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ConcreteCategory|ObjectCollection $concreteCategory The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByConcreteCategory($concreteCategory, ?string $comparison = null)
    {
        if ($concreteCategory instanceof \Propel\Tests\Bookstore\Behavior\ConcreteCategory) {
            return $this
                ->addUsingAlias(ConcreteArticleTableMap::COL_CATEGORY_ID, $concreteCategory->getId(), $comparison);
        } elseif ($concreteCategory instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ConcreteArticleTableMap::COL_CATEGORY_ID, $concreteCategory->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByConcreteCategory() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ConcreteCategory or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConcreteCategory relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinConcreteCategory(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConcreteCategory');

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
            $this->addJoinObject($join, 'ConcreteCategory');
        }

        return $this;
    }

    /**
     * Use the ConcreteCategory relation ConcreteCategory object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery A secondary query class using the current class as primary query
     */
    public function useConcreteCategoryQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinConcreteCategory($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConcreteCategory', '\Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery');
    }

    /**
     * Use the ConcreteCategory relation ConcreteCategory object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery):\Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withConcreteCategoryQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useConcreteCategoryQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ConcreteCategory table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery The inner query object of the EXISTS statement
     */
    public function useConcreteCategoryExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery */
        $q = $this->useExistsQuery('ConcreteCategory', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ConcreteCategory table for a NOT EXISTS query.
     *
     * @see useConcreteCategoryExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery The inner query object of the NOT EXISTS statement
     */
    public function useConcreteCategoryNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery */
        $q = $this->useExistsQuery('ConcreteCategory', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ConcreteCategory table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery The inner query object of the IN statement
     */
    public function useInConcreteCategoryQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery */
        $q = $this->useInQuery('ConcreteCategory', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ConcreteCategory table for a NOT IN query.
     *
     * @see useConcreteCategoryInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery The inner query object of the NOT IN statement
     */
    public function useNotInConcreteCategoryQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery */
        $q = $this->useInQuery('ConcreteCategory', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ConcreteNews object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ConcreteNews|ObjectCollection $concreteNews the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByConcreteNews($concreteNews, ?string $comparison = null)
    {
        if ($concreteNews instanceof \Propel\Tests\Bookstore\Behavior\ConcreteNews) {
            $this
                ->addUsingAlias(ConcreteArticleTableMap::COL_ID, $concreteNews->getId(), $comparison);

            return $this;
        } elseif ($concreteNews instanceof ObjectCollection) {
            $this
                ->useConcreteNewsQuery()
                ->filterByPrimaryKeys($concreteNews->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByConcreteNews() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ConcreteNews or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConcreteNews relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinConcreteNews(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConcreteNews');

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
            $this->addJoinObject($join, 'ConcreteNews');
        }

        return $this;
    }

    /**
     * Use the ConcreteNews relation ConcreteNews object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery A secondary query class using the current class as primary query
     */
    public function useConcreteNewsQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinConcreteNews($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConcreteNews', '\Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery');
    }

    /**
     * Use the ConcreteNews relation ConcreteNews object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery):\Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withConcreteNewsQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useConcreteNewsQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ConcreteNews table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery The inner query object of the EXISTS statement
     */
    public function useConcreteNewsExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery */
        $q = $this->useExistsQuery('ConcreteNews', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ConcreteNews table for a NOT EXISTS query.
     *
     * @see useConcreteNewsExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery The inner query object of the NOT EXISTS statement
     */
    public function useConcreteNewsNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery */
        $q = $this->useExistsQuery('ConcreteNews', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ConcreteNews table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery The inner query object of the IN statement
     */
    public function useInConcreteNewsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery */
        $q = $this->useInQuery('ConcreteNews', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ConcreteNews table for a NOT IN query.
     *
     * @see useConcreteNewsInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery The inner query object of the NOT IN statement
     */
    public function useNotInConcreteNewsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery */
        $q = $this->useInQuery('ConcreteNews', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildConcreteArticle $concreteArticle Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($concreteArticle = null)
    {
        if ($concreteArticle) {
            $this->addUsingAlias(ConcreteArticleTableMap::COL_ID, $concreteArticle->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the concrete_article table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteArticleTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ConcreteArticleTableMap::clearInstancePool();
            ConcreteArticleTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteArticleTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ConcreteArticleTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ConcreteArticleTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ConcreteArticleTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
