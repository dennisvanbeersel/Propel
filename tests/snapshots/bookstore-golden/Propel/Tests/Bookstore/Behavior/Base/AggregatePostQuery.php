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
use Propel\Tests\Bookstore\Behavior\AggregatePost as ChildAggregatePost;
use Propel\Tests\Bookstore\Behavior\AggregatePostQuery as ChildAggregatePostQuery;
use Propel\Tests\Bookstore\Behavior\Map\AggregatePostTableMap;

/**
 * Base class that represents a query for the `aggregate_post` table.
 *
 * @method     ChildAggregatePostQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildAggregatePostQuery orderByNbComments($order = Criteria::ASC) Order by the nb_comments column
 *
 * @method     ChildAggregatePostQuery groupById() Group by the id column
 * @method     ChildAggregatePostQuery groupByNbComments() Group by the nb_comments column
 *
 * @method     ChildAggregatePostQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildAggregatePostQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildAggregatePostQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildAggregatePostQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildAggregatePostQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildAggregatePostQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildAggregatePostQuery leftJoinAggregateComment($relationAlias = null) Adds a LEFT JOIN clause to the query using the AggregateComment relation
 * @method     ChildAggregatePostQuery rightJoinAggregateComment($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AggregateComment relation
 * @method     ChildAggregatePostQuery innerJoinAggregateComment($relationAlias = null) Adds a INNER JOIN clause to the query using the AggregateComment relation
 *
 * @method     ChildAggregatePostQuery joinWithAggregateComment($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the AggregateComment relation
 *
 * @method     ChildAggregatePostQuery leftJoinWithAggregateComment() Adds a LEFT JOIN clause and with to the query using the AggregateComment relation
 * @method     ChildAggregatePostQuery rightJoinWithAggregateComment() Adds a RIGHT JOIN clause and with to the query using the AggregateComment relation
 * @method     ChildAggregatePostQuery innerJoinWithAggregateComment() Adds a INNER JOIN clause and with to the query using the AggregateComment relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildAggregatePost|null findOne(?ConnectionInterface $con = null) Return the first ChildAggregatePost matching the query
 * @method     ChildAggregatePost findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildAggregatePost matching the query, or a new ChildAggregatePost object populated from the query conditions when no match is found
 *
 * @method     ChildAggregatePost|null findOneById(int $id) Return the first ChildAggregatePost filtered by the id column
 * @method     ChildAggregatePost|null findOneByNbComments(int $nb_comments) Return the first ChildAggregatePost filtered by the nb_comments column
 *
 * @method     ChildAggregatePost requirePk($key, ?ConnectionInterface $con = null) Return the ChildAggregatePost by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregatePost requireOne(?ConnectionInterface $con = null) Return the first ChildAggregatePost matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAggregatePost requireOneById(int $id) Return the first ChildAggregatePost filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildAggregatePost requireOneByNbComments(int $nb_comments) Return the first ChildAggregatePost filtered by the nb_comments column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildAggregatePost[]|Collection find(?ConnectionInterface $con = null) Return ChildAggregatePost objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildAggregatePost> find(?ConnectionInterface $con = null) Return ChildAggregatePost objects based on current ModelCriteria
 *
 * @method     ChildAggregatePost[]|Collection findById(int|array<int> $id) Return ChildAggregatePost objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildAggregatePost> findById(int|array<int> $id) Return ChildAggregatePost objects filtered by the id column
 * @method     ChildAggregatePost[]|Collection findByNbComments(int|array<int> $nb_comments) Return ChildAggregatePost objects filtered by the nb_comments column
 * @psalm-method Collection&\Traversable<ChildAggregatePost> findByNbComments(int|array<int> $nb_comments) Return ChildAggregatePost objects filtered by the nb_comments column
 *
 * @method     ChildAggregatePost[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildAggregatePost> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class AggregatePostQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\AggregatePostQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\AggregatePost', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildAggregatePostQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildAggregatePostQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildAggregatePostQuery) {
            return $criteria;
        }
        $query = new ChildAggregatePostQuery();
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
     * @return ChildAggregatePost|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(AggregatePostTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = AggregatePostTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildAggregatePost A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, nb_comments FROM aggregate_post WHERE id = :p0';
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
            /** @var ChildAggregatePost $obj */
            $obj = new ChildAggregatePost();
            $obj->hydrate($row);
            AggregatePostTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildAggregatePost|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(AggregatePostTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(AggregatePostTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(AggregatePostTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(AggregatePostTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregatePostTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the nb_comments column
     *
     * Example usage:
     * <code>
     * $query->filterByNbComments(1234); // WHERE nb_comments = 1234
     * $query->filterByNbComments(array(12, 34)); // WHERE nb_comments IN (12, 34)
     * $query->filterByNbComments(array('min' => 12)); // WHERE nb_comments > 12
     * </code>
     *
     * @param mixed $nbComments The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByNbComments($nbComments = null, ?string $comparison = null)
    {
        if (is_array($nbComments)) {
            $useMinMax = false;
            if (isset($nbComments['min'])) {
                $this->addUsingAlias(AggregatePostTableMap::COL_NB_COMMENTS, $nbComments['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($nbComments['max'])) {
                $this->addUsingAlias(AggregatePostTableMap::COL_NB_COMMENTS, $nbComments['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(AggregatePostTableMap::COL_NB_COMMENTS, $nbComments, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\AggregateComment object
     *
     * @param \Propel\Tests\Bookstore\Behavior\AggregateComment|ObjectCollection $aggregateComment the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAggregateComment($aggregateComment, ?string $comparison = null)
    {
        if ($aggregateComment instanceof \Propel\Tests\Bookstore\Behavior\AggregateComment) {
            $this
                ->addUsingAlias(AggregatePostTableMap::COL_ID, $aggregateComment->getPostId(), $comparison);

            return $this;
        } elseif ($aggregateComment instanceof ObjectCollection) {
            $this
                ->useAggregateCommentQuery()
                ->filterByPrimaryKeys($aggregateComment->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByAggregateComment() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\AggregateComment or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AggregateComment relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinAggregateComment(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AggregateComment');

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
            $this->addJoinObject($join, 'AggregateComment');
        }

        return $this;
    }

    /**
     * Use the AggregateComment relation AggregateComment object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery A secondary query class using the current class as primary query
     */
    public function useAggregateCommentQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAggregateComment($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AggregateComment', '\Propel\Tests\Bookstore\Behavior\AggregateCommentQuery');
    }

    /**
     * Use the AggregateComment relation AggregateComment object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\AggregateCommentQuery):\Propel\Tests\Bookstore\Behavior\AggregateCommentQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withAggregateCommentQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useAggregateCommentQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to AggregateComment table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery The inner query object of the EXISTS statement
     */
    public function useAggregateCommentExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery */
        $q = $this->useExistsQuery('AggregateComment', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to AggregateComment table for a NOT EXISTS query.
     *
     * @see useAggregateCommentExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery The inner query object of the NOT EXISTS statement
     */
    public function useAggregateCommentNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery */
        $q = $this->useExistsQuery('AggregateComment', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to AggregateComment table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery The inner query object of the IN statement
     */
    public function useInAggregateCommentQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery */
        $q = $this->useInQuery('AggregateComment', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to AggregateComment table for a NOT IN query.
     *
     * @see useAggregateCommentInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery The inner query object of the NOT IN statement
     */
    public function useNotInAggregateCommentQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\AggregateCommentQuery */
        $q = $this->useInQuery('AggregateComment', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildAggregatePost $aggregatePost Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($aggregatePost = null)
    {
        if ($aggregatePost) {
            $this->addUsingAlias(AggregatePostTableMap::COL_ID, $aggregatePost->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the aggregate_post table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AggregatePostTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            AggregatePostTableMap::clearInstancePool();
            AggregatePostTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(AggregatePostTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(AggregatePostTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            AggregatePostTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            AggregatePostTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
