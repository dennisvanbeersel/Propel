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
use Propel\Tests\Bookstore\TypeObject as ChildTypeObject;
use Propel\Tests\Bookstore\TypeObjectQuery as ChildTypeObjectQuery;
use Propel\Tests\Bookstore\Map\TypeObjectTableMap;

/**
 * Base class that represents a query for the `type_object` table.
 *
 * @method     ChildTypeObjectQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildTypeObjectQuery orderByDetails($order = Criteria::ASC) Order by the details column
 * @method     ChildTypeObjectQuery orderByDummyObject($order = Criteria::ASC) Order by the dummy_object column
 * @method     ChildTypeObjectQuery orderBySelfRef($order = Criteria::ASC) Order by the self_ref column
 * @method     ChildTypeObjectQuery orderBySomeArray($order = Criteria::ASC) Order by the some_array column
 *
 * @method     ChildTypeObjectQuery groupById() Group by the id column
 * @method     ChildTypeObjectQuery groupByDetails() Group by the details column
 * @method     ChildTypeObjectQuery groupByDummyObject() Group by the dummy_object column
 * @method     ChildTypeObjectQuery groupBySelfRef() Group by the self_ref column
 * @method     ChildTypeObjectQuery groupBySomeArray() Group by the some_array column
 *
 * @method     ChildTypeObjectQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildTypeObjectQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildTypeObjectQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildTypeObjectQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildTypeObjectQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildTypeObjectQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildTypeObjectQuery leftJoinTypeObject($relationAlias = null) Adds a LEFT JOIN clause to the query using the TypeObject relation
 * @method     ChildTypeObjectQuery rightJoinTypeObject($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TypeObject relation
 * @method     ChildTypeObjectQuery innerJoinTypeObject($relationAlias = null) Adds a INNER JOIN clause to the query using the TypeObject relation
 *
 * @method     ChildTypeObjectQuery joinWithTypeObject($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the TypeObject relation
 *
 * @method     ChildTypeObjectQuery leftJoinWithTypeObject() Adds a LEFT JOIN clause and with to the query using the TypeObject relation
 * @method     ChildTypeObjectQuery rightJoinWithTypeObject() Adds a RIGHT JOIN clause and with to the query using the TypeObject relation
 * @method     ChildTypeObjectQuery innerJoinWithTypeObject() Adds a INNER JOIN clause and with to the query using the TypeObject relation
 *
 * @method     ChildTypeObjectQuery leftJoinTypeObjectRelatedById($relationAlias = null) Adds a LEFT JOIN clause to the query using the TypeObjectRelatedById relation
 * @method     ChildTypeObjectQuery rightJoinTypeObjectRelatedById($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TypeObjectRelatedById relation
 * @method     ChildTypeObjectQuery innerJoinTypeObjectRelatedById($relationAlias = null) Adds a INNER JOIN clause to the query using the TypeObjectRelatedById relation
 *
 * @method     ChildTypeObjectQuery joinWithTypeObjectRelatedById($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the TypeObjectRelatedById relation
 *
 * @method     ChildTypeObjectQuery leftJoinWithTypeObjectRelatedById() Adds a LEFT JOIN clause and with to the query using the TypeObjectRelatedById relation
 * @method     ChildTypeObjectQuery rightJoinWithTypeObjectRelatedById() Adds a RIGHT JOIN clause and with to the query using the TypeObjectRelatedById relation
 * @method     ChildTypeObjectQuery innerJoinWithTypeObjectRelatedById() Adds a INNER JOIN clause and with to the query using the TypeObjectRelatedById relation
 *
 * @method     \Propel\Tests\Bookstore\TypeObjectQuery|\Propel\Tests\Bookstore\TypeObjectQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildTypeObject|null findOne(?ConnectionInterface $con = null) Return the first ChildTypeObject matching the query
 * @method     ChildTypeObject findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildTypeObject matching the query, or a new ChildTypeObject object populated from the query conditions when no match is found
 *
 * @method     ChildTypeObject|null findOneById(int $id) Return the first ChildTypeObject filtered by the id column
 * @method     ChildTypeObject|null findOneByDetails( $details) Return the first ChildTypeObject filtered by the details column
 * @method     ChildTypeObject|null findOneByDummyObject( $dummy_object) Return the first ChildTypeObject filtered by the dummy_object column
 * @method     ChildTypeObject|null findOneBySelfRef(int $self_ref) Return the first ChildTypeObject filtered by the self_ref column
 * @method     ChildTypeObject|null findOneBySomeArray(array $some_array) Return the first ChildTypeObject filtered by the some_array column
 *
 * @method     ChildTypeObject requirePk($key, ?ConnectionInterface $con = null) Return the ChildTypeObject by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTypeObject requireOne(?ConnectionInterface $con = null) Return the first ChildTypeObject matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildTypeObject requireOneById(int $id) Return the first ChildTypeObject filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTypeObject requireOneByDetails( $details) Return the first ChildTypeObject filtered by the details column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTypeObject requireOneByDummyObject( $dummy_object) Return the first ChildTypeObject filtered by the dummy_object column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTypeObject requireOneBySelfRef(int $self_ref) Return the first ChildTypeObject filtered by the self_ref column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTypeObject requireOneBySomeArray(array $some_array) Return the first ChildTypeObject filtered by the some_array column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildTypeObject[]|Collection find(?ConnectionInterface $con = null) Return ChildTypeObject objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildTypeObject> find(?ConnectionInterface $con = null) Return ChildTypeObject objects based on current ModelCriteria
 *
 * @method     ChildTypeObject[]|Collection findById(int|array<int> $id) Return ChildTypeObject objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildTypeObject> findById(int|array<int> $id) Return ChildTypeObject objects filtered by the id column
 * @method     ChildTypeObject[]|Collection findByDetails(|array<> $details) Return ChildTypeObject objects filtered by the details column
 * @psalm-method Collection&\Traversable<ChildTypeObject> findByDetails(|array<> $details) Return ChildTypeObject objects filtered by the details column
 * @method     ChildTypeObject[]|Collection findByDummyObject(|array<> $dummy_object) Return ChildTypeObject objects filtered by the dummy_object column
 * @psalm-method Collection&\Traversable<ChildTypeObject> findByDummyObject(|array<> $dummy_object) Return ChildTypeObject objects filtered by the dummy_object column
 * @method     ChildTypeObject[]|Collection findBySelfRef(int|array<int> $self_ref) Return ChildTypeObject objects filtered by the self_ref column
 * @psalm-method Collection&\Traversable<ChildTypeObject> findBySelfRef(int|array<int> $self_ref) Return ChildTypeObject objects filtered by the self_ref column
 * @method     ChildTypeObject[]|Collection findBySomeArray(array|array<array> $some_array) Return ChildTypeObject objects filtered by the some_array column
 * @psalm-method Collection&\Traversable<ChildTypeObject> findBySomeArray(array|array<array> $some_array) Return ChildTypeObject objects filtered by the some_array column
 *
 * @method     ChildTypeObject[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildTypeObject> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class TypeObjectQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\TypeObjectQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\TypeObject', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildTypeObjectQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildTypeObjectQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildTypeObjectQuery) {
            return $criteria;
        }
        $query = new ChildTypeObjectQuery();
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
     * @return ChildTypeObject|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(TypeObjectTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = TypeObjectTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildTypeObject A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, details, dummy_object, self_ref, some_array FROM type_object WHERE id = :p0';
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
            /** @var ChildTypeObject $obj */
            $obj = new ChildTypeObject();
            $obj->hydrate($row);
            TypeObjectTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildTypeObject|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(TypeObjectTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(TypeObjectTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(TypeObjectTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(TypeObjectTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(TypeObjectTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the details column
     *
     * @param mixed $details The value to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByDetails($details = null, ?string $comparison = null)
    {
        if (is_object($details)) {
            $details = serialize($details);
        }

        $this->addUsingAlias(TypeObjectTableMap::COL_DETAILS, $details, $comparison);

        return $this;
    }

    /**
     * Filter the query on the dummy_object column
     *
     * @param mixed $dummyObject The value to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByDummyObject($dummyObject = null, ?string $comparison = null)
    {
        if (is_object($dummyObject)) {
            $dummyObject = serialize($dummyObject);
        }

        $this->addUsingAlias(TypeObjectTableMap::COL_DUMMY_OBJECT, $dummyObject, $comparison);

        return $this;
    }

    /**
     * Filter the query on the self_ref column
     *
     * Example usage:
     * <code>
     * $query->filterBySelfRef(1234); // WHERE self_ref = 1234
     * $query->filterBySelfRef(array(12, 34)); // WHERE self_ref IN (12, 34)
     * $query->filterBySelfRef(array('min' => 12)); // WHERE self_ref > 12
     * </code>
     *
     * @see       filterByTypeObject()
     *
     * @param mixed $selfRef The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySelfRef($selfRef = null, ?string $comparison = null)
    {
        if (is_array($selfRef)) {
            $useMinMax = false;
            if (isset($selfRef['min'])) {
                $this->addUsingAlias(TypeObjectTableMap::COL_SELF_REF, $selfRef['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($selfRef['max'])) {
                $this->addUsingAlias(TypeObjectTableMap::COL_SELF_REF, $selfRef['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(TypeObjectTableMap::COL_SELF_REF, $selfRef, $comparison);

        return $this;
    }

    /**
     * Filter the query on the some_array column
     *
     * @param array $someArray The values to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterBySomeArray($someArray = null, ?string $comparison = null)
    {
        $key = $this->getAliasedColName(TypeObjectTableMap::COL_SOME_ARRAY);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($someArray as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($someArray as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($someArray as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::NOT_LIKE);
                } else {
                    $this->add($key, $value, Criteria::NOT_LIKE);
                }
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        $this->addUsingAlias(TypeObjectTableMap::COL_SOME_ARRAY, $someArray, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\TypeObject object
     *
     * @param \Propel\Tests\Bookstore\TypeObject|ObjectCollection $typeObject The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTypeObject($typeObject, ?string $comparison = null)
    {
        if ($typeObject instanceof \Propel\Tests\Bookstore\TypeObject) {
            return $this
                ->addUsingAlias(TypeObjectTableMap::COL_SELF_REF, $typeObject->getId(), $comparison);
        } elseif ($typeObject instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(TypeObjectTableMap::COL_SELF_REF, $typeObject->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByTypeObject() only accepts arguments of type \Propel\Tests\Bookstore\TypeObject or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TypeObject relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinTypeObject(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TypeObject');

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
            $this->addJoinObject($join, 'TypeObject');
        }

        return $this;
    }

    /**
     * Use the TypeObject relation TypeObject object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery A secondary query class using the current class as primary query
     */
    public function useTypeObjectQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTypeObject($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TypeObject', '\Propel\Tests\Bookstore\TypeObjectQuery');
    }

    /**
     * Use the TypeObject relation TypeObject object
     *
     * @param callable(\Propel\Tests\Bookstore\TypeObjectQuery):\Propel\Tests\Bookstore\TypeObjectQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withTypeObjectQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useTypeObjectQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to TypeObject table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the EXISTS statement
     */
    public function useTypeObjectExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useExistsQuery('TypeObject', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to TypeObject table for a NOT EXISTS query.
     *
     * @see useTypeObjectExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the NOT EXISTS statement
     */
    public function useTypeObjectNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useExistsQuery('TypeObject', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to TypeObject table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the IN statement
     */
    public function useInTypeObjectQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useInQuery('TypeObject', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to TypeObject table for a NOT IN query.
     *
     * @see useTypeObjectInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the NOT IN statement
     */
    public function useNotInTypeObjectQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useInQuery('TypeObject', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\TypeObject object
     *
     * @param \Propel\Tests\Bookstore\TypeObject|ObjectCollection $typeObject the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTypeObjectRelatedById($typeObject, ?string $comparison = null)
    {
        if ($typeObject instanceof \Propel\Tests\Bookstore\TypeObject) {
            $this
                ->addUsingAlias(TypeObjectTableMap::COL_ID, $typeObject->getSelfRef(), $comparison);

            return $this;
        } elseif ($typeObject instanceof ObjectCollection) {
            $this
                ->useTypeObjectRelatedByIdQuery()
                ->filterByPrimaryKeys($typeObject->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByTypeObjectRelatedById() only accepts arguments of type \Propel\Tests\Bookstore\TypeObject or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TypeObjectRelatedById relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinTypeObjectRelatedById(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TypeObjectRelatedById');

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
            $this->addJoinObject($join, 'TypeObjectRelatedById');
        }

        return $this;
    }

    /**
     * Use the TypeObjectRelatedById relation TypeObject object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery A secondary query class using the current class as primary query
     */
    public function useTypeObjectRelatedByIdQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTypeObjectRelatedById($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TypeObjectRelatedById', '\Propel\Tests\Bookstore\TypeObjectQuery');
    }

    /**
     * Use the TypeObjectRelatedById relation TypeObject object
     *
     * @param callable(\Propel\Tests\Bookstore\TypeObjectQuery):\Propel\Tests\Bookstore\TypeObjectQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withTypeObjectRelatedByIdQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useTypeObjectRelatedByIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the TypeObjectRelatedById relation to the TypeObject table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the EXISTS statement
     */
    public function useTypeObjectRelatedByIdExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useExistsQuery('TypeObjectRelatedById', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the TypeObjectRelatedById relation to the TypeObject table for a NOT EXISTS query.
     *
     * @see useTypeObjectRelatedByIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the NOT EXISTS statement
     */
    public function useTypeObjectRelatedByIdNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useExistsQuery('TypeObjectRelatedById', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the TypeObjectRelatedById relation to the TypeObject table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the IN statement
     */
    public function useInTypeObjectRelatedByIdQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useInQuery('TypeObjectRelatedById', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the TypeObjectRelatedById relation to the TypeObject table for a NOT IN query.
     *
     * @see useTypeObjectRelatedByIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\TypeObjectQuery The inner query object of the NOT IN statement
     */
    public function useNotInTypeObjectRelatedByIdQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\TypeObjectQuery */
        $q = $this->useInQuery('TypeObjectRelatedById', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildTypeObject $typeObject Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($typeObject = null)
    {
        if ($typeObject) {
            $this->addUsingAlias(TypeObjectTableMap::COL_ID, $typeObject->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the type_object table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(TypeObjectTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            TypeObjectTableMap::clearInstancePool();
            TypeObjectTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(TypeObjectTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(TypeObjectTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            TypeObjectTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            TypeObjectTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
