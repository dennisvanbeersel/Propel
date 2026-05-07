<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Tests\Bookstore\Behavior\Table3 as ChildTable3;
use Propel\Tests\Bookstore\Behavior\Table3Query as ChildTable3Query;
use Propel\Tests\Bookstore\Behavior\Map\Table3TableMap;

/**
 * Base class that represents a query for the `table3` table.
 *
 * @method     ChildTable3Query orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildTable3Query orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildTable3Query orderByTest($order = Criteria::ASC) Order by the test column
 *
 * @method     ChildTable3Query groupById() Group by the id column
 * @method     ChildTable3Query groupByTitle() Group by the title column
 * @method     ChildTable3Query groupByTest() Group by the test column
 *
 * @method     ChildTable3Query leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildTable3Query rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildTable3Query innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildTable3Query leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildTable3Query rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildTable3Query innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildTable3|null findOne(?ConnectionInterface $con = null) Return the first ChildTable3 matching the query
 * @method     ChildTable3 findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildTable3 matching the query, or a new ChildTable3 object populated from the query conditions when no match is found
 *
 * @method     ChildTable3|null findOneById(int $id) Return the first ChildTable3 filtered by the id column
 * @method     ChildTable3|null findOneByTitle(string $title) Return the first ChildTable3 filtered by the title column
 * @method     ChildTable3|null findOneByTest(string $test) Return the first ChildTable3 filtered by the test column
 *
 * @method     ChildTable3 requirePk($key, ?ConnectionInterface $con = null) Return the ChildTable3 by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTable3 requireOne(?ConnectionInterface $con = null) Return the first ChildTable3 matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildTable3 requireOneById(int $id) Return the first ChildTable3 filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTable3 requireOneByTitle(string $title) Return the first ChildTable3 filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildTable3 requireOneByTest(string $test) Return the first ChildTable3 filtered by the test column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildTable3[]|Collection find(?ConnectionInterface $con = null) Return ChildTable3 objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildTable3> find(?ConnectionInterface $con = null) Return ChildTable3 objects based on current ModelCriteria
 *
 * @method     ChildTable3[]|Collection findById(int|array<int> $id) Return ChildTable3 objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildTable3> findById(int|array<int> $id) Return ChildTable3 objects filtered by the id column
 * @method     ChildTable3[]|Collection findByTitle(string|array<string> $title) Return ChildTable3 objects filtered by the title column
 * @psalm-method Collection&\Traversable<ChildTable3> findByTitle(string|array<string> $title) Return ChildTable3 objects filtered by the title column
 * @method     ChildTable3[]|Collection findByTest(string|array<string> $test) Return ChildTable3 objects filtered by the test column
 * @psalm-method Collection&\Traversable<ChildTable3> findByTest(string|array<string> $test) Return ChildTable3 objects filtered by the test column
 *
 * @method     ChildTable3[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildTable3> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class Table3Query extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\Table3Query object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\Table3', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildTable3Query object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildTable3Query
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof static) {
            return $criteria;
        }
        if ($criteria instanceof ChildTable3Query) {
            $query = $criteria;
        } else {
            $query = new static();
            if ($criteria instanceof Criteria) {
                $query->mergeWith($criteria);
            }
        }
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
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
     * @return ChildTable3|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(Table3TableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = Table3TableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildTable3 A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, test FROM table3 WHERE id = :p0';
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
            /** @var ChildTable3 $obj */
            $obj = new ChildTable3();
            $obj->hydrate($row);
            Table3TableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildTable3|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(Table3TableMap::COL_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(Table3TableMap::COL_ID, $keys, Criteria::IN);

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
                $this->addUsingAlias(Table3TableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(Table3TableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(Table3TableMap::COL_ID, $id, $comparison);

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

        $this->addUsingAlias(Table3TableMap::COL_TITLE, $title, $comparison);

        return $this;
    }

    /**
     * Filter the query on the test column
     *
     * Example usage:
     * <code>
     * $query->filterByTest('2011-03-14'); // WHERE test = '2011-03-14'
     * $query->filterByTest('now'); // WHERE test = '2011-03-14'
     * $query->filterByTest(array('max' => 'yesterday')); // WHERE test > '2011-03-13'
     * </code>
     *
     * @param mixed $test The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByTest($test = null, ?string $comparison = null)
    {
        if (is_array($test)) {
            $useMinMax = false;
            if (isset($test['min'])) {
                $this->addUsingAlias(Table3TableMap::COL_TEST, $test['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($test['max'])) {
                $this->addUsingAlias(Table3TableMap::COL_TEST, $test['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(Table3TableMap::COL_TEST, $test, $comparison);

        return $this;
    }

    /**
     * Exclude object from result
     *
     * @param ChildTable3 $table3 Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($table3 = null)
    {
        if ($table3) {
            $this->addUsingAlias(Table3TableMap::COL_ID, $table3->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Code to execute before every SELECT statement
     *
     * @param ConnectionInterface $con The connection object used by the query
     */
    protected function basePreSelect(ConnectionInterface $con): void
    {
        // Propel\Tests\Helpers\Bookstore\Behavior\Testallhooksbehavior behavior
        // foo

        $this->preSelect($con);
    }

    /**
     * Code to execute before every DELETE statement
     *
     * @param ConnectionInterface $con The connection object used by the query
     * @return int|null
     */
    protected function basePreDelete(ConnectionInterface $con): ?int
    {
        // Propel\Tests\Helpers\Bookstore\Behavior\Testallhooksbehavior behavior
        // foo

        return $this->preDelete($con);
    }

    /**
     * Code to execute after every DELETE statement
     *
     * @param int $affectedRows the number of deleted rows
     * @param ConnectionInterface $con The connection object used by the query
     * @return int|null
     */
    protected function basePostDelete(int $affectedRows, ConnectionInterface $con): ?int
    {
        // Propel\Tests\Helpers\Bookstore\Behavior\Testallhooksbehavior behavior
        // foo

        return $this->postDelete($affectedRows, $con);
    }

    /**
     * Code to execute before every UPDATE statement
     *
     * @param array $values The associative array of columns and values for the update
     * @param ConnectionInterface $con The connection object used by the query
     * @param bool $forceIndividualSaves If false (default), the resulting call is a Criteria::doUpdate(), otherwise it is a series of save() calls on all the found objects
     *
     * @return int|null
     */
    protected function basePreUpdate(&$values, ConnectionInterface $con, $forceIndividualSaves = false): ?int
    {
        // Propel\Tests\Helpers\Bookstore\Behavior\Testallhooksbehavior behavior
        // foo

        return $this->preUpdate($values, $con, $forceIndividualSaves);
    }

    /**
     * Code to execute after every UPDATE statement
     *
     * @param int $affectedRows the number of updated rows
     * @param ConnectionInterface $con The connection object used by the query
     *
     * @return int|null
     */
    protected function basePostUpdate($affectedRows, ConnectionInterface $con): ?int
    {
        // Propel\Tests\Helpers\Bookstore\Behavior\Testallhooksbehavior behavior
        // foo

        return $this->postUpdate($affectedRows, $con);
    }

    /**
     * Deletes all rows from the table3 table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(Table3TableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            Table3TableMap::clearInstancePool();
            Table3TableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(Table3TableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(Table3TableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            Table3TableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            Table3TableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // Propel\Tests\Helpers\Bookstore\Behavior\Testallhooksbehavior behavior
    static public $customStaticAttribute = 1;public static $staticAttributeBuilder = "Propel\Generator\Builder\Om\QueryBuilder";
    // Propel\Tests\Helpers\Bookstore\Behavior\Testallhooksbehavior behavior
    static public function hello() { return "Propel\Generator\Builder\Om\QueryBuilder"; }
}
class testQueryFilter { const FOO = "Propel\Generator\Builder\Om\QueryBuilder"; }
