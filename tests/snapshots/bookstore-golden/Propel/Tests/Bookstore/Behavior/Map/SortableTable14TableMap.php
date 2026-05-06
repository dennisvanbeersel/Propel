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
use Propel\Tests\Bookstore\Behavior\SortableTable14;
use Propel\Tests\Bookstore\Behavior\SortableTable14Query;


/**
 * This class defines the structure of the 'sortable_table14' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class SortableTable14TableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.SortableTable14TableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'sortable_table14';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'SortableTable14';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\SortableTable14';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.SortableTable14';

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
    public const COL_ID = 'sortable_table14.id';

    /**
     * the column name for the title field
     */
    public const COL_TITLE = 'sortable_table14.title';

    /**
     * the column name for the style2 field
     */
    public const COL_STYLE2 = 'sortable_table14.style2';

    /**
     * the column name for the sortable_rank field
     */
    public const COL_SORTABLE_RANK = 'sortable_table14.sortable_rank';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /** The enumerated values for the style2 field */
    public const COL_STYLE2_NOVEL = 'novel';
    public const COL_STYLE2_ESSAY = 'essay';

    // sortable behavior
    /**
     * rank column
     */
    const RANK_COL = "sortable_table14.sortable_rank";



    /**
    * Scope column for the set
    */
    const SCOPE_COL = 'sortable_table14.style2';


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'Title', 'Style2', 'SortableRank', ],
        self::TYPE_CAMELNAME     => ['id', 'title', 'style2', 'sortableRank', ],
        self::TYPE_COLNAME       => [SortableTable14TableMap::COL_ID, SortableTable14TableMap::COL_TITLE, SortableTable14TableMap::COL_STYLE2, SortableTable14TableMap::COL_SORTABLE_RANK, ],
        self::TYPE_FIELDNAME     => ['id', 'title', 'style2', 'sortable_rank', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Title' => 1, 'Style2' => 2, 'SortableRank' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'title' => 1, 'style2' => 2, 'sortableRank' => 3, ],
        self::TYPE_COLNAME       => [SortableTable14TableMap::COL_ID => 0, SortableTable14TableMap::COL_TITLE => 1, SortableTable14TableMap::COL_STYLE2 => 2, SortableTable14TableMap::COL_SORTABLE_RANK => 3, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'title' => 1, 'style2' => 2, 'sortable_rank' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'SortableTable14.Id' => 'ID',
        'id' => 'ID',
        'sortableTable14.id' => 'ID',
        'SortableTable14TableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'sortable_table14.id' => 'ID',
        'Title' => 'TITLE',
        'SortableTable14.Title' => 'TITLE',
        'title' => 'TITLE',
        'sortableTable14.title' => 'TITLE',
        'SortableTable14TableMap::COL_TITLE' => 'TITLE',
        'COL_TITLE' => 'TITLE',
        'sortable_table14.title' => 'TITLE',
        'Style2' => 'STYLE2',
        'SortableTable14.Style2' => 'STYLE2',
        'style2' => 'STYLE2',
        'sortableTable14.style2' => 'STYLE2',
        'SortableTable14TableMap::COL_STYLE2' => 'STYLE2',
        'COL_STYLE2' => 'STYLE2',
        'sortable_table14.style2' => 'STYLE2',
        'SortableRank' => 'SORTABLE_RANK',
        'SortableTable14.SortableRank' => 'SORTABLE_RANK',
        'sortableRank' => 'SORTABLE_RANK',
        'sortableTable14.sortableRank' => 'SORTABLE_RANK',
        'SortableTable14TableMap::COL_SORTABLE_RANK' => 'SORTABLE_RANK',
        'COL_SORTABLE_RANK' => 'SORTABLE_RANK',
        'sortable_rank' => 'SORTABLE_RANK',
        'sortable_table14.sortable_rank' => 'SORTABLE_RANK',
    ];

    /**
     * The enumerated values for this table
     *
     * @var array<string, array<string>>
     */
    protected static $enumValueSets = [
                SortableTable14TableMap::COL_STYLE2 => [
                            self::COL_STYLE2_NOVEL,
            self::COL_STYLE2_ESSAY,
        ],
    ];

    /**
     * Gets the list of values for all ENUM and SET columns
     * @return array
     */
    public static function getValueSets(): array
    {
      return static::$enumValueSets;
    }

    /**
     * Gets the list of values for an ENUM or SET column
     * @param string $colname
     * @return array list of possible values for the column
     */
    public static function getValueSet(string $colname): array
    {
        $valueSets = self::getValueSets();

        return $valueSets[$colname];
    }

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
        $this->setName('sortable_table14');
        $this->setPhpName('SortableTable14');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\SortableTable14');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('title', 'Title', 'VARCHAR', false, 100, null);
        $this->getColumn('title')->setPrimaryString(true);
        $this->addColumn('style2', 'Style2', 'SET', false, null, null);
        $this->getColumn('style2')->setValueSet(array (
  0 => 'novel',
  1 => 'essay',
));
        $this->addColumn('sortable_rank', 'SortableRank', 'INTEGER', false, null, null);
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
            'sortable' => ['rank_column' => 'sortable_rank', 'use_scope' => 'true', 'scope_column' => 'style2'],
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
        return $withPrefix ? SortableTable14TableMap::CLASS_DEFAULT : SortableTable14TableMap::OM_CLASS;
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
     * @return array (SortableTable14 object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = SortableTable14TableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = SortableTable14TableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + SortableTable14TableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = SortableTable14TableMap::OM_CLASS;
            /** @var SortableTable14 $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            SortableTable14TableMap::addInstanceToPool($obj, $key);
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
            $key = SortableTable14TableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = SortableTable14TableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var SortableTable14 $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                SortableTable14TableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(SortableTable14TableMap::COL_ID);
            $criteria->addSelectColumn(SortableTable14TableMap::COL_TITLE);
            $criteria->addSelectColumn(SortableTable14TableMap::COL_STYLE2);
            $criteria->addSelectColumn(SortableTable14TableMap::COL_SORTABLE_RANK);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.title');
            $criteria->addSelectColumn($alias . '.style2');
            $criteria->addSelectColumn($alias . '.sortable_rank');
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
            $criteria->removeSelectColumn(SortableTable14TableMap::COL_ID);
            $criteria->removeSelectColumn(SortableTable14TableMap::COL_TITLE);
            $criteria->removeSelectColumn(SortableTable14TableMap::COL_STYLE2);
            $criteria->removeSelectColumn(SortableTable14TableMap::COL_SORTABLE_RANK);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.title');
            $criteria->removeSelectColumn($alias . '.style2');
            $criteria->removeSelectColumn($alias . '.sortable_rank');
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
        return Propel::getServiceContainer()->getDatabaseMap(SortableTable14TableMap::DATABASE_NAME)->getTable(SortableTable14TableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a SortableTable14 or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or SortableTable14 object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\SortableTable14) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(SortableTable14TableMap::DATABASE_NAME);
            $criteria->add(SortableTable14TableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = SortableTable14Query::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            SortableTable14TableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                SortableTable14TableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the sortable_table14 table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return SortableTable14Query::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a SortableTable14 or Criteria object.
     *
     * @param mixed $criteria Criteria or SortableTable14 object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SortableTable14TableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from SortableTable14 object
        }

        if ($criteria->containsKey(SortableTable14TableMap::COL_ID) && $criteria->keyContainsValue(SortableTable14TableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.SortableTable14TableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = SortableTable14Query::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
