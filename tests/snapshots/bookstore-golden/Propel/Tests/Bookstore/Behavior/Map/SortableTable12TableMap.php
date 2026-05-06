<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Map;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;
use Propel\Tests\Bookstore\Behavior\SortableTable12;
use Propel\Tests\Bookstore\Behavior\SortableTable12Query;


/**
 * This class defines the structure of the 'sortable_table12' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class SortableTable12TableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.SortableTable12TableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'sortable_table12';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'SortableTable12';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\SortableTable12';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.SortableTable12';

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
    public const COL_ID = 'sortable_table12.id';

    /**
     * the column name for the title field
     */
    public const COL_TITLE = 'sortable_table12.title';

    /**
     * the column name for the position field
     */
    public const COL_POSITION = 'sortable_table12.position';

    /**
     * the column name for the my_scope_column field
     */
    public const COL_MY_SCOPE_COLUMN = 'sortable_table12.my_scope_column';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    // sortable behavior
    /**
     * rank column
     */
    const RANK_COL = "sortable_table12.position";



    /**
    * Scope column for the set
    */
    const SCOPE_COL = 'sortable_table12.my_scope_column';


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'Title', 'Position', 'MyScopeColumn', ],
        self::TYPE_CAMELNAME     => ['id', 'title', 'position', 'myScopeColumn', ],
        self::TYPE_COLNAME       => [SortableTable12TableMap::COL_ID, SortableTable12TableMap::COL_TITLE, SortableTable12TableMap::COL_POSITION, SortableTable12TableMap::COL_MY_SCOPE_COLUMN, ],
        self::TYPE_FIELDNAME     => ['id', 'title', 'position', 'my_scope_column', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Title' => 1, 'Position' => 2, 'MyScopeColumn' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'title' => 1, 'position' => 2, 'myScopeColumn' => 3, ],
        self::TYPE_COLNAME       => [SortableTable12TableMap::COL_ID => 0, SortableTable12TableMap::COL_TITLE => 1, SortableTable12TableMap::COL_POSITION => 2, SortableTable12TableMap::COL_MY_SCOPE_COLUMN => 3, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'title' => 1, 'position' => 2, 'my_scope_column' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'SortableTable12.Id' => 'ID',
        'id' => 'ID',
        'sortableTable12.id' => 'ID',
        'SortableTable12TableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'sortable_table12.id' => 'ID',
        'Title' => 'TITLE',
        'SortableTable12.Title' => 'TITLE',
        'title' => 'TITLE',
        'sortableTable12.title' => 'TITLE',
        'SortableTable12TableMap::COL_TITLE' => 'TITLE',
        'COL_TITLE' => 'TITLE',
        'sortable_table12.title' => 'TITLE',
        'Position' => 'POSITION',
        'SortableTable12.Position' => 'POSITION',
        'position' => 'POSITION',
        'sortableTable12.position' => 'POSITION',
        'SortableTable12TableMap::COL_POSITION' => 'POSITION',
        'COL_POSITION' => 'POSITION',
        'sortable_table12.position' => 'POSITION',
        'MyScopeColumn' => 'MY_SCOPE_COLUMN',
        'SortableTable12.MyScopeColumn' => 'MY_SCOPE_COLUMN',
        'myScopeColumn' => 'MY_SCOPE_COLUMN',
        'sortableTable12.myScopeColumn' => 'MY_SCOPE_COLUMN',
        'SortableTable12TableMap::COL_MY_SCOPE_COLUMN' => 'MY_SCOPE_COLUMN',
        'COL_MY_SCOPE_COLUMN' => 'MY_SCOPE_COLUMN',
        'my_scope_column' => 'MY_SCOPE_COLUMN',
        'sortable_table12.my_scope_column' => 'MY_SCOPE_COLUMN',
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
        $this->setName('sortable_table12');
        $this->setPhpName('SortableTable12');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\SortableTable12');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('title', 'Title', 'VARCHAR', false, 100, null);
        $this->getColumn('title')->setPrimaryString(true);
        $this->addColumn('position', 'Position', 'INTEGER', false, null, null);
        $this->addColumn('my_scope_column', 'MyScopeColumn', 'INTEGER', false, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
    }

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array<string, array> Associative array (name => parameters) of behaviors
     */
    public function getBehaviors(): array
    {
        return [
            'sortable' => ['rank_column' => 'position', 'use_scope' => 'true', 'scope_column' => 'my_scope_column'],
        ];
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
        return $withPrefix ? SortableTable12TableMap::CLASS_DEFAULT : SortableTable12TableMap::OM_CLASS;
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
     * @return array (SortableTable12 object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = SortableTable12TableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = SortableTable12TableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + SortableTable12TableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = SortableTable12TableMap::OM_CLASS;
            /** @var SortableTable12 $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            SortableTable12TableMap::addInstanceToPool($obj, $key);
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
            $key = SortableTable12TableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = SortableTable12TableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var SortableTable12 $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                SortableTable12TableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(SortableTable12TableMap::COL_ID);
            $criteria->addSelectColumn(SortableTable12TableMap::COL_TITLE);
            $criteria->addSelectColumn(SortableTable12TableMap::COL_POSITION);
            $criteria->addSelectColumn(SortableTable12TableMap::COL_MY_SCOPE_COLUMN);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.title');
            $criteria->addSelectColumn($alias . '.position');
            $criteria->addSelectColumn($alias . '.my_scope_column');
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
            $criteria->removeSelectColumn(SortableTable12TableMap::COL_ID);
            $criteria->removeSelectColumn(SortableTable12TableMap::COL_TITLE);
            $criteria->removeSelectColumn(SortableTable12TableMap::COL_POSITION);
            $criteria->removeSelectColumn(SortableTable12TableMap::COL_MY_SCOPE_COLUMN);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.title');
            $criteria->removeSelectColumn($alias . '.position');
            $criteria->removeSelectColumn($alias . '.my_scope_column');
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
        return Propel::getServiceContainer()->getDatabaseMap(SortableTable12TableMap::DATABASE_NAME)->getTable(SortableTable12TableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a SortableTable12 or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or SortableTable12 object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(SortableTable12TableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\SortableTable12) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(SortableTable12TableMap::DATABASE_NAME);
            $criteria->add(SortableTable12TableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = SortableTable12Query::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            SortableTable12TableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                SortableTable12TableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the sortable_table12 table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return SortableTable12Query::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a SortableTable12 or Criteria object.
     *
     * @param mixed $criteria Criteria or SortableTable12 object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SortableTable12TableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from SortableTable12 object
        }

        if ($criteria->containsKey(SortableTable12TableMap::COL_ID) && $criteria->keyContainsValue(SortableTable12TableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.SortableTable12TableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = SortableTable12Query::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
