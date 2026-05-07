<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Map;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;
use Propel\Tests\Bookstore\BookstoreEmployeeAccount;
use Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery;


/**
 * This class defines the structure of the 'bookstore_employee_account' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class BookstoreEmployeeAccountTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.BookstoreEmployeeAccountTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'bookstore_employee_account';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'BookstoreEmployeeAccount';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\BookstoreEmployeeAccount';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.BookstoreEmployeeAccount';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 9;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 9;

    /**
     * the column name for the employee_id field
     */
    public const COL_EMPLOYEE_ID = 'bookstore_employee_account.employee_id';

    /**
     * the column name for the login field
     */
    public const COL_LOGIN = 'bookstore_employee_account.login';

    /**
     * the column name for the password field
     */
    public const COL_PASSWORD = 'bookstore_employee_account.password';

    /**
     * the column name for the enabled field
     */
    public const COL_ENABLED = 'bookstore_employee_account.enabled';

    /**
     * the column name for the not_enabled field
     */
    public const COL_NOT_ENABLED = 'bookstore_employee_account.not_enabled';

    /**
     * the column name for the created field
     */
    public const COL_CREATED = 'bookstore_employee_account.created';

    /**
     * the column name for the updated field
     */
    public const COL_UPDATED = 'bookstore_employee_account.updated';

    /**
     * the column name for the role_id field
     */
    public const COL_ROLE_ID = 'bookstore_employee_account.role_id';

    /**
     * the column name for the authenticator field
     */
    public const COL_AUTHENTICATOR = 'bookstore_employee_account.authenticator';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['EmployeeId', 'Login', 'Password', 'Enabled', 'NotEnabled', 'Created', 'Updated', 'RoleId', 'Authenticator', ],
        self::TYPE_CAMELNAME     => ['employeeId', 'login', 'password', 'enabled', 'notEnabled', 'created', 'updated', 'roleId', 'authenticator', ],
        self::TYPE_COLNAME       => [BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, BookstoreEmployeeAccountTableMap::COL_LOGIN, BookstoreEmployeeAccountTableMap::COL_PASSWORD, BookstoreEmployeeAccountTableMap::COL_ENABLED, BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED, BookstoreEmployeeAccountTableMap::COL_CREATED, BookstoreEmployeeAccountTableMap::COL_UPDATED, BookstoreEmployeeAccountTableMap::COL_ROLE_ID, BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR, ],
        self::TYPE_FIELDNAME     => ['employee_id', 'login', 'password', 'enabled', 'not_enabled', 'created', 'updated', 'role_id', 'authenticator', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, ]
    ];

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     *
     * @var array<string, mixed>
     */
    protected static $fieldKeys = [
        self::TYPE_PHPNAME       => ['EmployeeId' => 0, 'Login' => 1, 'Password' => 2, 'Enabled' => 3, 'NotEnabled' => 4, 'Created' => 5, 'Updated' => 6, 'RoleId' => 7, 'Authenticator' => 8, ],
        self::TYPE_CAMELNAME     => ['employeeId' => 0, 'login' => 1, 'password' => 2, 'enabled' => 3, 'notEnabled' => 4, 'created' => 5, 'updated' => 6, 'roleId' => 7, 'authenticator' => 8, ],
        self::TYPE_COLNAME       => [BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID => 0, BookstoreEmployeeAccountTableMap::COL_LOGIN => 1, BookstoreEmployeeAccountTableMap::COL_PASSWORD => 2, BookstoreEmployeeAccountTableMap::COL_ENABLED => 3, BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED => 4, BookstoreEmployeeAccountTableMap::COL_CREATED => 5, BookstoreEmployeeAccountTableMap::COL_UPDATED => 6, BookstoreEmployeeAccountTableMap::COL_ROLE_ID => 7, BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR => 8, ],
        self::TYPE_FIELDNAME     => ['employee_id' => 0, 'login' => 1, 'password' => 2, 'enabled' => 3, 'not_enabled' => 4, 'created' => 5, 'updated' => 6, 'role_id' => 7, 'authenticator' => 8, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'EmployeeId' => 'EMPLOYEE_ID',
        'BookstoreEmployeeAccount.EmployeeId' => 'EMPLOYEE_ID',
        'employeeId' => 'EMPLOYEE_ID',
        'bookstoreEmployeeAccount.employeeId' => 'EMPLOYEE_ID',
        'BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID' => 'EMPLOYEE_ID',
        'COL_EMPLOYEE_ID' => 'EMPLOYEE_ID',
        'employee_id' => 'EMPLOYEE_ID',
        'bookstore_employee_account.employee_id' => 'EMPLOYEE_ID',
        'Login' => 'LOGIN',
        'BookstoreEmployeeAccount.Login' => 'LOGIN',
        'login' => 'LOGIN',
        'bookstoreEmployeeAccount.login' => 'LOGIN',
        'BookstoreEmployeeAccountTableMap::COL_LOGIN' => 'LOGIN',
        'COL_LOGIN' => 'LOGIN',
        'bookstore_employee_account.login' => 'LOGIN',
        'Password' => 'PASSWORD',
        'BookstoreEmployeeAccount.Password' => 'PASSWORD',
        'password' => 'PASSWORD',
        'bookstoreEmployeeAccount.password' => 'PASSWORD',
        'BookstoreEmployeeAccountTableMap::COL_PASSWORD' => 'PASSWORD',
        'COL_PASSWORD' => 'PASSWORD',
        'bookstore_employee_account.password' => 'PASSWORD',
        'Enabled' => 'ENABLED',
        'BookstoreEmployeeAccount.Enabled' => 'ENABLED',
        'enabled' => 'ENABLED',
        'bookstoreEmployeeAccount.enabled' => 'ENABLED',
        'BookstoreEmployeeAccountTableMap::COL_ENABLED' => 'ENABLED',
        'COL_ENABLED' => 'ENABLED',
        'bookstore_employee_account.enabled' => 'ENABLED',
        'NotEnabled' => 'NOT_ENABLED',
        'BookstoreEmployeeAccount.NotEnabled' => 'NOT_ENABLED',
        'notEnabled' => 'NOT_ENABLED',
        'bookstoreEmployeeAccount.notEnabled' => 'NOT_ENABLED',
        'BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED' => 'NOT_ENABLED',
        'COL_NOT_ENABLED' => 'NOT_ENABLED',
        'not_enabled' => 'NOT_ENABLED',
        'bookstore_employee_account.not_enabled' => 'NOT_ENABLED',
        'Created' => 'CREATED',
        'BookstoreEmployeeAccount.Created' => 'CREATED',
        'created' => 'CREATED',
        'bookstoreEmployeeAccount.created' => 'CREATED',
        'BookstoreEmployeeAccountTableMap::COL_CREATED' => 'CREATED',
        'COL_CREATED' => 'CREATED',
        'bookstore_employee_account.created' => 'CREATED',
        'Updated' => 'UPDATED',
        'BookstoreEmployeeAccount.Updated' => 'UPDATED',
        'updated' => 'UPDATED',
        'bookstoreEmployeeAccount.updated' => 'UPDATED',
        'BookstoreEmployeeAccountTableMap::COL_UPDATED' => 'UPDATED',
        'COL_UPDATED' => 'UPDATED',
        'bookstore_employee_account.updated' => 'UPDATED',
        'RoleId' => 'ROLE_ID',
        'BookstoreEmployeeAccount.RoleId' => 'ROLE_ID',
        'roleId' => 'ROLE_ID',
        'bookstoreEmployeeAccount.roleId' => 'ROLE_ID',
        'BookstoreEmployeeAccountTableMap::COL_ROLE_ID' => 'ROLE_ID',
        'COL_ROLE_ID' => 'ROLE_ID',
        'role_id' => 'ROLE_ID',
        'bookstore_employee_account.role_id' => 'ROLE_ID',
        'Authenticator' => 'AUTHENTICATOR',
        'BookstoreEmployeeAccount.Authenticator' => 'AUTHENTICATOR',
        'authenticator' => 'AUTHENTICATOR',
        'bookstoreEmployeeAccount.authenticator' => 'AUTHENTICATOR',
        'BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR' => 'AUTHENTICATOR',
        'COL_AUTHENTICATOR' => 'AUTHENTICATOR',
        'bookstore_employee_account.authenticator' => 'AUTHENTICATOR',
    ];

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function initialize(): void
    {
        // attributes
        $this->setName('bookstore_employee_account');
        $this->setPhpName('BookstoreEmployeeAccount');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\BookstoreEmployeeAccount');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(false);
        // columns
        $this->addForeignPrimaryKey('employee_id', 'EmployeeId', 'INTEGER' , 'bookstore_employee', 'id', true, null, null);
        $this->addColumn('login', 'Login', 'VARCHAR', false, 32, null);
        $this->addColumn('password', 'Password', 'VARCHAR', false, 100, '\'@\'\'34"');
        $this->addColumn('enabled', 'Enabled', 'BOOLEAN', false, null, true);
        $this->addColumn('not_enabled', 'NotEnabled', 'BOOLEAN', false, null, false);
        $this->addColumn('created', 'Created', 'TIMESTAMP', false, null, 'CURRENT_TIMESTAMP');
        $this->addColumn('updated', 'Updated', 'TIMESTAMP', false, null, 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        $this->addForeignKey('role_id', 'RoleId', 'INTEGER', 'acct_access_role', 'id', false, null, null);
        $this->addColumn('authenticator', 'Authenticator', 'VARCHAR', false, 32, '\'Password\'');
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('BookstoreEmployee', '\\Propel\\Tests\\Bookstore\\BookstoreEmployee', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':employee_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('AcctAccessRole', '\\Propel\\Tests\\Bookstore\\AcctAccessRole', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':role_id',
    1 => ':id',
  ),
), 'SET NULL', null, null, false);
        $this->addRelation('AcctAuditLog', '\\Propel\\Tests\\Bookstore\\AcctAuditLog', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':uid',
    1 => ':login',
  ),
), 'CASCADE', null, 'AcctAuditLogs', false);
    }

    /**
     * Method to invalidate the instance pool of all tables related to bookstore_employee_account     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        AcctAuditLogTableMap::clearInstancePool();
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string|null The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): ?string
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param bool $withPrefix Whether to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass(bool $withPrefix = true): string
    {
        return $withPrefix ? BookstoreEmployeeAccountTableMap::CLASS_DEFAULT : BookstoreEmployeeAccountTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array $row Row returned by DataFetcher->fetch().
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array (BookstoreEmployeeAccount object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = BookstoreEmployeeAccountTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = BookstoreEmployeeAccountTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + BookstoreEmployeeAccountTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = BookstoreEmployeeAccountTableMap::OM_CLASS;
            /** @var BookstoreEmployeeAccount $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            BookstoreEmployeeAccountTableMap::addInstanceToPool($obj, $key);
        }

        return [$obj, $col];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array<object>
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher): array
    {
        $results = [];

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = BookstoreEmployeeAccountTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = BookstoreEmployeeAccountTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var BookstoreEmployeeAccount $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                BookstoreEmployeeAccountTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria Object containing the columns to add.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function addSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_LOGIN);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_PASSWORD);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_ENABLED);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_CREATED);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_UPDATED);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_ROLE_ID);
            $criteria->addSelectColumn(BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR);
        } else {
            $criteria->addSelectColumn($alias . '.employee_id');
            $criteria->addSelectColumn($alias . '.login');
            $criteria->addSelectColumn($alias . '.password');
            $criteria->addSelectColumn($alias . '.enabled');
            $criteria->addSelectColumn($alias . '.not_enabled');
            $criteria->addSelectColumn($alias . '.created');
            $criteria->addSelectColumn($alias . '.updated');
            $criteria->addSelectColumn($alias . '.role_id');
            $criteria->addSelectColumn($alias . '.authenticator');
        }
    }

    /**
     * Remove all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be removed as they are only loaded on demand.
     *
     * @param Criteria $criteria Object containing the columns to remove.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function removeSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_LOGIN);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_PASSWORD);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_ENABLED);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_CREATED);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_UPDATED);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_ROLE_ID);
            $criteria->removeSelectColumn(BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR);
        } else {
            $criteria->removeSelectColumn($alias . '.employee_id');
            $criteria->removeSelectColumn($alias . '.login');
            $criteria->removeSelectColumn($alias . '.password');
            $criteria->removeSelectColumn($alias . '.enabled');
            $criteria->removeSelectColumn($alias . '.not_enabled');
            $criteria->removeSelectColumn($alias . '.created');
            $criteria->removeSelectColumn($alias . '.updated');
            $criteria->removeSelectColumn($alias . '.role_id');
            $criteria->removeSelectColumn($alias . '.authenticator');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap(): TableMap
    {
        return Propel::getServiceContainer()->getDatabaseMap(BookstoreEmployeeAccountTableMap::DATABASE_NAME)->getTable(BookstoreEmployeeAccountTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a BookstoreEmployeeAccount or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or BookstoreEmployeeAccount object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?ConnectionInterface $con = null): int
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\BookstoreEmployeeAccount) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, (array) $values, Criteria::IN);
        }

        $query = BookstoreEmployeeAccountQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            BookstoreEmployeeAccountTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                BookstoreEmployeeAccountTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the bookstore_employee_account table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return BookstoreEmployeeAccountQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a BookstoreEmployeeAccount or Criteria object.
     *
     * @param mixed $criteria Criteria or BookstoreEmployeeAccount object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from BookstoreEmployeeAccount object
        }


        // Set the correct dbName
        $query = BookstoreEmployeeAccountQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
