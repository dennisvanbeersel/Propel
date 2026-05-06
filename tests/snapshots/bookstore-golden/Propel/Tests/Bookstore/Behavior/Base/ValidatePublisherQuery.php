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
use Propel\Tests\Bookstore\Behavior\ValidatePublisher as ChildValidatePublisher;
use Propel\Tests\Bookstore\Behavior\ValidatePublisherQuery as ChildValidatePublisherQuery;
use Propel\Tests\Bookstore\Behavior\Map\ValidatePublisherTableMap;

/**
 * Base class that represents a query for the `validate_publisher` table.
 *
 * Publisher Table
 *
 * @method     ChildValidatePublisherQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildValidatePublisherQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     ChildValidatePublisherQuery orderByWebsite($order = Criteria::ASC) Order by the website column
 *
 * @method     ChildValidatePublisherQuery groupById() Group by the id column
 * @method     ChildValidatePublisherQuery groupByName() Group by the name column
 * @method     ChildValidatePublisherQuery groupByWebsite() Group by the website column
 *
 * @method     ChildValidatePublisherQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildValidatePublisherQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildValidatePublisherQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildValidatePublisherQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildValidatePublisherQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildValidatePublisherQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildValidatePublisherQuery leftJoinValidateBook($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateBook relation
 * @method     ChildValidatePublisherQuery rightJoinValidateBook($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateBook relation
 * @method     ChildValidatePublisherQuery innerJoinValidateBook($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateBook relation
 *
 * @method     ChildValidatePublisherQuery joinWithValidateBook($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateBook relation
 *
 * @method     ChildValidatePublisherQuery leftJoinWithValidateBook() Adds a LEFT JOIN clause and with to the query using the ValidateBook relation
 * @method     ChildValidatePublisherQuery rightJoinWithValidateBook() Adds a RIGHT JOIN clause and with to the query using the ValidateBook relation
 * @method     ChildValidatePublisherQuery innerJoinWithValidateBook() Adds a INNER JOIN clause and with to the query using the ValidateBook relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ValidateBookQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildValidatePublisher|null findOne(?ConnectionInterface $con = null) Return the first ChildValidatePublisher matching the query
 * @method     ChildValidatePublisher findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildValidatePublisher matching the query, or a new ChildValidatePublisher object populated from the query conditions when no match is found
 *
 * @method     ChildValidatePublisher|null findOneById(int $id) Return the first ChildValidatePublisher filtered by the id column
 * @method     ChildValidatePublisher|null findOneByName(string $name) Return the first ChildValidatePublisher filtered by the name column
 * @method     ChildValidatePublisher|null findOneByWebsite(string $website) Return the first ChildValidatePublisher filtered by the website column
 *
 * @method     ChildValidatePublisher requirePk($key, ?ConnectionInterface $con = null) Return the ChildValidatePublisher by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidatePublisher requireOne(?ConnectionInterface $con = null) Return the first ChildValidatePublisher matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidatePublisher requireOneById(int $id) Return the first ChildValidatePublisher filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidatePublisher requireOneByName(string $name) Return the first ChildValidatePublisher filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidatePublisher requireOneByWebsite(string $website) Return the first ChildValidatePublisher filtered by the website column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidatePublisher[]|Collection find(?ConnectionInterface $con = null) Return ChildValidatePublisher objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildValidatePublisher> find(?ConnectionInterface $con = null) Return ChildValidatePublisher objects based on current ModelCriteria
 *
 * @method     ChildValidatePublisher[]|Collection findById(int|array<int> $id) Return ChildValidatePublisher objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildValidatePublisher> findById(int|array<int> $id) Return ChildValidatePublisher objects filtered by the id column
 * @method     ChildValidatePublisher[]|Collection findByName(string|array<string> $name) Return ChildValidatePublisher objects filtered by the name column
 * @psalm-method Collection&\Traversable<ChildValidatePublisher> findByName(string|array<string> $name) Return ChildValidatePublisher objects filtered by the name column
 * @method     ChildValidatePublisher[]|Collection findByWebsite(string|array<string> $website) Return ChildValidatePublisher objects filtered by the website column
 * @psalm-method Collection&\Traversable<ChildValidatePublisher> findByWebsite(string|array<string> $website) Return ChildValidatePublisher objects filtered by the website column
 *
 * @method     ChildValidatePublisher[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildValidatePublisher> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ValidatePublisherQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ValidatePublisherQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ValidatePublisher', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildValidatePublisherQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildValidatePublisherQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildValidatePublisherQuery) {
            return $criteria;
        }
        $query = new ChildValidatePublisherQuery();
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
     * @return ChildValidatePublisher|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ValidatePublisherTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ValidatePublisherTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildValidatePublisher A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, name, website FROM validate_publisher WHERE id = :p0';
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
            /** @var ChildValidatePublisher $obj */
            $obj = new ChildValidatePublisher();
            $obj->hydrate($row);
            ValidatePublisherTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildValidatePublisher|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(ValidatePublisherTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(ValidatePublisherTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(ValidatePublisherTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ValidatePublisherTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidatePublisherTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * $query->filterByName(['foo', 'bar']); // WHERE name IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $name The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByName($name = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidatePublisherTableMap::COL_NAME, $name, $comparison);

        return $this;
    }

    /**
     * Filter the query on the website column
     *
     * Example usage:
     * <code>
     * $query->filterByWebsite('fooValue');   // WHERE website = 'fooValue'
     * $query->filterByWebsite('%fooValue%', Criteria::LIKE); // WHERE website LIKE '%fooValue%'
     * $query->filterByWebsite(['foo', 'bar']); // WHERE website IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $website The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByWebsite($website = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($website)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidatePublisherTableMap::COL_WEBSITE, $website, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateBook object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateBook|ObjectCollection $validateBook the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateBook($validateBook, ?string $comparison = null)
    {
        if ($validateBook instanceof \Propel\Tests\Bookstore\Behavior\ValidateBook) {
            $this
                ->addUsingAlias(ValidatePublisherTableMap::COL_ID, $validateBook->getPublisherId(), $comparison);

            return $this;
        } elseif ($validateBook instanceof ObjectCollection) {
            $this
                ->useValidateBookQuery()
                ->filterByPrimaryKeys($validateBook->getPrimaryKeys())
                ->endUse();

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
    public function joinValidateBook(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
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
    public function useValidateBookQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
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
        ?string $joinType = Criteria::LEFT_JOIN
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
     * @param ChildValidatePublisher $validatePublisher Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($validatePublisher = null)
    {
        if ($validatePublisher) {
            $this->addUsingAlias(ValidatePublisherTableMap::COL_ID, $validatePublisher->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the validate_publisher table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidatePublisherTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ValidatePublisherTableMap::clearInstancePool();
            ValidatePublisherTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidatePublisherTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ValidatePublisherTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ValidatePublisherTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ValidatePublisherTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
