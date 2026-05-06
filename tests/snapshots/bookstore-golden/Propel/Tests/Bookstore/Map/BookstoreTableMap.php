<?php

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
use Propel\Tests\Bookstore\Bookstore;
use Propel\Tests\Bookstore\BookstoreQuery;


/**
 * This class defines the structure of the 'bookstore' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class BookstoreTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.BookstoreTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'bookstore';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'Bookstore';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Bookstore';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Bookstore';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 7;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 7;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'bookstore.id';

    /**
     * the column name for the store_name field
     */
    public const COL_STORE_NAME = 'bookstore.store_name';

    /**
     * the column name for the location field
     */
    public const COL_LOCATION = 'bookstore.location';

    /**
     * the column name for the population_served field
     */
    public const COL_POPULATION_SERVED = 'bookstore.population_served';

    /**
     * the column name for the total_books field
     */
    public const COL_TOTAL_BOOKS = 'bookstore.total_books';

    /**
     * the column name for the store_open_time field
     */
    public const COL_STORE_OPEN_TIME = 'bookstore.store_open_time';

    /**
     * the column name for the website field
     */
    public const COL_WEBSITE = 'bookstore.website';

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
        self::TYPE_PHPNAME       => ['Id', 'StoreName', 'Location', 'PopulationServed', 'TotalBooks', 'StoreOpenTime', 'Website', ],
        self::TYPE_CAMELNAME     => ['id', 'storeName', 'location', 'populationServed', 'totalBooks', 'storeOpenTime', 'website', ],
        self::TYPE_COLNAME       => [BookstoreTableMap::COL_ID, BookstoreTableMap::COL_STORE_NAME, BookstoreTableMap::COL_LOCATION, BookstoreTableMap::COL_POPULATION_SERVED, BookstoreTableMap::COL_TOTAL_BOOKS, BookstoreTableMap::COL_STORE_OPEN_TIME, BookstoreTableMap::COL_WEBSITE, ],
        self::TYPE_FIELDNAME     => ['id', 'store_name', 'location', 'population_served', 'total_books', 'store_open_time', 'website', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'StoreName' => 1, 'Location' => 2, 'PopulationServed' => 3, 'TotalBooks' => 4, 'StoreOpenTime' => 5, 'Website' => 6, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'storeName' => 1, 'location' => 2, 'populationServed' => 3, 'totalBooks' => 4, 'storeOpenTime' => 5, 'website' => 6, ],
        self::TYPE_COLNAME       => [BookstoreTableMap::COL_ID => 0, BookstoreTableMap::COL_STORE_NAME => 1, BookstoreTableMap::COL_LOCATION => 2, BookstoreTableMap::COL_POPULATION_SERVED => 3, BookstoreTableMap::COL_TOTAL_BOOKS => 4, BookstoreTableMap::COL_STORE_OPEN_TIME => 5, BookstoreTableMap::COL_WEBSITE => 6, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'store_name' => 1, 'location' => 2, 'population_served' => 3, 'total_books' => 4, 'store_open_time' => 5, 'website' => 6, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'Bookstore.Id' => 'ID',
        'id' => 'ID',
        'bookstore.id' => 'ID',
        'BookstoreTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'StoreName' => 'STORE_NAME',
        'Bookstore.StoreName' => 'STORE_NAME',
        'storeName' => 'STORE_NAME',
        'bookstore.storeName' => 'STORE_NAME',
        'BookstoreTableMap::COL_STORE_NAME' => 'STORE_NAME',
        'COL_STORE_NAME' => 'STORE_NAME',
        'store_name' => 'STORE_NAME',
        'bookstore.store_name' => 'STORE_NAME',
        'Location' => 'LOCATION',
        'Bookstore.Location' => 'LOCATION',
        'location' => 'LOCATION',
        'bookstore.location' => 'LOCATION',
        'BookstoreTableMap::COL_LOCATION' => 'LOCATION',
        'COL_LOCATION' => 'LOCATION',
        'PopulationServed' => 'POPULATION_SERVED',
        'Bookstore.PopulationServed' => 'POPULATION_SERVED',
        'populationServed' => 'POPULATION_SERVED',
        'bookstore.populationServed' => 'POPULATION_SERVED',
        'BookstoreTableMap::COL_POPULATION_SERVED' => 'POPULATION_SERVED',
        'COL_POPULATION_SERVED' => 'POPULATION_SERVED',
        'population_served' => 'POPULATION_SERVED',
        'bookstore.population_served' => 'POPULATION_SERVED',
        'TotalBooks' => 'TOTAL_BOOKS',
        'Bookstore.TotalBooks' => 'TOTAL_BOOKS',
        'totalBooks' => 'TOTAL_BOOKS',
        'bookstore.totalBooks' => 'TOTAL_BOOKS',
        'BookstoreTableMap::COL_TOTAL_BOOKS' => 'TOTAL_BOOKS',
        'COL_TOTAL_BOOKS' => 'TOTAL_BOOKS',
        'total_books' => 'TOTAL_BOOKS',
        'bookstore.total_books' => 'TOTAL_BOOKS',
        'StoreOpenTime' => 'STORE_OPEN_TIME',
        'Bookstore.StoreOpenTime' => 'STORE_OPEN_TIME',
        'storeOpenTime' => 'STORE_OPEN_TIME',
        'bookstore.storeOpenTime' => 'STORE_OPEN_TIME',
        'BookstoreTableMap::COL_STORE_OPEN_TIME' => 'STORE_OPEN_TIME',
        'COL_STORE_OPEN_TIME' => 'STORE_OPEN_TIME',
        'store_open_time' => 'STORE_OPEN_TIME',
        'bookstore.store_open_time' => 'STORE_OPEN_TIME',
        'Website' => 'WEBSITE',
        'Bookstore.Website' => 'WEBSITE',
        'website' => 'WEBSITE',
        'bookstore.website' => 'WEBSITE',
        'BookstoreTableMap::COL_WEBSITE' => 'WEBSITE',
        'COL_WEBSITE' => 'WEBSITE',
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
        $this->setName('bookstore');
        $this->setPhpName('Bookstore');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Bookstore');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('store_name', 'StoreName', 'VARCHAR', true, 50, null);
        $this->addColumn('location', 'Location', 'VARCHAR', false, 100, null);
        $this->addColumn('population_served', 'PopulationServed', 'BIGINT', false, null, null);
        $this->addColumn('total_books', 'TotalBooks', 'INTEGER', false, null, null);
        $this->addColumn('store_open_time', 'StoreOpenTime', 'TIME', false, null, null);
        $this->addColumn('website', 'Website', 'VARCHAR', false, 255, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('BookstoreSale', '\\Propel\\Tests\\Bookstore\\BookstoreSale', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':bookstore_id',
    1 => ':id',
  ),
), 'CASCADE', null, 'BookstoreSales', false);
        $this->addRelation('BookstoreContest', '\\Propel\\Tests\\Bookstore\\BookstoreContest', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':bookstore_id',
    1 => ':id',
  ),
), 'CASCADE', null, 'BookstoreContests', false);
        $this->addRelation('BookstoreContestEntry', '\\Propel\\Tests\\Bookstore\\BookstoreContestEntry', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':bookstore_id',
    1 => ':id',
  ),
), 'CASCADE', null, 'BookstoreContestEntries', false);
    }

    /**
     * Method to invalidate the instance pool of all tables related to bookstore     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        BookstoreSaleTableMap::clearInstancePool();
        BookstoreContestTableMap::clearInstancePool();
        BookstoreContestEntryTableMap::clearInstancePool();
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
        return $withPrefix ? BookstoreTableMap::CLASS_DEFAULT : BookstoreTableMap::OM_CLASS;
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
     * @return array (Bookstore object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = BookstoreTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = BookstoreTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + BookstoreTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = BookstoreTableMap::OM_CLASS;
            /** @var Bookstore $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            BookstoreTableMap::addInstanceToPool($obj, $key);
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
            $key = BookstoreTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = BookstoreTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Bookstore $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                BookstoreTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(BookstoreTableMap::COL_ID);
            $criteria->addSelectColumn(BookstoreTableMap::COL_STORE_NAME);
            $criteria->addSelectColumn(BookstoreTableMap::COL_LOCATION);
            $criteria->addSelectColumn(BookstoreTableMap::COL_POPULATION_SERVED);
            $criteria->addSelectColumn(BookstoreTableMap::COL_TOTAL_BOOKS);
            $criteria->addSelectColumn(BookstoreTableMap::COL_STORE_OPEN_TIME);
            $criteria->addSelectColumn(BookstoreTableMap::COL_WEBSITE);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.store_name');
            $criteria->addSelectColumn($alias . '.location');
            $criteria->addSelectColumn($alias . '.population_served');
            $criteria->addSelectColumn($alias . '.total_books');
            $criteria->addSelectColumn($alias . '.store_open_time');
            $criteria->addSelectColumn($alias . '.website');
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
            $criteria->removeSelectColumn(BookstoreTableMap::COL_ID);
            $criteria->removeSelectColumn(BookstoreTableMap::COL_STORE_NAME);
            $criteria->removeSelectColumn(BookstoreTableMap::COL_LOCATION);
            $criteria->removeSelectColumn(BookstoreTableMap::COL_POPULATION_SERVED);
            $criteria->removeSelectColumn(BookstoreTableMap::COL_TOTAL_BOOKS);
            $criteria->removeSelectColumn(BookstoreTableMap::COL_STORE_OPEN_TIME);
            $criteria->removeSelectColumn(BookstoreTableMap::COL_WEBSITE);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.store_name');
            $criteria->removeSelectColumn($alias . '.location');
            $criteria->removeSelectColumn($alias . '.population_served');
            $criteria->removeSelectColumn($alias . '.total_books');
            $criteria->removeSelectColumn($alias . '.store_open_time');
            $criteria->removeSelectColumn($alias . '.website');
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
        return Propel::getServiceContainer()->getDatabaseMap(BookstoreTableMap::DATABASE_NAME)->getTable(BookstoreTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a Bookstore or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or Bookstore object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Bookstore) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(BookstoreTableMap::DATABASE_NAME);
            $criteria->add(BookstoreTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = BookstoreQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            BookstoreTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                BookstoreTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the bookstore table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return BookstoreQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Bookstore or Criteria object.
     *
     * @param mixed $criteria Criteria or Bookstore object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Bookstore object
        }

        if ($criteria->containsKey(BookstoreTableMap::COL_ID) && $criteria->keyContainsValue(BookstoreTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.BookstoreTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = BookstoreQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
