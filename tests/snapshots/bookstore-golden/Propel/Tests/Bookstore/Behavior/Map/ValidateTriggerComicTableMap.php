<?php

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
use Propel\Tests\Bookstore\Behavior\ValidateTriggerComic;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery;


/**
 * This class defines the structure of the 'validate_trigger_comic' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ValidateTriggerComicTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.ValidateTriggerComicTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'validate_trigger_comic';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'ValidateTriggerComic';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerComic';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.ValidateTriggerComic';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 6;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 6;

    /**
     * the column name for the bar field
     */
    public const COL_BAR = 'validate_trigger_comic.bar';

    /**
     * the column name for the id field
     */
    public const COL_ID = 'validate_trigger_comic.id';

    /**
     * the column name for the isbn field
     */
    public const COL_ISBN = 'validate_trigger_comic.isbn';

    /**
     * the column name for the price field
     */
    public const COL_PRICE = 'validate_trigger_comic.price';

    /**
     * the column name for the publisher_id field
     */
    public const COL_PUBLISHER_ID = 'validate_trigger_comic.publisher_id';

    /**
     * the column name for the author_id field
     */
    public const COL_AUTHOR_ID = 'validate_trigger_comic.author_id';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    // i18n behavior

    /**
     * The default locale to use for translations.
     *
     * @var string
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Bar', 'Id', 'ISBN', 'Price', 'PublisherId', 'AuthorId', ],
        self::TYPE_CAMELNAME     => ['bar', 'id', 'iSBN', 'price', 'publisherId', 'authorId', ],
        self::TYPE_COLNAME       => [ValidateTriggerComicTableMap::COL_BAR, ValidateTriggerComicTableMap::COL_ID, ValidateTriggerComicTableMap::COL_ISBN, ValidateTriggerComicTableMap::COL_PRICE, ValidateTriggerComicTableMap::COL_PUBLISHER_ID, ValidateTriggerComicTableMap::COL_AUTHOR_ID, ],
        self::TYPE_FIELDNAME     => ['bar', 'id', 'isbn', 'price', 'publisher_id', 'author_id', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
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
        self::TYPE_PHPNAME       => ['Bar' => 0, 'Id' => 1, 'ISBN' => 2, 'Price' => 3, 'PublisherId' => 4, 'AuthorId' => 5, ],
        self::TYPE_CAMELNAME     => ['bar' => 0, 'id' => 1, 'iSBN' => 2, 'price' => 3, 'publisherId' => 4, 'authorId' => 5, ],
        self::TYPE_COLNAME       => [ValidateTriggerComicTableMap::COL_BAR => 0, ValidateTriggerComicTableMap::COL_ID => 1, ValidateTriggerComicTableMap::COL_ISBN => 2, ValidateTriggerComicTableMap::COL_PRICE => 3, ValidateTriggerComicTableMap::COL_PUBLISHER_ID => 4, ValidateTriggerComicTableMap::COL_AUTHOR_ID => 5, ],
        self::TYPE_FIELDNAME     => ['bar' => 0, 'id' => 1, 'isbn' => 2, 'price' => 3, 'publisher_id' => 4, 'author_id' => 5, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Bar' => 'BAR',
        'ValidateTriggerComic.Bar' => 'BAR',
        'bar' => 'BAR',
        'validateTriggerComic.bar' => 'BAR',
        'ValidateTriggerComicTableMap::COL_BAR' => 'BAR',
        'COL_BAR' => 'BAR',
        'validate_trigger_comic.bar' => 'BAR',
        'Id' => 'ID',
        'ValidateTriggerComic.Id' => 'ID',
        'id' => 'ID',
        'validateTriggerComic.id' => 'ID',
        'ValidateTriggerComicTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'validate_trigger_comic.id' => 'ID',
        'ISBN' => 'ISBN',
        'ValidateTriggerComic.ISBN' => 'ISBN',
        'iSBN' => 'ISBN',
        'validateTriggerComic.iSBN' => 'ISBN',
        'ValidateTriggerComicTableMap::COL_ISBN' => 'ISBN',
        'COL_ISBN' => 'ISBN',
        'isbn' => 'ISBN',
        'validate_trigger_comic.isbn' => 'ISBN',
        'Price' => 'PRICE',
        'ValidateTriggerComic.Price' => 'PRICE',
        'price' => 'PRICE',
        'validateTriggerComic.price' => 'PRICE',
        'ValidateTriggerComicTableMap::COL_PRICE' => 'PRICE',
        'COL_PRICE' => 'PRICE',
        'validate_trigger_comic.price' => 'PRICE',
        'PublisherId' => 'PUBLISHER_ID',
        'ValidateTriggerComic.PublisherId' => 'PUBLISHER_ID',
        'publisherId' => 'PUBLISHER_ID',
        'validateTriggerComic.publisherId' => 'PUBLISHER_ID',
        'ValidateTriggerComicTableMap::COL_PUBLISHER_ID' => 'PUBLISHER_ID',
        'COL_PUBLISHER_ID' => 'PUBLISHER_ID',
        'publisher_id' => 'PUBLISHER_ID',
        'validate_trigger_comic.publisher_id' => 'PUBLISHER_ID',
        'AuthorId' => 'AUTHOR_ID',
        'ValidateTriggerComic.AuthorId' => 'AUTHOR_ID',
        'authorId' => 'AUTHOR_ID',
        'validateTriggerComic.authorId' => 'AUTHOR_ID',
        'ValidateTriggerComicTableMap::COL_AUTHOR_ID' => 'AUTHOR_ID',
        'COL_AUTHOR_ID' => 'AUTHOR_ID',
        'author_id' => 'AUTHOR_ID',
        'validate_trigger_comic.author_id' => 'AUTHOR_ID',
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
        $this->setName('validate_trigger_comic');
        $this->setPhpName('ValidateTriggerComic');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerComic');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(false);
        // columns
        $this->addColumn('bar', 'Bar', 'VARCHAR', false, 100, null);
        $this->addForeignPrimaryKey('id', 'Id', 'INTEGER' , 'validate_trigger_book', 'id', true, null, null);
        $this->addColumn('isbn', 'ISBN', 'VARCHAR', false, 24, null);
        $this->addColumn('price', 'Price', 'FLOAT', false, null, null);
        $this->addColumn('publisher_id', 'PublisherId', 'INTEGER', false, null, null);
        $this->addColumn('author_id', 'AuthorId', 'INTEGER', false, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('ValidateTriggerBook', '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerBook', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('ValidateTriggerComicI18n', '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerComicI18n', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':id',
    1 => ':id',
  ),
), 'CASCADE', null, 'ValidateTriggerComicI18ns', false);
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
            'concrete_inheritance' => ['extends' => 'validate_trigger_book', 'descendant_column' => 'descendant_class', 'copy_data_to_parent' => 'true', 'copy_data_to_child' => 'false', 'schema' => '', 'exclude_behaviors' => ''],
            'validate' => ['rule1' => ['column' => 'bar', 'validator' => 'NotNull'], 'rule2' => ['column' => 'bar', 'validator' => 'Type', 'options' => ['type' => 'string']], 'rule4' => ['column' => 'isbn', 'validator' => 'Regex', 'options' => ['pattern' => '/[^\\d-]+/', 'match' => false, 'message' => 'Please enter a valid ISBN']]],
            'i18n' => ['i18n_table' => '%TABLE%_i18n', 'i18n_phpname' => '%PHPNAME%I18n', 'i18n_columns' => 'title', 'i18n_pk_column' => NULL, 'locale_column' => 'locale', 'locale_length' => 5, 'default_locale' => NULL, 'locale_alias' => ''],
        ];
    }

    /**
     * Method to invalidate the instance pool of all tables related to validate_trigger_comic     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        ValidateTriggerComicI18nTableMap::clearInstancePool();
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
        if ($row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
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
                ? 1 + $offset
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
        return $withPrefix ? ValidateTriggerComicTableMap::CLASS_DEFAULT : ValidateTriggerComicTableMap::OM_CLASS;
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
     * @return array (ValidateTriggerComic object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ValidateTriggerComicTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ValidateTriggerComicTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ValidateTriggerComicTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ValidateTriggerComicTableMap::OM_CLASS;
            /** @var ValidateTriggerComic $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ValidateTriggerComicTableMap::addInstanceToPool($obj, $key);
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
            $key = ValidateTriggerComicTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ValidateTriggerComicTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var ValidateTriggerComic $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ValidateTriggerComicTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ValidateTriggerComicTableMap::COL_BAR);
            $criteria->addSelectColumn(ValidateTriggerComicTableMap::COL_ID);
            $criteria->addSelectColumn(ValidateTriggerComicTableMap::COL_ISBN);
            $criteria->addSelectColumn(ValidateTriggerComicTableMap::COL_PRICE);
            $criteria->addSelectColumn(ValidateTriggerComicTableMap::COL_PUBLISHER_ID);
            $criteria->addSelectColumn(ValidateTriggerComicTableMap::COL_AUTHOR_ID);
        } else {
            $criteria->addSelectColumn($alias . '.bar');
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.isbn');
            $criteria->addSelectColumn($alias . '.price');
            $criteria->addSelectColumn($alias . '.publisher_id');
            $criteria->addSelectColumn($alias . '.author_id');
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
            $criteria->removeSelectColumn(ValidateTriggerComicTableMap::COL_BAR);
            $criteria->removeSelectColumn(ValidateTriggerComicTableMap::COL_ID);
            $criteria->removeSelectColumn(ValidateTriggerComicTableMap::COL_ISBN);
            $criteria->removeSelectColumn(ValidateTriggerComicTableMap::COL_PRICE);
            $criteria->removeSelectColumn(ValidateTriggerComicTableMap::COL_PUBLISHER_ID);
            $criteria->removeSelectColumn(ValidateTriggerComicTableMap::COL_AUTHOR_ID);
        } else {
            $criteria->removeSelectColumn($alias . '.bar');
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.isbn');
            $criteria->removeSelectColumn($alias . '.price');
            $criteria->removeSelectColumn($alias . '.publisher_id');
            $criteria->removeSelectColumn($alias . '.author_id');
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
        return Propel::getServiceContainer()->getDatabaseMap(ValidateTriggerComicTableMap::DATABASE_NAME)->getTable(ValidateTriggerComicTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a ValidateTriggerComic or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or ValidateTriggerComic object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerComicTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\ValidateTriggerComic) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ValidateTriggerComicTableMap::DATABASE_NAME);
            $criteria->add(ValidateTriggerComicTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ValidateTriggerComicQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ValidateTriggerComicTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ValidateTriggerComicTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the validate_trigger_comic table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ValidateTriggerComicQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ValidateTriggerComic or Criteria object.
     *
     * @param mixed $criteria Criteria or ValidateTriggerComic object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerComicTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ValidateTriggerComic object
        }


        // Set the correct dbName
        $query = ValidateTriggerComicQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
