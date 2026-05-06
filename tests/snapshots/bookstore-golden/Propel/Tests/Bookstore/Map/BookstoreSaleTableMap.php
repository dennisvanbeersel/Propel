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
use Propel\Tests\Bookstore\BookstoreSale;
use Propel\Tests\Bookstore\BookstoreSaleQuery;


/**
 * This class defines the structure of the 'bookstore_sale' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class BookstoreSaleTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.BookstoreSaleTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'bookstore_sale';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'BookstoreSale';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\BookstoreSale';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.BookstoreSale';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 5;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 5;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'bookstore_sale.id';

    /**
     * the column name for the bookstore_id field
     */
    public const COL_BOOKSTORE_ID = 'bookstore_sale.bookstore_id';

    /**
     * the column name for the publisher_id field
     */
    public const COL_PUBLISHER_ID = 'bookstore_sale.publisher_id';

    /**
     * the column name for the sale_name field
     */
    public const COL_SALE_NAME = 'bookstore_sale.sale_name';

    /**
     * the column name for the discount field
     */
    public const COL_DISCOUNT = 'bookstore_sale.discount';

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
        self::TYPE_PHPNAME       => ['Id', 'BookstoreId', 'PublisherId', 'SaleName', 'Discount', ],
        self::TYPE_CAMELNAME     => ['id', 'bookstoreId', 'publisherId', 'saleName', 'discount', ],
        self::TYPE_COLNAME       => [BookstoreSaleTableMap::COL_ID, BookstoreSaleTableMap::COL_BOOKSTORE_ID, BookstoreSaleTableMap::COL_PUBLISHER_ID, BookstoreSaleTableMap::COL_SALE_NAME, BookstoreSaleTableMap::COL_DISCOUNT, ],
        self::TYPE_FIELDNAME     => ['id', 'bookstore_id', 'publisher_id', 'sale_name', 'discount', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'BookstoreId' => 1, 'PublisherId' => 2, 'SaleName' => 3, 'Discount' => 4, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'bookstoreId' => 1, 'publisherId' => 2, 'saleName' => 3, 'discount' => 4, ],
        self::TYPE_COLNAME       => [BookstoreSaleTableMap::COL_ID => 0, BookstoreSaleTableMap::COL_BOOKSTORE_ID => 1, BookstoreSaleTableMap::COL_PUBLISHER_ID => 2, BookstoreSaleTableMap::COL_SALE_NAME => 3, BookstoreSaleTableMap::COL_DISCOUNT => 4, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'bookstore_id' => 1, 'publisher_id' => 2, 'sale_name' => 3, 'discount' => 4, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'BookstoreSale.Id' => 'ID',
        'id' => 'ID',
        'bookstoreSale.id' => 'ID',
        'BookstoreSaleTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'bookstore_sale.id' => 'ID',
        'BookstoreId' => 'BOOKSTORE_ID',
        'BookstoreSale.BookstoreId' => 'BOOKSTORE_ID',
        'bookstoreId' => 'BOOKSTORE_ID',
        'bookstoreSale.bookstoreId' => 'BOOKSTORE_ID',
        'BookstoreSaleTableMap::COL_BOOKSTORE_ID' => 'BOOKSTORE_ID',
        'COL_BOOKSTORE_ID' => 'BOOKSTORE_ID',
        'bookstore_id' => 'BOOKSTORE_ID',
        'bookstore_sale.bookstore_id' => 'BOOKSTORE_ID',
        'PublisherId' => 'PUBLISHER_ID',
        'BookstoreSale.PublisherId' => 'PUBLISHER_ID',
        'publisherId' => 'PUBLISHER_ID',
        'bookstoreSale.publisherId' => 'PUBLISHER_ID',
        'BookstoreSaleTableMap::COL_PUBLISHER_ID' => 'PUBLISHER_ID',
        'COL_PUBLISHER_ID' => 'PUBLISHER_ID',
        'publisher_id' => 'PUBLISHER_ID',
        'bookstore_sale.publisher_id' => 'PUBLISHER_ID',
        'SaleName' => 'SALE_NAME',
        'BookstoreSale.SaleName' => 'SALE_NAME',
        'saleName' => 'SALE_NAME',
        'bookstoreSale.saleName' => 'SALE_NAME',
        'BookstoreSaleTableMap::COL_SALE_NAME' => 'SALE_NAME',
        'COL_SALE_NAME' => 'SALE_NAME',
        'sale_name' => 'SALE_NAME',
        'bookstore_sale.sale_name' => 'SALE_NAME',
        'Discount' => 'DISCOUNT',
        'BookstoreSale.Discount' => 'DISCOUNT',
        'discount' => 'DISCOUNT',
        'bookstoreSale.discount' => 'DISCOUNT',
        'BookstoreSaleTableMap::COL_DISCOUNT' => 'DISCOUNT',
        'COL_DISCOUNT' => 'DISCOUNT',
        'bookstore_sale.discount' => 'DISCOUNT',
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
        $this->setName('bookstore_sale');
        $this->setPhpName('BookstoreSale');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\BookstoreSale');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('bookstore_id', 'BookstoreId', 'INTEGER', 'bookstore', 'id', false, null, 1);
        $this->addForeignKey('publisher_id', 'PublisherId', 'INTEGER', 'publisher', 'id', false, null, null);
        $this->addColumn('sale_name', 'SaleName', 'VARCHAR', false, 100, null);
        $this->addColumn('discount', 'Discount', 'TINYINT', false, null, 10);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('Bookstore', '\\Propel\\Tests\\Bookstore\\Bookstore', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':bookstore_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('Publisher', '\\Propel\\Tests\\Bookstore\\Publisher', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':publisher_id',
    1 => ':id',
  ),
), 'SET NULL', null, null, false);
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
        return $withPrefix ? BookstoreSaleTableMap::CLASS_DEFAULT : BookstoreSaleTableMap::OM_CLASS;
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
     * @return array (BookstoreSale object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = BookstoreSaleTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = BookstoreSaleTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + BookstoreSaleTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = BookstoreSaleTableMap::OM_CLASS;
            /** @var BookstoreSale $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            BookstoreSaleTableMap::addInstanceToPool($obj, $key);
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
            $key = BookstoreSaleTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = BookstoreSaleTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var BookstoreSale $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                BookstoreSaleTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(BookstoreSaleTableMap::COL_ID);
            $criteria->addSelectColumn(BookstoreSaleTableMap::COL_BOOKSTORE_ID);
            $criteria->addSelectColumn(BookstoreSaleTableMap::COL_PUBLISHER_ID);
            $criteria->addSelectColumn(BookstoreSaleTableMap::COL_SALE_NAME);
            $criteria->addSelectColumn(BookstoreSaleTableMap::COL_DISCOUNT);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.bookstore_id');
            $criteria->addSelectColumn($alias . '.publisher_id');
            $criteria->addSelectColumn($alias . '.sale_name');
            $criteria->addSelectColumn($alias . '.discount');
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
            $criteria->removeSelectColumn(BookstoreSaleTableMap::COL_ID);
            $criteria->removeSelectColumn(BookstoreSaleTableMap::COL_BOOKSTORE_ID);
            $criteria->removeSelectColumn(BookstoreSaleTableMap::COL_PUBLISHER_ID);
            $criteria->removeSelectColumn(BookstoreSaleTableMap::COL_SALE_NAME);
            $criteria->removeSelectColumn(BookstoreSaleTableMap::COL_DISCOUNT);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.bookstore_id');
            $criteria->removeSelectColumn($alias . '.publisher_id');
            $criteria->removeSelectColumn($alias . '.sale_name');
            $criteria->removeSelectColumn($alias . '.discount');
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
        return Propel::getServiceContainer()->getDatabaseMap(BookstoreSaleTableMap::DATABASE_NAME)->getTable(BookstoreSaleTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a BookstoreSale or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or BookstoreSale object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreSaleTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\BookstoreSale) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(BookstoreSaleTableMap::DATABASE_NAME);
            $criteria->add(BookstoreSaleTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = BookstoreSaleQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            BookstoreSaleTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                BookstoreSaleTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the bookstore_sale table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return BookstoreSaleQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a BookstoreSale or Criteria object.
     *
     * @param mixed $criteria Criteria or BookstoreSale object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreSaleTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from BookstoreSale object
        }

        if ($criteria->containsKey(BookstoreSaleTableMap::COL_ID) && $criteria->keyContainsValue(BookstoreSaleTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.BookstoreSaleTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = BookstoreSaleQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
