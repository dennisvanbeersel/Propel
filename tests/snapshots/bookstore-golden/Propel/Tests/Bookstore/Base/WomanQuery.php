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
use Propel\Tests\Bookstore\Woman as ChildWoman;
use Propel\Tests\Bookstore\WomanQuery as ChildWomanQuery;
use Propel\Tests\Bookstore\Map\WomanTableMap;

/**
 * Base class that represents a query for the `woman` table.
 *
 * @method     ChildWomanQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildWomanQuery orderByHusbandId($order = Criteria::ASC) Order by the husband_id column
 *
 * @method     ChildWomanQuery groupById() Group by the id column
 * @method     ChildWomanQuery groupByHusbandId() Group by the husband_id column
 *
 * @method     ChildWomanQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildWomanQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildWomanQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildWomanQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildWomanQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildWomanQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildWomanQuery leftJoinManRelatedByHusbandId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ManRelatedByHusbandId relation
 * @method     ChildWomanQuery rightJoinManRelatedByHusbandId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ManRelatedByHusbandId relation
 * @method     ChildWomanQuery innerJoinManRelatedByHusbandId($relationAlias = null) Adds a INNER JOIN clause to the query using the ManRelatedByHusbandId relation
 *
 * @method     ChildWomanQuery joinWithManRelatedByHusbandId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ManRelatedByHusbandId relation
 *
 * @method     ChildWomanQuery leftJoinWithManRelatedByHusbandId() Adds a LEFT JOIN clause and with to the query using the ManRelatedByHusbandId relation
 * @method     ChildWomanQuery rightJoinWithManRelatedByHusbandId() Adds a RIGHT JOIN clause and with to the query using the ManRelatedByHusbandId relation
 * @method     ChildWomanQuery innerJoinWithManRelatedByHusbandId() Adds a INNER JOIN clause and with to the query using the ManRelatedByHusbandId relation
 *
 * @method     ChildWomanQuery leftJoinManRelatedByWifeId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ManRelatedByWifeId relation
 * @method     ChildWomanQuery rightJoinManRelatedByWifeId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ManRelatedByWifeId relation
 * @method     ChildWomanQuery innerJoinManRelatedByWifeId($relationAlias = null) Adds a INNER JOIN clause to the query using the ManRelatedByWifeId relation
 *
 * @method     ChildWomanQuery joinWithManRelatedByWifeId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ManRelatedByWifeId relation
 *
 * @method     ChildWomanQuery leftJoinWithManRelatedByWifeId() Adds a LEFT JOIN clause and with to the query using the ManRelatedByWifeId relation
 * @method     ChildWomanQuery rightJoinWithManRelatedByWifeId() Adds a RIGHT JOIN clause and with to the query using the ManRelatedByWifeId relation
 * @method     ChildWomanQuery innerJoinWithManRelatedByWifeId() Adds a INNER JOIN clause and with to the query using the ManRelatedByWifeId relation
 *
 * @method     \Propel\Tests\Bookstore\ManQuery|\Propel\Tests\Bookstore\ManQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildWoman|null findOne(?ConnectionInterface $con = null) Return the first ChildWoman matching the query
 * @method     ChildWoman findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildWoman matching the query, or a new ChildWoman object populated from the query conditions when no match is found
 *
 * @method     ChildWoman|null findOneById(int $id) Return the first ChildWoman filtered by the id column
 * @method     ChildWoman|null findOneByHusbandId(int $husband_id) Return the first ChildWoman filtered by the husband_id column
 *
 * @method     ChildWoman requirePk($key, ?ConnectionInterface $con = null) Return the ChildWoman by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildWoman requireOne(?ConnectionInterface $con = null) Return the first ChildWoman matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildWoman requireOneById(int $id) Return the first ChildWoman filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildWoman requireOneByHusbandId(int $husband_id) Return the first ChildWoman filtered by the husband_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildWoman[]|Collection find(?ConnectionInterface $con = null) Return ChildWoman objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildWoman> find(?ConnectionInterface $con = null) Return ChildWoman objects based on current ModelCriteria
 *
 * @method     ChildWoman[]|Collection findById(int|array<int> $id) Return ChildWoman objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildWoman> findById(int|array<int> $id) Return ChildWoman objects filtered by the id column
 * @method     ChildWoman[]|Collection findByHusbandId(int|array<int> $husband_id) Return ChildWoman objects filtered by the husband_id column
 * @psalm-method Collection&\Traversable<ChildWoman> findByHusbandId(int|array<int> $husband_id) Return ChildWoman objects filtered by the husband_id column
 *
 * @method     ChildWoman[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildWoman> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class WomanQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\WomanQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\Woman', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildWomanQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildWomanQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildWomanQuery) {
            return $criteria;
        }
        $query = new ChildWomanQuery();
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
     * @return ChildWoman|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(WomanTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = WomanTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildWoman A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, husband_id FROM woman WHERE id = :p0';
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
            /** @var ChildWoman $obj */
            $obj = new ChildWoman();
            $obj->hydrate($row);
            WomanTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildWoman|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(WomanTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(WomanTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(WomanTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(WomanTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(WomanTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the husband_id column
     *
     * Example usage:
     * <code>
     * $query->filterByHusbandId(1234); // WHERE husband_id = 1234
     * $query->filterByHusbandId(array(12, 34)); // WHERE husband_id IN (12, 34)
     * $query->filterByHusbandId(array('min' => 12)); // WHERE husband_id > 12
     * </code>
     *
     * @see       filterByManRelatedByHusbandId()
     *
     * @param mixed $husbandId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByHusbandId($husbandId = null, ?string $comparison = null)
    {
        if (is_array($husbandId)) {
            $useMinMax = false;
            if (isset($husbandId['min'])) {
                $this->addUsingAlias(WomanTableMap::COL_HUSBAND_ID, $husbandId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($husbandId['max'])) {
                $this->addUsingAlias(WomanTableMap::COL_HUSBAND_ID, $husbandId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(WomanTableMap::COL_HUSBAND_ID, $husbandId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Man object
     *
     * @param \Propel\Tests\Bookstore\Man|ObjectCollection $man The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByManRelatedByHusbandId($man, ?string $comparison = null)
    {
        if ($man instanceof \Propel\Tests\Bookstore\Man) {
            return $this
                ->addUsingAlias(WomanTableMap::COL_HUSBAND_ID, $man->getId(), $comparison);
        } elseif ($man instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(WomanTableMap::COL_HUSBAND_ID, $man->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByManRelatedByHusbandId() only accepts arguments of type \Propel\Tests\Bookstore\Man or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ManRelatedByHusbandId relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinManRelatedByHusbandId(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ManRelatedByHusbandId');

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
            $this->addJoinObject($join, 'ManRelatedByHusbandId');
        }

        return $this;
    }

    /**
     * Use the ManRelatedByHusbandId relation Man object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\ManQuery A secondary query class using the current class as primary query
     */
    public function useManRelatedByHusbandIdQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinManRelatedByHusbandId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ManRelatedByHusbandId', '\Propel\Tests\Bookstore\ManQuery');
    }

    /**
     * Use the ManRelatedByHusbandId relation Man object
     *
     * @param callable(\Propel\Tests\Bookstore\ManQuery):\Propel\Tests\Bookstore\ManQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withManRelatedByHusbandIdQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useManRelatedByHusbandIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the ManRelatedByHusbandId relation to the Man table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the EXISTS statement
     */
    public function useManRelatedByHusbandIdExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useExistsQuery('ManRelatedByHusbandId', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the ManRelatedByHusbandId relation to the Man table for a NOT EXISTS query.
     *
     * @see useManRelatedByHusbandIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the NOT EXISTS statement
     */
    public function useManRelatedByHusbandIdNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useExistsQuery('ManRelatedByHusbandId', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the ManRelatedByHusbandId relation to the Man table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the IN statement
     */
    public function useInManRelatedByHusbandIdQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useInQuery('ManRelatedByHusbandId', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the ManRelatedByHusbandId relation to the Man table for a NOT IN query.
     *
     * @see useManRelatedByHusbandIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the NOT IN statement
     */
    public function useNotInManRelatedByHusbandIdQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useInQuery('ManRelatedByHusbandId', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Man object
     *
     * @param \Propel\Tests\Bookstore\Man|ObjectCollection $man the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByManRelatedByWifeId($man, ?string $comparison = null)
    {
        if ($man instanceof \Propel\Tests\Bookstore\Man) {
            $this
                ->addUsingAlias(WomanTableMap::COL_ID, $man->getWifeId(), $comparison);

            return $this;
        } elseif ($man instanceof ObjectCollection) {
            $this
                ->useManRelatedByWifeIdQuery()
                ->filterByPrimaryKeys($man->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByManRelatedByWifeId() only accepts arguments of type \Propel\Tests\Bookstore\Man or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ManRelatedByWifeId relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinManRelatedByWifeId(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ManRelatedByWifeId');

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
            $this->addJoinObject($join, 'ManRelatedByWifeId');
        }

        return $this;
    }

    /**
     * Use the ManRelatedByWifeId relation Man object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\ManQuery A secondary query class using the current class as primary query
     */
    public function useManRelatedByWifeIdQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinManRelatedByWifeId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ManRelatedByWifeId', '\Propel\Tests\Bookstore\ManQuery');
    }

    /**
     * Use the ManRelatedByWifeId relation Man object
     *
     * @param callable(\Propel\Tests\Bookstore\ManQuery):\Propel\Tests\Bookstore\ManQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withManRelatedByWifeIdQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useManRelatedByWifeIdQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the ManRelatedByWifeId relation to the Man table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the EXISTS statement
     */
    public function useManRelatedByWifeIdExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useExistsQuery('ManRelatedByWifeId', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the ManRelatedByWifeId relation to the Man table for a NOT EXISTS query.
     *
     * @see useManRelatedByWifeIdExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the NOT EXISTS statement
     */
    public function useManRelatedByWifeIdNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useExistsQuery('ManRelatedByWifeId', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the ManRelatedByWifeId relation to the Man table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the IN statement
     */
    public function useInManRelatedByWifeIdQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useInQuery('ManRelatedByWifeId', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the ManRelatedByWifeId relation to the Man table for a NOT IN query.
     *
     * @see useManRelatedByWifeIdInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ManQuery The inner query object of the NOT IN statement
     */
    public function useNotInManRelatedByWifeIdQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ManQuery */
        $q = $this->useInQuery('ManRelatedByWifeId', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildWoman $woman Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($woman = null)
    {
        if ($woman) {
            $this->addUsingAlias(WomanTableMap::COL_ID, $woman->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the woman table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(WomanTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            WomanTableMap::clearInstancePool();
            WomanTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(WomanTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(WomanTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            WomanTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            WomanTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
