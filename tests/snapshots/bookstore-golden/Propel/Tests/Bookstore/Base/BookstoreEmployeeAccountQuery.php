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
use Propel\Tests\Bookstore\BookstoreEmployeeAccount as ChildBookstoreEmployeeAccount;
use Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery as ChildBookstoreEmployeeAccountQuery;
use Propel\Tests\Bookstore\Map\BookstoreEmployeeAccountTableMap;

/**
 * Base class that represents a query for the `bookstore_employee_account` table.
 *
 * Bookstore employees login credentials.
 *
 * @method     ChildBookstoreEmployeeAccountQuery orderByEmployeeId($order = Criteria::ASC) Order by the employee_id column
 * @method     ChildBookstoreEmployeeAccountQuery orderByLogin($order = Criteria::ASC) Order by the login column
 * @method     ChildBookstoreEmployeeAccountQuery orderByPassword($order = Criteria::ASC) Order by the password column
 * @method     ChildBookstoreEmployeeAccountQuery orderByEnabled($order = Criteria::ASC) Order by the enabled column
 * @method     ChildBookstoreEmployeeAccountQuery orderByNotEnabled($order = Criteria::ASC) Order by the not_enabled column
 * @method     ChildBookstoreEmployeeAccountQuery orderByCreated($order = Criteria::ASC) Order by the created column
 * @method     ChildBookstoreEmployeeAccountQuery orderByUpdated($order = Criteria::ASC) Order by the updated column
 * @method     ChildBookstoreEmployeeAccountQuery orderByRoleId($order = Criteria::ASC) Order by the role_id column
 * @method     ChildBookstoreEmployeeAccountQuery orderByAuthenticator($order = Criteria::ASC) Order by the authenticator column
 *
 * @method     ChildBookstoreEmployeeAccountQuery groupByEmployeeId() Group by the employee_id column
 * @method     ChildBookstoreEmployeeAccountQuery groupByLogin() Group by the login column
 * @method     ChildBookstoreEmployeeAccountQuery groupByPassword() Group by the password column
 * @method     ChildBookstoreEmployeeAccountQuery groupByEnabled() Group by the enabled column
 * @method     ChildBookstoreEmployeeAccountQuery groupByNotEnabled() Group by the not_enabled column
 * @method     ChildBookstoreEmployeeAccountQuery groupByCreated() Group by the created column
 * @method     ChildBookstoreEmployeeAccountQuery groupByUpdated() Group by the updated column
 * @method     ChildBookstoreEmployeeAccountQuery groupByRoleId() Group by the role_id column
 * @method     ChildBookstoreEmployeeAccountQuery groupByAuthenticator() Group by the authenticator column
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildBookstoreEmployeeAccountQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildBookstoreEmployeeAccountQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildBookstoreEmployeeAccountQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildBookstoreEmployeeAccountQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoinBookstoreEmployee($relationAlias = null) Adds a LEFT JOIN clause to the query using the BookstoreEmployee relation
 * @method     ChildBookstoreEmployeeAccountQuery rightJoinBookstoreEmployee($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BookstoreEmployee relation
 * @method     ChildBookstoreEmployeeAccountQuery innerJoinBookstoreEmployee($relationAlias = null) Adds a INNER JOIN clause to the query using the BookstoreEmployee relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery joinWithBookstoreEmployee($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the BookstoreEmployee relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoinWithBookstoreEmployee() Adds a LEFT JOIN clause and with to the query using the BookstoreEmployee relation
 * @method     ChildBookstoreEmployeeAccountQuery rightJoinWithBookstoreEmployee() Adds a RIGHT JOIN clause and with to the query using the BookstoreEmployee relation
 * @method     ChildBookstoreEmployeeAccountQuery innerJoinWithBookstoreEmployee() Adds a INNER JOIN clause and with to the query using the BookstoreEmployee relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoinAcctAccessRole($relationAlias = null) Adds a LEFT JOIN clause to the query using the AcctAccessRole relation
 * @method     ChildBookstoreEmployeeAccountQuery rightJoinAcctAccessRole($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AcctAccessRole relation
 * @method     ChildBookstoreEmployeeAccountQuery innerJoinAcctAccessRole($relationAlias = null) Adds a INNER JOIN clause to the query using the AcctAccessRole relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery joinWithAcctAccessRole($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the AcctAccessRole relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoinWithAcctAccessRole() Adds a LEFT JOIN clause and with to the query using the AcctAccessRole relation
 * @method     ChildBookstoreEmployeeAccountQuery rightJoinWithAcctAccessRole() Adds a RIGHT JOIN clause and with to the query using the AcctAccessRole relation
 * @method     ChildBookstoreEmployeeAccountQuery innerJoinWithAcctAccessRole() Adds a INNER JOIN clause and with to the query using the AcctAccessRole relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoinAcctAuditLog($relationAlias = null) Adds a LEFT JOIN clause to the query using the AcctAuditLog relation
 * @method     ChildBookstoreEmployeeAccountQuery rightJoinAcctAuditLog($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AcctAuditLog relation
 * @method     ChildBookstoreEmployeeAccountQuery innerJoinAcctAuditLog($relationAlias = null) Adds a INNER JOIN clause to the query using the AcctAuditLog relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery joinWithAcctAuditLog($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the AcctAuditLog relation
 *
 * @method     ChildBookstoreEmployeeAccountQuery leftJoinWithAcctAuditLog() Adds a LEFT JOIN clause and with to the query using the AcctAuditLog relation
 * @method     ChildBookstoreEmployeeAccountQuery rightJoinWithAcctAuditLog() Adds a RIGHT JOIN clause and with to the query using the AcctAuditLog relation
 * @method     ChildBookstoreEmployeeAccountQuery innerJoinWithAcctAuditLog() Adds a INNER JOIN clause and with to the query using the AcctAuditLog relation
 *
 * @method     \Propel\Tests\Bookstore\BookstoreEmployeeQuery|\Propel\Tests\Bookstore\AcctAccessRoleQuery|\Propel\Tests\Bookstore\AcctAuditLogQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildBookstoreEmployeeAccount|null findOne(?ConnectionInterface $con = null) Return the first ChildBookstoreEmployeeAccount matching the query
 * @method     ChildBookstoreEmployeeAccount findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildBookstoreEmployeeAccount matching the query, or a new ChildBookstoreEmployeeAccount object populated from the query conditions when no match is found
 *
 * @method     ChildBookstoreEmployeeAccount|null findOneByEmployeeId(int $employee_id) Return the first ChildBookstoreEmployeeAccount filtered by the employee_id column
 * @method     ChildBookstoreEmployeeAccount|null findOneByLogin(string $login) Return the first ChildBookstoreEmployeeAccount filtered by the login column
 * @method     ChildBookstoreEmployeeAccount|null findOneByPassword(string $password) Return the first ChildBookstoreEmployeeAccount filtered by the password column
 * @method     ChildBookstoreEmployeeAccount|null findOneByEnabled(bool $enabled) Return the first ChildBookstoreEmployeeAccount filtered by the enabled column
 * @method     ChildBookstoreEmployeeAccount|null findOneByNotEnabled(bool $not_enabled) Return the first ChildBookstoreEmployeeAccount filtered by the not_enabled column
 * @method     ChildBookstoreEmployeeAccount|null findOneByCreated(string $created) Return the first ChildBookstoreEmployeeAccount filtered by the created column
 * @method     ChildBookstoreEmployeeAccount|null findOneByUpdated(string $updated) Return the first ChildBookstoreEmployeeAccount filtered by the updated column
 * @method     ChildBookstoreEmployeeAccount|null findOneByRoleId(int $role_id) Return the first ChildBookstoreEmployeeAccount filtered by the role_id column
 * @method     ChildBookstoreEmployeeAccount|null findOneByAuthenticator(string $authenticator) Return the first ChildBookstoreEmployeeAccount filtered by the authenticator column
 *
 * @method     ChildBookstoreEmployeeAccount requirePk($key, ?ConnectionInterface $con = null) Return the ChildBookstoreEmployeeAccount by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOne(?ConnectionInterface $con = null) Return the first ChildBookstoreEmployeeAccount matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookstoreEmployeeAccount requireOneByEmployeeId(int $employee_id) Return the first ChildBookstoreEmployeeAccount filtered by the employee_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByLogin(string $login) Return the first ChildBookstoreEmployeeAccount filtered by the login column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByPassword(string $password) Return the first ChildBookstoreEmployeeAccount filtered by the password column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByEnabled(bool $enabled) Return the first ChildBookstoreEmployeeAccount filtered by the enabled column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByNotEnabled(bool $not_enabled) Return the first ChildBookstoreEmployeeAccount filtered by the not_enabled column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByCreated(string $created) Return the first ChildBookstoreEmployeeAccount filtered by the created column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByUpdated(string $updated) Return the first ChildBookstoreEmployeeAccount filtered by the updated column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByRoleId(int $role_id) Return the first ChildBookstoreEmployeeAccount filtered by the role_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBookstoreEmployeeAccount requireOneByAuthenticator(string $authenticator) Return the first ChildBookstoreEmployeeAccount filtered by the authenticator column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBookstoreEmployeeAccount[]|Collection find(?ConnectionInterface $con = null) Return ChildBookstoreEmployeeAccount objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> find(?ConnectionInterface $con = null) Return ChildBookstoreEmployeeAccount objects based on current ModelCriteria
 *
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByEmployeeId(int|array<int> $employee_id) Return ChildBookstoreEmployeeAccount objects filtered by the employee_id column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByEmployeeId(int|array<int> $employee_id) Return ChildBookstoreEmployeeAccount objects filtered by the employee_id column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByLogin(string|array<string> $login) Return ChildBookstoreEmployeeAccount objects filtered by the login column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByLogin(string|array<string> $login) Return ChildBookstoreEmployeeAccount objects filtered by the login column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByPassword(string|array<string> $password) Return ChildBookstoreEmployeeAccount objects filtered by the password column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByPassword(string|array<string> $password) Return ChildBookstoreEmployeeAccount objects filtered by the password column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByEnabled(bool|array<bool> $enabled) Return ChildBookstoreEmployeeAccount objects filtered by the enabled column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByEnabled(bool|array<bool> $enabled) Return ChildBookstoreEmployeeAccount objects filtered by the enabled column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByNotEnabled(bool|array<bool> $not_enabled) Return ChildBookstoreEmployeeAccount objects filtered by the not_enabled column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByNotEnabled(bool|array<bool> $not_enabled) Return ChildBookstoreEmployeeAccount objects filtered by the not_enabled column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByCreated(string|array<string> $created) Return ChildBookstoreEmployeeAccount objects filtered by the created column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByCreated(string|array<string> $created) Return ChildBookstoreEmployeeAccount objects filtered by the created column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByUpdated(string|array<string> $updated) Return ChildBookstoreEmployeeAccount objects filtered by the updated column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByUpdated(string|array<string> $updated) Return ChildBookstoreEmployeeAccount objects filtered by the updated column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByRoleId(int|array<int> $role_id) Return ChildBookstoreEmployeeAccount objects filtered by the role_id column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByRoleId(int|array<int> $role_id) Return ChildBookstoreEmployeeAccount objects filtered by the role_id column
 * @method     ChildBookstoreEmployeeAccount[]|Collection findByAuthenticator(string|array<string> $authenticator) Return ChildBookstoreEmployeeAccount objects filtered by the authenticator column
 * @psalm-method Collection&\Traversable<ChildBookstoreEmployeeAccount> findByAuthenticator(string|array<string> $authenticator) Return ChildBookstoreEmployeeAccount objects filtered by the authenticator column
 *
 * @method     ChildBookstoreEmployeeAccount[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildBookstoreEmployeeAccount> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class BookstoreEmployeeAccountQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\BookstoreEmployeeAccountQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\BookstoreEmployeeAccount', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildBookstoreEmployeeAccountQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildBookstoreEmployeeAccountQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof static) {
            return $criteria;
        }
        if ($criteria instanceof ChildBookstoreEmployeeAccountQuery) {
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
     * @return ChildBookstoreEmployeeAccount|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = BookstoreEmployeeAccountTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildBookstoreEmployeeAccount A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT employee_id, login, password, enabled, not_enabled, created, updated, role_id, authenticator FROM bookstore_employee_account WHERE employee_id = :p0';
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
            /** @var ChildBookstoreEmployeeAccount $obj */
            $obj = new ChildBookstoreEmployeeAccount();
            $obj->hydrate($row);
            BookstoreEmployeeAccountTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildBookstoreEmployeeAccount|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the employee_id column
     *
     * Example usage:
     * <code>
     * $query->filterByEmployeeId(1234); // WHERE employee_id = 1234
     * $query->filterByEmployeeId(array(12, 34)); // WHERE employee_id IN (12, 34)
     * $query->filterByEmployeeId(array('min' => 12)); // WHERE employee_id > 12
     * </code>
     *
     * @see       filterByBookstoreEmployee()
     *
     * @param mixed $employeeId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByEmployeeId($employeeId = null, ?string $comparison = null)
    {
        if (is_array($employeeId)) {
            $useMinMax = false;
            if (isset($employeeId['min'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $employeeId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($employeeId['max'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $employeeId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $employeeId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the login column
     *
     * Example usage:
     * <code>
     * $query->filterByLogin('fooValue');   // WHERE login = 'fooValue'
     * $query->filterByLogin('%fooValue%', Criteria::LIKE); // WHERE login LIKE '%fooValue%'
     * $query->filterByLogin(['foo', 'bar']); // WHERE login IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $login The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByLogin($login = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($login)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_LOGIN, $login, $comparison);

        return $this;
    }

    /**
     * Filter the query on the password column
     *
     * Example usage:
     * <code>
     * $query->filterByPassword('fooValue');   // WHERE password = 'fooValue'
     * $query->filterByPassword('%fooValue%', Criteria::LIKE); // WHERE password LIKE '%fooValue%'
     * $query->filterByPassword(['foo', 'bar']); // WHERE password IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $password The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPassword($password = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($password)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_PASSWORD, $password, $comparison);

        return $this;
    }

    /**
     * Filter the query on the enabled column
     *
     * Example usage:
     * <code>
     * $query->filterByEnabled(true); // WHERE enabled = true
     * $query->filterByEnabled('yes'); // WHERE enabled = true
     * </code>
     *
     * @param bool|string $enabled The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByEnabled($enabled = null, ?string $comparison = null)
    {
        if (is_string($enabled)) {
            $enabled = in_array(strtolower($enabled), array('false', 'off', '-', 'no', 'n', '0', ''), true) ? false : true;
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_ENABLED, $enabled, $comparison);

        return $this;
    }

    /**
     * Filter the query on the not_enabled column
     *
     * Example usage:
     * <code>
     * $query->filterByNotEnabled(true); // WHERE not_enabled = true
     * $query->filterByNotEnabled('yes'); // WHERE not_enabled = true
     * </code>
     *
     * @param bool|string $notEnabled The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByNotEnabled($notEnabled = null, ?string $comparison = null)
    {
        if (is_string($notEnabled)) {
            $notEnabled = in_array(strtolower($notEnabled), array('false', 'off', '-', 'no', 'n', '0', ''), true) ? false : true;
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED, $notEnabled, $comparison);

        return $this;
    }

    /**
     * Filter the query on the created column
     *
     * Example usage:
     * <code>
     * $query->filterByCreated('2011-03-14'); // WHERE created = '2011-03-14'
     * $query->filterByCreated('now'); // WHERE created = '2011-03-14'
     * $query->filterByCreated(array('max' => 'yesterday')); // WHERE created > '2011-03-13'
     * </code>
     *
     * @param mixed $created The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCreated($created = null, ?string $comparison = null)
    {
        if (is_array($created)) {
            $useMinMax = false;
            if (isset($created['min'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_CREATED, $created['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($created['max'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_CREATED, $created['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_CREATED, $created, $comparison);

        return $this;
    }

    /**
     * Filter the query on the updated column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdated('2011-03-14'); // WHERE updated = '2011-03-14'
     * $query->filterByUpdated('now'); // WHERE updated = '2011-03-14'
     * $query->filterByUpdated(array('max' => 'yesterday')); // WHERE updated > '2011-03-13'
     * </code>
     *
     * @param mixed $updated The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByUpdated($updated = null, ?string $comparison = null)
    {
        if (is_array($updated)) {
            $useMinMax = false;
            if (isset($updated['min'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_UPDATED, $updated['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updated['max'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_UPDATED, $updated['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_UPDATED, $updated, $comparison);

        return $this;
    }

    /**
     * Filter the query on the role_id column
     *
     * Example usage:
     * <code>
     * $query->filterByRoleId(1234); // WHERE role_id = 1234
     * $query->filterByRoleId(array(12, 34)); // WHERE role_id IN (12, 34)
     * $query->filterByRoleId(array('min' => 12)); // WHERE role_id > 12
     * </code>
     *
     * @see       filterByAcctAccessRole()
     *
     * @param mixed $roleId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByRoleId($roleId = null, ?string $comparison = null)
    {
        if (is_array($roleId)) {
            $useMinMax = false;
            if (isset($roleId['min'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_ROLE_ID, $roleId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($roleId['max'])) {
                $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_ROLE_ID, $roleId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_ROLE_ID, $roleId, $comparison);

        return $this;
    }

    /**
     * Filter the query on the authenticator column
     *
     * Example usage:
     * <code>
     * $query->filterByAuthenticator('fooValue');   // WHERE authenticator = 'fooValue'
     * $query->filterByAuthenticator('%fooValue%', Criteria::LIKE); // WHERE authenticator LIKE '%fooValue%'
     * $query->filterByAuthenticator(['foo', 'bar']); // WHERE authenticator IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $authenticator The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAuthenticator($authenticator = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($authenticator)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR, $authenticator, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\BookstoreEmployee object
     *
     * @param \Propel\Tests\Bookstore\BookstoreEmployee|ObjectCollection $bookstoreEmployee The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByBookstoreEmployee($bookstoreEmployee, ?string $comparison = null)
    {
        if ($bookstoreEmployee instanceof \Propel\Tests\Bookstore\BookstoreEmployee) {
            return $this
                ->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $bookstoreEmployee->getId(), $comparison);
        } elseif ($bookstoreEmployee instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $bookstoreEmployee->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByBookstoreEmployee() only accepts arguments of type \Propel\Tests\Bookstore\BookstoreEmployee or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BookstoreEmployee relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinBookstoreEmployee(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BookstoreEmployee');

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
            $this->addJoinObject($join, 'BookstoreEmployee');
        }

        return $this;
    }

    /**
     * Use the BookstoreEmployee relation BookstoreEmployee object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeQuery A secondary query class using the current class as primary query
     */
    public function useBookstoreEmployeeQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBookstoreEmployee($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BookstoreEmployee', '\Propel\Tests\Bookstore\BookstoreEmployeeQuery');
    }

    /**
     * Use the BookstoreEmployee relation BookstoreEmployee object
     *
     * @param callable(\Propel\Tests\Bookstore\BookstoreEmployeeQuery):\Propel\Tests\Bookstore\BookstoreEmployeeQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withBookstoreEmployeeQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useBookstoreEmployeeQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to BookstoreEmployee table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeQuery The inner query object of the EXISTS statement
     */
    public function useBookstoreEmployeeExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeQuery */
        $q = $this->useExistsQuery('BookstoreEmployee', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to BookstoreEmployee table for a NOT EXISTS query.
     *
     * @see useBookstoreEmployeeExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeQuery The inner query object of the NOT EXISTS statement
     */
    public function useBookstoreEmployeeNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeQuery */
        $q = $this->useExistsQuery('BookstoreEmployee', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to BookstoreEmployee table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeQuery The inner query object of the IN statement
     */
    public function useInBookstoreEmployeeQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeQuery */
        $q = $this->useInQuery('BookstoreEmployee', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to BookstoreEmployee table for a NOT IN query.
     *
     * @see useBookstoreEmployeeInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeQuery The inner query object of the NOT IN statement
     */
    public function useNotInBookstoreEmployeeQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\BookstoreEmployeeQuery */
        $q = $this->useInQuery('BookstoreEmployee', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\AcctAccessRole object
     *
     * @param \Propel\Tests\Bookstore\AcctAccessRole|ObjectCollection $acctAccessRole The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAcctAccessRole($acctAccessRole, ?string $comparison = null)
    {
        if ($acctAccessRole instanceof \Propel\Tests\Bookstore\AcctAccessRole) {
            return $this
                ->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_ROLE_ID, $acctAccessRole->getId(), $comparison);
        } elseif ($acctAccessRole instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_ROLE_ID, $acctAccessRole->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByAcctAccessRole() only accepts arguments of type \Propel\Tests\Bookstore\AcctAccessRole or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AcctAccessRole relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinAcctAccessRole(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AcctAccessRole');

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
            $this->addJoinObject($join, 'AcctAccessRole');
        }

        return $this;
    }

    /**
     * Use the AcctAccessRole relation AcctAccessRole object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\AcctAccessRoleQuery A secondary query class using the current class as primary query
     */
    public function useAcctAccessRoleQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAcctAccessRole($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AcctAccessRole', '\Propel\Tests\Bookstore\AcctAccessRoleQuery');
    }

    /**
     * Use the AcctAccessRole relation AcctAccessRole object
     *
     * @param callable(\Propel\Tests\Bookstore\AcctAccessRoleQuery):\Propel\Tests\Bookstore\AcctAccessRoleQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withAcctAccessRoleQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useAcctAccessRoleQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to AcctAccessRole table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\AcctAccessRoleQuery The inner query object of the EXISTS statement
     */
    public function useAcctAccessRoleExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAccessRoleQuery */
        $q = $this->useExistsQuery('AcctAccessRole', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to AcctAccessRole table for a NOT EXISTS query.
     *
     * @see useAcctAccessRoleExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AcctAccessRoleQuery The inner query object of the NOT EXISTS statement
     */
    public function useAcctAccessRoleNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAccessRoleQuery */
        $q = $this->useExistsQuery('AcctAccessRole', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to AcctAccessRole table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\AcctAccessRoleQuery The inner query object of the IN statement
     */
    public function useInAcctAccessRoleQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAccessRoleQuery */
        $q = $this->useInQuery('AcctAccessRole', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to AcctAccessRole table for a NOT IN query.
     *
     * @see useAcctAccessRoleInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AcctAccessRoleQuery The inner query object of the NOT IN statement
     */
    public function useNotInAcctAccessRoleQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAccessRoleQuery */
        $q = $this->useInQuery('AcctAccessRole', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\AcctAuditLog object
     *
     * @param \Propel\Tests\Bookstore\AcctAuditLog|ObjectCollection $acctAuditLog the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAcctAuditLog($acctAuditLog, ?string $comparison = null)
    {
        if ($acctAuditLog instanceof \Propel\Tests\Bookstore\AcctAuditLog) {
            $this
                ->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_LOGIN, $acctAuditLog->getUid(), $comparison);

            return $this;
        } elseif ($acctAuditLog instanceof ObjectCollection) {
            $this
                ->useAcctAuditLogQuery()
                ->filterByPrimaryKeys($acctAuditLog->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByAcctAuditLog() only accepts arguments of type \Propel\Tests\Bookstore\AcctAuditLog or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AcctAuditLog relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinAcctAuditLog(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AcctAuditLog');

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
            $this->addJoinObject($join, 'AcctAuditLog');
        }

        return $this;
    }

    /**
     * Use the AcctAuditLog relation AcctAuditLog object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\AcctAuditLogQuery A secondary query class using the current class as primary query
     */
    public function useAcctAuditLogQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinAcctAuditLog($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AcctAuditLog', '\Propel\Tests\Bookstore\AcctAuditLogQuery');
    }

    /**
     * Use the AcctAuditLog relation AcctAuditLog object
     *
     * @param callable(\Propel\Tests\Bookstore\AcctAuditLogQuery):\Propel\Tests\Bookstore\AcctAuditLogQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withAcctAuditLogQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useAcctAuditLogQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to AcctAuditLog table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\AcctAuditLogQuery The inner query object of the EXISTS statement
     */
    public function useAcctAuditLogExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAuditLogQuery */
        $q = $this->useExistsQuery('AcctAuditLog', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to AcctAuditLog table for a NOT EXISTS query.
     *
     * @see useAcctAuditLogExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AcctAuditLogQuery The inner query object of the NOT EXISTS statement
     */
    public function useAcctAuditLogNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAuditLogQuery */
        $q = $this->useExistsQuery('AcctAuditLog', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to AcctAuditLog table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\AcctAuditLogQuery The inner query object of the IN statement
     */
    public function useInAcctAuditLogQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAuditLogQuery */
        $q = $this->useInQuery('AcctAuditLog', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to AcctAuditLog table for a NOT IN query.
     *
     * @see useAcctAuditLogInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\AcctAuditLogQuery The inner query object of the NOT IN statement
     */
    public function useNotInAcctAuditLogQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\AcctAuditLogQuery */
        $q = $this->useInQuery('AcctAuditLog', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildBookstoreEmployeeAccount $bookstoreEmployeeAccount Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($bookstoreEmployeeAccount = null)
    {
        if ($bookstoreEmployeeAccount) {
            $this->addUsingAlias(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $bookstoreEmployeeAccount->getEmployeeId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the bookstore_employee_account table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BookstoreEmployeeAccountTableMap::clearInstancePool();
            BookstoreEmployeeAccountTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(BookstoreEmployeeAccountTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            BookstoreEmployeeAccountTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            BookstoreEmployeeAccountTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
