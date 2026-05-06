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
use Propel\Tests\Bookstore\Distribution;
use Propel\Tests\Bookstore\DistributionQuery;


/**
 * This class defines the structure of the 'distribution' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class DistributionTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.DistributionTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'distribution';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'Distribution';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Distribution';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 4;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 4;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'distribution.id';

    /**
     * the column name for the name field
     */
    public const COL_NAME = 'distribution.name';

    /**
     * the column name for the type field
     */
    public const COL_TYPE = 'distribution.type';

    /**
     * the column name for the distribution_manager_id field
     */
    public const COL_DISTRIBUTION_MANAGER_ID = 'distribution.distribution_manager_id';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /** A key representing a particular subclass */
    public const CLASSKEY_44 = '44';

    /** A key representing a particular subclass */
    public const CLASSKEY_DISTRIBUTIONSTORE = '\\Propel\\Tests\\Bookstore\\DistributionStore';

    /** A class that can be returned by this tableMap. */
    public const CLASSNAME_44 = '\\Propel\\Tests\\Bookstore\\DistributionStore';

    /** A key representing a particular subclass */
    public const CLASSKEY_23 = '23';

    /** A key representing a particular subclass */
    public const CLASSKEY_DISTRIBUTIONONLINE = '\\Propel\\Tests\\Bookstore\\DistributionOnline';

    /** A class that can be returned by this tableMap. */
    public const CLASSNAME_23 = '\\Propel\\Tests\\Bookstore\\DistributionOnline';

    /** A key representing a particular subclass */
    public const CLASSKEY_3838 = '3838';

    /** A key representing a particular subclass */
    public const CLASSKEY_DISTRIBUTIONVIRTUALSTORE = '\\Propel\\Tests\\Bookstore\\DistributionVirtualStore';

    /** A class that can be returned by this tableMap. */
    public const CLASSNAME_3838 = '\\Propel\\Tests\\Bookstore\\DistributionVirtualStore';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'Name', 'Type', 'DistributionManagerId', ],
        self::TYPE_CAMELNAME     => ['id', 'name', 'type', 'distributionManagerId', ],
        self::TYPE_COLNAME       => [DistributionTableMap::COL_ID, DistributionTableMap::COL_NAME, DistributionTableMap::COL_TYPE, DistributionTableMap::COL_DISTRIBUTION_MANAGER_ID, ],
        self::TYPE_FIELDNAME     => ['id', 'name', 'type', 'distribution_manager_id', ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Name' => 1, 'Type' => 2, 'DistributionManagerId' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'name' => 1, 'type' => 2, 'distributionManagerId' => 3, ],
        self::TYPE_COLNAME       => [DistributionTableMap::COL_ID => 0, DistributionTableMap::COL_NAME => 1, DistributionTableMap::COL_TYPE => 2, DistributionTableMap::COL_DISTRIBUTION_MANAGER_ID => 3, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'name' => 1, 'type' => 2, 'distribution_manager_id' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'Distribution.Id' => 'ID',
        'id' => 'ID',
        'distribution.id' => 'ID',
        'DistributionTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'Name' => 'NAME',
        'Distribution.Name' => 'NAME',
        'name' => 'NAME',
        'distribution.name' => 'NAME',
        'DistributionTableMap::COL_NAME' => 'NAME',
        'COL_NAME' => 'NAME',
        'Type' => 'TYPE',
        'Distribution.Type' => 'TYPE',
        'type' => 'TYPE',
        'distribution.type' => 'TYPE',
        'DistributionTableMap::COL_TYPE' => 'TYPE',
        'COL_TYPE' => 'TYPE',
        'DistributionManagerId' => 'DISTRIBUTION_MANAGER_ID',
        'Distribution.DistributionManagerId' => 'DISTRIBUTION_MANAGER_ID',
        'distributionManagerId' => 'DISTRIBUTION_MANAGER_ID',
        'distribution.distributionManagerId' => 'DISTRIBUTION_MANAGER_ID',
        'DistributionTableMap::COL_DISTRIBUTION_MANAGER_ID' => 'DISTRIBUTION_MANAGER_ID',
        'COL_DISTRIBUTION_MANAGER_ID' => 'DISTRIBUTION_MANAGER_ID',
        'distribution_manager_id' => 'DISTRIBUTION_MANAGER_ID',
        'distribution.distribution_manager_id' => 'DISTRIBUTION_MANAGER_ID',
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
        $this->setName('distribution');
        $this->setPhpName('Distribution');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Distribution');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(true);
        $this->setSingleTableInheritance(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('name', 'Name', 'VARCHAR', false, 255, null);
        $this->addColumn('type', 'Type', 'INTEGER', true, null, 0);
        $this->addForeignKey('distribution_manager_id', 'DistributionManagerId', 'INTEGER', 'distribution_manager', 'id', true, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('DistributionManager', '\\Propel\\Tests\\Bookstore\\DistributionManager', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':distribution_manager_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
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
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
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
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The returned Class will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param array $row ConnectionInterface result row.
     * @param int $colNum Column to examine for OM class information (first is 0).
     * @param bool $withPrefix Whether to return the path with the class name
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     *
     * @return string The OM class
     */
    public static function getOMClass(array $row, int $colNum, bool $withPrefix = true): string
    {
        try {

            $omClass = null;
            $classKey = $row[$colNum + 2];

            switch ($classKey) {

                case DistributionTableMap::CLASSKEY_44:
                    $omClass = DistributionTableMap::CLASSNAME_44;
                    break;

                case DistributionTableMap::CLASSKEY_23:
                    $omClass = DistributionTableMap::CLASSNAME_23;
                    break;

                case DistributionTableMap::CLASSKEY_3838:
                    $omClass = DistributionTableMap::CLASSNAME_3838;
                    break;

                default:
                    $omClass = $withPrefix
                        ? DistributionTableMap::CLASS_DEFAULT
                        : DistributionTableMap::OM_CLASS;

            } // switch
            if (!$withPrefix) {
                $omClass = preg_replace('#\.#', '\\', $omClass);
            }

        } catch (\Exception $e) {
            throw new PropelException('Unable to get OM class.', 0, $e);
        }

        return $omClass;
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
     * @return array (Distribution object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = DistributionTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = DistributionTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + DistributionTableMap::NUM_HYDRATE_COLUMNS;
        } elseif (null === $key) {
            // empty resultset, probably from a left join
            // since this table is abstract, we can't hydrate an empty object
            $obj = null;
            $col = $offset + DistributionTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = static::getOMClass($row, $offset, false);
            /** @var Distribution $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            DistributionTableMap::addInstanceToPool($obj, $key);
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

        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = DistributionTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = DistributionTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                // class must be set each time from the record row
                $cls = static::getOMClass($row, 0);
                $cls = preg_replace('#\.#', '\\', $cls);
                /** @var Distribution $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                DistributionTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(DistributionTableMap::COL_ID);
            $criteria->addSelectColumn(DistributionTableMap::COL_NAME);
            $criteria->addSelectColumn(DistributionTableMap::COL_TYPE);
            $criteria->addSelectColumn(DistributionTableMap::COL_DISTRIBUTION_MANAGER_ID);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.name');
            $criteria->addSelectColumn($alias . '.type');
            $criteria->addSelectColumn($alias . '.distribution_manager_id');
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
            $criteria->removeSelectColumn(DistributionTableMap::COL_ID);
            $criteria->removeSelectColumn(DistributionTableMap::COL_NAME);
            $criteria->removeSelectColumn(DistributionTableMap::COL_TYPE);
            $criteria->removeSelectColumn(DistributionTableMap::COL_DISTRIBUTION_MANAGER_ID);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.name');
            $criteria->removeSelectColumn($alias . '.type');
            $criteria->removeSelectColumn($alias . '.distribution_manager_id');
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
        return Propel::getServiceContainer()->getDatabaseMap(DistributionTableMap::DATABASE_NAME)->getTable(DistributionTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a Distribution or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or Distribution object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(DistributionTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Distribution) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(DistributionTableMap::DATABASE_NAME);
            $criteria->add(DistributionTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = DistributionQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            DistributionTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                DistributionTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the distribution table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return DistributionQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Distribution or Criteria object.
     *
     * @param mixed $criteria Criteria or Distribution object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(DistributionTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Distribution object
        }

        if ($criteria->containsKey(DistributionTableMap::COL_ID) && $criteria->keyContainsValue(DistributionTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.DistributionTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = DistributionQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
