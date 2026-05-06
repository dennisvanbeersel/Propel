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
use Propel\Tests\Bookstore\Behavior\ValidateReader as ChildValidateReader;
use Propel\Tests\Bookstore\Behavior\ValidateReaderQuery as ChildValidateReaderQuery;
use Propel\Tests\Bookstore\Behavior\Map\ValidateReaderTableMap;

/**
 * Base class that represents a query for the `validate_reader` table.
 *
 * Reader Table
 *
 * @method     ChildValidateReaderQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildValidateReaderQuery orderByFirstName($order = Criteria::ASC) Order by the first_name column
 * @method     ChildValidateReaderQuery orderByLastName($order = Criteria::ASC) Order by the last_name column
 * @method     ChildValidateReaderQuery orderByEmail($order = Criteria::ASC) Order by the email column
 * @method     ChildValidateReaderQuery orderByBirthday($order = Criteria::ASC) Order by the birthday column
 *
 * @method     ChildValidateReaderQuery groupById() Group by the id column
 * @method     ChildValidateReaderQuery groupByFirstName() Group by the first_name column
 * @method     ChildValidateReaderQuery groupByLastName() Group by the last_name column
 * @method     ChildValidateReaderQuery groupByEmail() Group by the email column
 * @method     ChildValidateReaderQuery groupByBirthday() Group by the birthday column
 *
 * @method     ChildValidateReaderQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildValidateReaderQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildValidateReaderQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildValidateReaderQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildValidateReaderQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildValidateReaderQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildValidateReaderQuery leftJoinValidateReaderBook($relationAlias = null) Adds a LEFT JOIN clause to the query using the ValidateReaderBook relation
 * @method     ChildValidateReaderQuery rightJoinValidateReaderBook($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ValidateReaderBook relation
 * @method     ChildValidateReaderQuery innerJoinValidateReaderBook($relationAlias = null) Adds a INNER JOIN clause to the query using the ValidateReaderBook relation
 *
 * @method     ChildValidateReaderQuery joinWithValidateReaderBook($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ValidateReaderBook relation
 *
 * @method     ChildValidateReaderQuery leftJoinWithValidateReaderBook() Adds a LEFT JOIN clause and with to the query using the ValidateReaderBook relation
 * @method     ChildValidateReaderQuery rightJoinWithValidateReaderBook() Adds a RIGHT JOIN clause and with to the query using the ValidateReaderBook relation
 * @method     ChildValidateReaderQuery innerJoinWithValidateReaderBook() Adds a INNER JOIN clause and with to the query using the ValidateReaderBook relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildValidateReader|null findOne(?ConnectionInterface $con = null) Return the first ChildValidateReader matching the query
 * @method     ChildValidateReader findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildValidateReader matching the query, or a new ChildValidateReader object populated from the query conditions when no match is found
 *
 * @method     ChildValidateReader|null findOneById(int $id) Return the first ChildValidateReader filtered by the id column
 * @method     ChildValidateReader|null findOneByFirstName(string $first_name) Return the first ChildValidateReader filtered by the first_name column
 * @method     ChildValidateReader|null findOneByLastName(string $last_name) Return the first ChildValidateReader filtered by the last_name column
 * @method     ChildValidateReader|null findOneByEmail(string $email) Return the first ChildValidateReader filtered by the email column
 * @method     ChildValidateReader|null findOneByBirthday(string $birthday) Return the first ChildValidateReader filtered by the birthday column
 *
 * @method     ChildValidateReader requirePk($key, ?ConnectionInterface $con = null) Return the ChildValidateReader by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateReader requireOne(?ConnectionInterface $con = null) Return the first ChildValidateReader matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateReader requireOneById(int $id) Return the first ChildValidateReader filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateReader requireOneByFirstName(string $first_name) Return the first ChildValidateReader filtered by the first_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateReader requireOneByLastName(string $last_name) Return the first ChildValidateReader filtered by the last_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateReader requireOneByEmail(string $email) Return the first ChildValidateReader filtered by the email column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildValidateReader requireOneByBirthday(string $birthday) Return the first ChildValidateReader filtered by the birthday column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildValidateReader[]|Collection find(?ConnectionInterface $con = null) Return ChildValidateReader objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildValidateReader> find(?ConnectionInterface $con = null) Return ChildValidateReader objects based on current ModelCriteria
 *
 * @method     ChildValidateReader[]|Collection findById(int|array<int> $id) Return ChildValidateReader objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildValidateReader> findById(int|array<int> $id) Return ChildValidateReader objects filtered by the id column
 * @method     ChildValidateReader[]|Collection findByFirstName(string|array<string> $first_name) Return ChildValidateReader objects filtered by the first_name column
 * @psalm-method Collection&\Traversable<ChildValidateReader> findByFirstName(string|array<string> $first_name) Return ChildValidateReader objects filtered by the first_name column
 * @method     ChildValidateReader[]|Collection findByLastName(string|array<string> $last_name) Return ChildValidateReader objects filtered by the last_name column
 * @psalm-method Collection&\Traversable<ChildValidateReader> findByLastName(string|array<string> $last_name) Return ChildValidateReader objects filtered by the last_name column
 * @method     ChildValidateReader[]|Collection findByEmail(string|array<string> $email) Return ChildValidateReader objects filtered by the email column
 * @psalm-method Collection&\Traversable<ChildValidateReader> findByEmail(string|array<string> $email) Return ChildValidateReader objects filtered by the email column
 * @method     ChildValidateReader[]|Collection findByBirthday(string|array<string> $birthday) Return ChildValidateReader objects filtered by the birthday column
 * @psalm-method Collection&\Traversable<ChildValidateReader> findByBirthday(string|array<string> $birthday) Return ChildValidateReader objects filtered by the birthday column
 *
 * @method     ChildValidateReader[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildValidateReader> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ValidateReaderQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ValidateReaderQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateReader', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildValidateReaderQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildValidateReaderQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): Criteria
    {
        if ($criteria instanceof ChildValidateReaderQuery) {
            return $criteria;
        }
        $query = new ChildValidateReaderQuery();
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
     * @return ChildValidateReader|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ValidateReaderTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ValidateReaderTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildValidateReader A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, first_name, last_name, email, birthday FROM validate_reader WHERE id = :p0';
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
            /** @var ChildValidateReader $obj */
            $obj = new ChildValidateReader();
            $obj->hydrate($row);
            ValidateReaderTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildValidateReader|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(ValidateReaderTableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(ValidateReaderTableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(ValidateReaderTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ValidateReaderTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateReaderTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the first_name column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstName('fooValue');   // WHERE first_name = 'fooValue'
     * $query->filterByFirstName('%fooValue%', Criteria::LIKE); // WHERE first_name LIKE '%fooValue%'
     * $query->filterByFirstName(['foo', 'bar']); // WHERE first_name IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $firstName The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByFirstName($firstName = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($firstName)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateReaderTableMap::COL_FIRST_NAME, $firstName, $comparison);

        return $this;
    }

    /**
     * Filter the query on the last_name column
     *
     * Example usage:
     * <code>
     * $query->filterByLastName('fooValue');   // WHERE last_name = 'fooValue'
     * $query->filterByLastName('%fooValue%', Criteria::LIKE); // WHERE last_name LIKE '%fooValue%'
     * $query->filterByLastName(['foo', 'bar']); // WHERE last_name IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $lastName The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByLastName($lastName = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastName)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateReaderTableMap::COL_LAST_NAME, $lastName, $comparison);

        return $this;
    }

    /**
     * Filter the query on the email column
     *
     * Example usage:
     * <code>
     * $query->filterByEmail('fooValue');   // WHERE email = 'fooValue'
     * $query->filterByEmail('%fooValue%', Criteria::LIKE); // WHERE email LIKE '%fooValue%'
     * $query->filterByEmail(['foo', 'bar']); // WHERE email IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $email The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByEmail($email = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($email)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateReaderTableMap::COL_EMAIL, $email, $comparison);

        return $this;
    }

    /**
     * Filter the query on the birthday column
     *
     * Example usage:
     * <code>
     * $query->filterByBirthday('2011-03-14'); // WHERE birthday = '2011-03-14'
     * $query->filterByBirthday('now'); // WHERE birthday = '2011-03-14'
     * $query->filterByBirthday(array('max' => 'yesterday')); // WHERE birthday > '2011-03-13'
     * </code>
     *
     * @param mixed $birthday The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBirthday($birthday = null, ?string $comparison = null)
    {
        if (is_array($birthday)) {
            $useMinMax = false;
            if (isset($birthday['min'])) {
                $this->addUsingAlias(ValidateReaderTableMap::COL_BIRTHDAY, $birthday['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($birthday['max'])) {
                $this->addUsingAlias(ValidateReaderTableMap::COL_BIRTHDAY, $birthday['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ValidateReaderTableMap::COL_BIRTHDAY, $birthday, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ValidateReaderBook object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ValidateReaderBook|ObjectCollection $validateReaderBook the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateReaderBook($validateReaderBook, ?string $comparison = null)
    {
        if ($validateReaderBook instanceof \Propel\Tests\Bookstore\Behavior\ValidateReaderBook) {
            $this
                ->addUsingAlias(ValidateReaderTableMap::COL_ID, $validateReaderBook->getReaderId(), $comparison);

            return $this;
        } elseif ($validateReaderBook instanceof ObjectCollection) {
            $this
                ->useValidateReaderBookQuery()
                ->filterByPrimaryKeys($validateReaderBook->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByValidateReaderBook() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ValidateReaderBook or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ValidateReaderBook relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinValidateReaderBook(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ValidateReaderBook');

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
            $this->addJoinObject($join, 'ValidateReaderBook');
        }

        return $this;
    }

    /**
     * Use the ValidateReaderBook relation ValidateReaderBook object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery A secondary query class using the current class as primary query
     */
    public function useValidateReaderBookQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinValidateReaderBook($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ValidateReaderBook', '\Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery');
    }

    /**
     * Use the ValidateReaderBook relation ValidateReaderBook object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery):\Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withValidateReaderBookQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useValidateReaderBookQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ValidateReaderBook table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the EXISTS statement
     */
    public function useValidateReaderBookExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useExistsQuery('ValidateReaderBook', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ValidateReaderBook table for a NOT EXISTS query.
     *
     * @see useValidateReaderBookExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the NOT EXISTS statement
     */
    public function useValidateReaderBookNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useExistsQuery('ValidateReaderBook', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ValidateReaderBook table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the IN statement
     */
    public function useInValidateReaderBookQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useInQuery('ValidateReaderBook', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ValidateReaderBook table for a NOT IN query.
     *
     * @see useValidateReaderBookInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery The inner query object of the NOT IN statement
     */
    public function useNotInValidateReaderBookQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery */
        $q = $this->useInQuery('ValidateReaderBook', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related ValidateBook object
     * using the validate_reader_book table as cross reference
     *
     * @param ValidateBook $validateBook the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL and Criteria::IN for queries
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByValidateBook($validateBook, ?string $comparison = null)
    {
        $this
            ->useValidateReaderBookQuery()
            ->filterByValidateBook($validateBook, $comparison)
            ->endUse();

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildValidateReader $validateReader Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($validateReader = null)
    {
        if ($validateReader) {
            $this->addUsingAlias(ValidateReaderTableMap::COL_ID, $validateReader->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the validate_reader table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateReaderTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ValidateReaderTableMap::clearInstancePool();
            ValidateReaderTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateReaderTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ValidateReaderTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ValidateReaderTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ValidateReaderTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
