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
use Propel\Tests\Bookstore\Book2;
use Propel\Tests\Bookstore\Book2Query;


/**
 * This class defines the structure of the 'book2' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class Book2TableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.Book2TableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'book2';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'Book2';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Book2';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Book2';

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
    public const COL_ID = 'book2.id';

    /**
     * the column name for the title field
     */
    public const COL_TITLE = 'book2.title';

    /**
     * the column name for the style field
     */
    public const COL_STYLE = 'book2.style';

    /**
     * the column name for the style2 field
     */
    public const COL_STYLE2 = 'book2.style2';

    /**
     * the column name for the tags field
     */
    public const COL_TAGS = 'book2.tags';

    /**
     * the column name for the uuid field
     */
    public const COL_UUID = 'book2.uuid';

    /**
     * the column name for the uuid_bin field
     */
    public const COL_UUID_BIN = 'book2.uuid_bin';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /** The enumerated values for the style field */
    public const COL_STYLE_NOVEL = 'novel';
    public const COL_STYLE_ESSAY = 'essay';
    public const COL_STYLE_POETRY = 'poetry';

    /** The enumerated values for the style2 field */
    public const COL_STYLE2_NOVEL = 'novel';
    public const COL_STYLE2_ESSAY = 'essay';
    public const COL_STYLE2_POETRY = 'poetry';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Id', 'Title', 'Style', 'Style2', 'Tags', 'Uuid', 'UuidBin', ],
        self::TYPE_CAMELNAME     => ['id', 'title', 'style', 'style2', 'tags', 'uuid', 'uuidBin', ],
        self::TYPE_COLNAME       => [Book2TableMap::COL_ID, Book2TableMap::COL_TITLE, Book2TableMap::COL_STYLE, Book2TableMap::COL_STYLE2, Book2TableMap::COL_TAGS, Book2TableMap::COL_UUID, Book2TableMap::COL_UUID_BIN, ],
        self::TYPE_FIELDNAME     => ['id', 'title', 'style', 'style2', 'tags', 'uuid', 'uuid_bin', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Title' => 1, 'Style' => 2, 'Style2' => 3, 'Tags' => 4, 'Uuid' => 5, 'UuidBin' => 6, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'title' => 1, 'style' => 2, 'style2' => 3, 'tags' => 4, 'uuid' => 5, 'uuidBin' => 6, ],
        self::TYPE_COLNAME       => [Book2TableMap::COL_ID => 0, Book2TableMap::COL_TITLE => 1, Book2TableMap::COL_STYLE => 2, Book2TableMap::COL_STYLE2 => 3, Book2TableMap::COL_TAGS => 4, Book2TableMap::COL_UUID => 5, Book2TableMap::COL_UUID_BIN => 6, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'title' => 1, 'style' => 2, 'style2' => 3, 'tags' => 4, 'uuid' => 5, 'uuid_bin' => 6, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'Book2.Id' => 'ID',
        'id' => 'ID',
        'book2.id' => 'ID',
        'Book2TableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'Title' => 'TITLE',
        'Book2.Title' => 'TITLE',
        'title' => 'TITLE',
        'book2.title' => 'TITLE',
        'Book2TableMap::COL_TITLE' => 'TITLE',
        'COL_TITLE' => 'TITLE',
        'Style' => 'STYLE',
        'Book2.Style' => 'STYLE',
        'style' => 'STYLE',
        'book2.style' => 'STYLE',
        'Book2TableMap::COL_STYLE' => 'STYLE',
        'COL_STYLE' => 'STYLE',
        'Style2' => 'STYLE2',
        'Book2.Style2' => 'STYLE2',
        'style2' => 'STYLE2',
        'book2.style2' => 'STYLE2',
        'Book2TableMap::COL_STYLE2' => 'STYLE2',
        'COL_STYLE2' => 'STYLE2',
        'Tags' => 'TAGS',
        'Book2.Tags' => 'TAGS',
        'tags' => 'TAGS',
        'book2.tags' => 'TAGS',
        'Book2TableMap::COL_TAGS' => 'TAGS',
        'COL_TAGS' => 'TAGS',
        'Uuid' => 'UUID',
        'Book2.Uuid' => 'UUID',
        'uuid' => 'UUID',
        'book2.uuid' => 'UUID',
        'Book2TableMap::COL_UUID' => 'UUID',
        'COL_UUID' => 'UUID',
        'UuidBin' => 'UUID_BIN',
        'Book2.UuidBin' => 'UUID_BIN',
        'uuidBin' => 'UUID_BIN',
        'book2.uuidBin' => 'UUID_BIN',
        'Book2TableMap::COL_UUID_BIN' => 'UUID_BIN',
        'COL_UUID_BIN' => 'UUID_BIN',
        'uuid_bin' => 'UUID_BIN',
        'book2.uuid_bin' => 'UUID_BIN',
    ];

    /**
     * The enumerated values for this table
     *
     * @var array<string, array<string>>
     */
    protected static $enumValueSets = [
                Book2TableMap::COL_STYLE => [
                            self::COL_STYLE_NOVEL,
            self::COL_STYLE_ESSAY,
            self::COL_STYLE_POETRY,
        ],
                Book2TableMap::COL_STYLE2 => [
                            self::COL_STYLE2_NOVEL,
            self::COL_STYLE2_ESSAY,
            self::COL_STYLE2_POETRY,
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
        $this->setName('book2');
        $this->setPhpName('Book2');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Book2');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('title', 'Title', 'VARCHAR', false, 255, null);
        $this->addColumn('style', 'Style', 'ENUM', false, null, null);
        $this->getColumn('style')->setValueSet(array (
  0 => 'novel',
  1 => 'essay',
  2 => 'poetry',
));
        $this->addColumn('style2', 'Style2', 'SET', false, null, null);
        $this->getColumn('style2')->setValueSet(array (
  0 => 'novel',
  1 => 'essay',
  2 => 'poetry',
));
        $this->addColumn('tags', 'Tags', 'JSON', false, null, null);
        $this->addColumn('uuid', 'Uuid', 'UUID_BINARY', false, null, null);
        $this->addColumn('uuid_bin', 'UuidBin', 'UUID_BINARY', false, null, null);
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
        return $withPrefix ? Book2TableMap::CLASS_DEFAULT : Book2TableMap::OM_CLASS;
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
     * @return array (Book2 object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = Book2TableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = Book2TableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + Book2TableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = Book2TableMap::OM_CLASS;
            /** @var Book2 $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            Book2TableMap::addInstanceToPool($obj, $key);
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
            $key = Book2TableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = Book2TableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Book2 $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                Book2TableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(Book2TableMap::COL_ID);
            $criteria->addSelectColumn(Book2TableMap::COL_TITLE);
            $criteria->addSelectColumn(Book2TableMap::COL_STYLE);
            $criteria->addSelectColumn(Book2TableMap::COL_STYLE2);
            $criteria->addSelectColumn(Book2TableMap::COL_TAGS);
            $criteria->addSelectColumn(Book2TableMap::COL_UUID);
            $criteria->addSelectColumn(Book2TableMap::COL_UUID_BIN);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.title');
            $criteria->addSelectColumn($alias . '.style');
            $criteria->addSelectColumn($alias . '.style2');
            $criteria->addSelectColumn($alias . '.tags');
            $criteria->addSelectColumn($alias . '.uuid');
            $criteria->addSelectColumn($alias . '.uuid_bin');
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
            $criteria->removeSelectColumn(Book2TableMap::COL_ID);
            $criteria->removeSelectColumn(Book2TableMap::COL_TITLE);
            $criteria->removeSelectColumn(Book2TableMap::COL_STYLE);
            $criteria->removeSelectColumn(Book2TableMap::COL_STYLE2);
            $criteria->removeSelectColumn(Book2TableMap::COL_TAGS);
            $criteria->removeSelectColumn(Book2TableMap::COL_UUID);
            $criteria->removeSelectColumn(Book2TableMap::COL_UUID_BIN);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.title');
            $criteria->removeSelectColumn($alias . '.style');
            $criteria->removeSelectColumn($alias . '.style2');
            $criteria->removeSelectColumn($alias . '.tags');
            $criteria->removeSelectColumn($alias . '.uuid');
            $criteria->removeSelectColumn($alias . '.uuid_bin');
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
        return Propel::getServiceContainer()->getDatabaseMap(Book2TableMap::DATABASE_NAME)->getTable(Book2TableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a Book2 or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or Book2 object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(Book2TableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Book2) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(Book2TableMap::DATABASE_NAME);
            $criteria->add(Book2TableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = Book2Query::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            Book2TableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                Book2TableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the book2 table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return Book2Query::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Book2 or Criteria object.
     *
     * @param mixed $criteria Criteria or Book2 object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(Book2TableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Book2 object
        }

        if ($criteria->containsKey(Book2TableMap::COL_ID) && $criteria->keyContainsValue(Book2TableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.Book2TableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = Book2Query::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
