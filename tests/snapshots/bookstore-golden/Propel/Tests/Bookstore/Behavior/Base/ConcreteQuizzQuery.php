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
use Propel\Tests\Bookstore\Behavior\ConcreteContentQuery as ChildConcreteContentQuery;
use Propel\Tests\Bookstore\Behavior\ConcreteQuizz as ChildConcreteQuizz;
use Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery as ChildConcreteQuizzQuery;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteQuizzTableMap;

/**
 * Base class that represents a query for the `concrete_quizz` table.
 *
 * @method     ChildConcreteQuizzQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildConcreteQuizzQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildConcreteQuizzQuery orderByCategoryId($order = Criteria::ASC) Order by the category_id column
 *
 * @method     ChildConcreteQuizzQuery groupByTitle() Group by the title column
 * @method     ChildConcreteQuizzQuery groupById() Group by the id column
 * @method     ChildConcreteQuizzQuery groupByCategoryId() Group by the category_id column
 *
 * @method     ChildConcreteQuizzQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildConcreteQuizzQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildConcreteQuizzQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildConcreteQuizzQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildConcreteQuizzQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildConcreteQuizzQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildConcreteQuizzQuery leftJoinConcreteCategory($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConcreteCategory relation
 * @method     ChildConcreteQuizzQuery rightJoinConcreteCategory($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConcreteCategory relation
 * @method     ChildConcreteQuizzQuery innerJoinConcreteCategory($relationAlias = null) Adds a INNER JOIN clause to the query using the ConcreteCategory relation
 *
 * @method     ChildConcreteQuizzQuery joinWithConcreteCategory($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ConcreteCategory relation
 *
 * @method     ChildConcreteQuizzQuery leftJoinWithConcreteCategory() Adds a LEFT JOIN clause and with to the query using the ConcreteCategory relation
 * @method     ChildConcreteQuizzQuery rightJoinWithConcreteCategory() Adds a RIGHT JOIN clause and with to the query using the ConcreteCategory relation
 * @method     ChildConcreteQuizzQuery innerJoinWithConcreteCategory() Adds a INNER JOIN clause and with to the query using the ConcreteCategory relation
 *
 * @method     ChildConcreteQuizzQuery leftJoinConcreteQuizzQuestion($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConcreteQuizzQuestion relation
 * @method     ChildConcreteQuizzQuery rightJoinConcreteQuizzQuestion($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConcreteQuizzQuestion relation
 * @method     ChildConcreteQuizzQuery innerJoinConcreteQuizzQuestion($relationAlias = null) Adds a INNER JOIN clause to the query using the ConcreteQuizzQuestion relation
 *
 * @method     ChildConcreteQuizzQuery joinWithConcreteQuizzQuestion($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ConcreteQuizzQuestion relation
 *
 * @method     ChildConcreteQuizzQuery leftJoinWithConcreteQuizzQuestion() Adds a LEFT JOIN clause and with to the query using the ConcreteQuizzQuestion relation
 * @method     ChildConcreteQuizzQuery rightJoinWithConcreteQuizzQuestion() Adds a RIGHT JOIN clause and with to the query using the ConcreteQuizzQuestion relation
 * @method     ChildConcreteQuizzQuery innerJoinWithConcreteQuizzQuestion() Adds a INNER JOIN clause and with to the query using the ConcreteQuizzQuestion relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery|\Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildConcreteQuizz|null findOne(?ConnectionInterface $con = null) Return the first ChildConcreteQuizz matching the query
 * @method     ChildConcreteQuizz findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildConcreteQuizz matching the query, or a new ChildConcreteQuizz object populated from the query conditions when no match is found
 *
 * @method     ChildConcreteQuizz|null findOneByTitle(string $title) Return the first ChildConcreteQuizz filtered by the title column
 * @method     ChildConcreteQuizz|null findOneById(int $id) Return the first ChildConcreteQuizz filtered by the id column
 * @method     ChildConcreteQuizz|null findOneByCategoryId(int $category_id) Return the first ChildConcreteQuizz filtered by the category_id column
 *
 * @method     ChildConcreteQuizz requirePk($key, ?ConnectionInterface $con = null) Return the ChildConcreteQuizz by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizz requireOne(?ConnectionInterface $con = null) Return the first ChildConcreteQuizz matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildConcreteQuizz requireOneByTitle(string $title) Return the first ChildConcreteQuizz filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizz requireOneById(int $id) Return the first ChildConcreteQuizz filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizz requireOneByCategoryId(int $category_id) Return the first ChildConcreteQuizz filtered by the category_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildConcreteQuizz[]|Collection find(?ConnectionInterface $con = null) Return ChildConcreteQuizz objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildConcreteQuizz> find(?ConnectionInterface $con = null) Return ChildConcreteQuizz objects based on current ModelCriteria
 *
 * @method     ChildConcreteQuizz[]|Collection findByTitle(string|array<string> $title) Return ChildConcreteQuizz objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizz> findByTitle(string|array<string> $title) Return ChildConcreteQuizz objects filtered by the title column
 * @method     ChildConcreteQuizz[]|Collection findById(int|array<int> $id) Return ChildConcreteQuizz objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizz> findById(int|array<int> $id) Return ChildConcreteQuizz objects filtered by the id column
 * @method     ChildConcreteQuizz[]|Collection findByCategoryId(int|array<int> $category_id) Return ChildConcreteQuizz objects filtered by the category_id column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizz> findByCategoryId(int|array<int> $category_id) Return ChildConcreteQuizz objects filtered by the category_id column
 *
 * @method     ChildConcreteQuizz[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildConcreteQuizz> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ConcreteQuizzQuery extends ChildConcreteContentQuery
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ConcreteQuizzQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteQuizz', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildConcreteQuizzQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildConcreteQuizzQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildConcreteQuizzQuery) {
            return $criteria;
        }
        $query = new ChildConcreteQuizzQuery();
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
     * @return ChildConcreteQuizz|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ConcreteQuizzTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ConcreteQuizzTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildConcreteQuizz A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT title, id, category_id FROM concrete_quizz WHERE id = :p0';
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
            /** @var ChildConcreteQuizz $obj */
            $obj = new ChildConcreteQuizz();
            $obj->hydrate($row);
            ConcreteQuizzTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildConcreteQuizz|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(ConcreteQuizzTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(ConcreteQuizzTableMap::COL_ID, $keys, Criteria::IN);

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

        $this->addUsingAlias(ConcreteQuizzTableMap::COL_TITLE, $title, $comparison);

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
                $this->addUsingAlias(ConcreteQuizzTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ConcreteQuizzTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzTableMap::COL_ID, $id, $comparison);

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
                $this->addUsingAlias(ConcreteQuizzTableMap::COL_CATEGORY_ID, $categoryId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($categoryId['max'])) {
                $this->addUsingAlias(ConcreteQuizzTableMap::COL_CATEGORY_ID, $categoryId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzTableMap::COL_CATEGORY_ID, $categoryId, $comparison);

        return $this;
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
                ->addUsingAlias(ConcreteQuizzTableMap::COL_CATEGORY_ID, $concreteCategory->getId(), $comparison);
        } elseif ($concreteCategory instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ConcreteQuizzTableMap::COL_CATEGORY_ID, $concreteCategory->toKeyValue('PrimaryKey', 'Id'), $comparison);

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
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestion object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestion|ObjectCollection $concreteQuizzQuestion the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByConcreteQuizzQuestion($concreteQuizzQuestion, ?string $comparison = null)
    {
        if ($concreteQuizzQuestion instanceof \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestion) {
            $this
                ->addUsingAlias(ConcreteQuizzTableMap::COL_ID, $concreteQuizzQuestion->getQuizzId(), $comparison);

            return $this;
        } elseif ($concreteQuizzQuestion instanceof ObjectCollection) {
            $this
                ->useConcreteQuizzQuestionQuery()
                ->filterByPrimaryKeys($concreteQuizzQuestion->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByConcreteQuizzQuestion() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestion or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConcreteQuizzQuestion relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinConcreteQuizzQuestion(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConcreteQuizzQuestion');

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
            $this->addJoinObject($join, 'ConcreteQuizzQuestion');
        }

        return $this;
    }

    /**
     * Use the ConcreteQuizzQuestion relation ConcreteQuizzQuestion object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery A secondary query class using the current class as primary query
     */
    public function useConcreteQuizzQuestionQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinConcreteQuizzQuestion($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConcreteQuizzQuestion', '\Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery');
    }

    /**
     * Use the ConcreteQuizzQuestion relation ConcreteQuizzQuestion object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery):\Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withConcreteQuizzQuestionQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useConcreteQuizzQuestionQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ConcreteQuizzQuestion table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery The inner query object of the EXISTS statement
     */
    public function useConcreteQuizzQuestionExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery */
        $q = $this->useExistsQuery('ConcreteQuizzQuestion', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ConcreteQuizzQuestion table for a NOT EXISTS query.
     *
     * @see useConcreteQuizzQuestionExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery The inner query object of the NOT EXISTS statement
     */
    public function useConcreteQuizzQuestionNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery */
        $q = $this->useExistsQuery('ConcreteQuizzQuestion', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ConcreteQuizzQuestion table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery The inner query object of the IN statement
     */
    public function useInConcreteQuizzQuestionQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery */
        $q = $this->useInQuery('ConcreteQuizzQuestion', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ConcreteQuizzQuestion table for a NOT IN query.
     *
     * @see useConcreteQuizzQuestionInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery The inner query object of the NOT IN statement
     */
    public function useNotInConcreteQuizzQuestionQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery */
        $q = $this->useInQuery('ConcreteQuizzQuestion', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildConcreteQuizz $concreteQuizz Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($concreteQuizz = null)
    {
        if ($concreteQuizz) {
            $this->addUsingAlias(ConcreteQuizzTableMap::COL_ID, $concreteQuizz->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the concrete_quizz table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteQuizzTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ConcreteQuizzTableMap::clearInstancePool();
            ConcreteQuizzTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteQuizzTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ConcreteQuizzTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ConcreteQuizzTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ConcreteQuizzTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
