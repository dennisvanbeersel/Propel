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
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBook;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBookQuery;


/**
 * This class defines the structure of the 'validate_trigger_book' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ValidateTriggerBookTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.ValidateTriggerBookTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'validate_trigger_book';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'ValidateTriggerBook';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerBook';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.ValidateTriggerBook';

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
     * the column name for the id field
     */
    public const COL_ID = 'validate_trigger_book.id';

    /**
     * the column name for the isbn field
     */
    public const COL_ISBN = 'validate_trigger_book.isbn';

    /**
     * the column name for the price field
     */
    public const COL_PRICE = 'validate_trigger_book.price';

    /**
     * the column name for the publisher_id field
     */
    public const COL_PUBLISHER_ID = 'validate_trigger_book.publisher_id';

    /**
     * the column name for the author_id field
     */
    public const COL_AUTHOR_ID = 'validate_trigger_book.author_id';

    /**
     * the column name for the descendant_class field
     */
    public const COL_DESCENDANT_CLASS = 'validate_trigger_book.descendant_class';

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
        self::TYPE_PHPNAME       => ['Id', 'ISBN', 'Price', 'PublisherId', 'AuthorId', 'DescendantClass', ],
        self::TYPE_CAMELNAME     => ['id', 'iSBN', 'price', 'publisherId', 'authorId', 'descendantClass', ],
        self::TYPE_COLNAME       => [ValidateTriggerBookTableMap::COL_ID, ValidateTriggerBookTableMap::COL_ISBN, ValidateTriggerBookTableMap::COL_PRICE, ValidateTriggerBookTableMap::COL_PUBLISHER_ID, ValidateTriggerBookTableMap::COL_AUTHOR_ID, ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS, ],
        self::TYPE_FIELDNAME     => ['id', 'isbn', 'price', 'publisher_id', 'author_id', 'descendant_class', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'ISBN' => 1, 'Price' => 2, 'PublisherId' => 3, 'AuthorId' => 4, 'DescendantClass' => 5, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'iSBN' => 1, 'price' => 2, 'publisherId' => 3, 'authorId' => 4, 'descendantClass' => 5, ],
        self::TYPE_COLNAME       => [ValidateTriggerBookTableMap::COL_ID => 0, ValidateTriggerBookTableMap::COL_ISBN => 1, ValidateTriggerBookTableMap::COL_PRICE => 2, ValidateTriggerBookTableMap::COL_PUBLISHER_ID => 3, ValidateTriggerBookTableMap::COL_AUTHOR_ID => 4, ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS => 5, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'isbn' => 1, 'price' => 2, 'publisher_id' => 3, 'author_id' => 4, 'descendant_class' => 5, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'ValidateTriggerBook.Id' => 'ID',
        'id' => 'ID',
        'validateTriggerBook.id' => 'ID',
        'ValidateTriggerBookTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'validate_trigger_book.id' => 'ID',
        'ISBN' => 'ISBN',
        'ValidateTriggerBook.ISBN' => 'ISBN',
        'iSBN' => 'ISBN',
        'validateTriggerBook.iSBN' => 'ISBN',
        'ValidateTriggerBookTableMap::COL_ISBN' => 'ISBN',
        'COL_ISBN' => 'ISBN',
        'isbn' => 'ISBN',
        'validate_trigger_book.isbn' => 'ISBN',
        'Price' => 'PRICE',
        'ValidateTriggerBook.Price' => 'PRICE',
        'price' => 'PRICE',
        'validateTriggerBook.price' => 'PRICE',
        'ValidateTriggerBookTableMap::COL_PRICE' => 'PRICE',
        'COL_PRICE' => 'PRICE',
        'validate_trigger_book.price' => 'PRICE',
        'PublisherId' => 'PUBLISHER_ID',
        'ValidateTriggerBook.PublisherId' => 'PUBLISHER_ID',
        'publisherId' => 'PUBLISHER_ID',
        'validateTriggerBook.publisherId' => 'PUBLISHER_ID',
        'ValidateTriggerBookTableMap::COL_PUBLISHER_ID' => 'PUBLISHER_ID',
        'COL_PUBLISHER_ID' => 'PUBLISHER_ID',
        'publisher_id' => 'PUBLISHER_ID',
        'validate_trigger_book.publisher_id' => 'PUBLISHER_ID',
        'AuthorId' => 'AUTHOR_ID',
        'ValidateTriggerBook.AuthorId' => 'AUTHOR_ID',
        'authorId' => 'AUTHOR_ID',
        'validateTriggerBook.authorId' => 'AUTHOR_ID',
        'ValidateTriggerBookTableMap::COL_AUTHOR_ID' => 'AUTHOR_ID',
        'COL_AUTHOR_ID' => 'AUTHOR_ID',
        'author_id' => 'AUTHOR_ID',
        'validate_trigger_book.author_id' => 'AUTHOR_ID',
        'DescendantClass' => 'DESCENDANT_CLASS',
        'ValidateTriggerBook.DescendantClass' => 'DESCENDANT_CLASS',
        'descendantClass' => 'DESCENDANT_CLASS',
        'validateTriggerBook.descendantClass' => 'DESCENDANT_CLASS',
        'ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS' => 'DESCENDANT_CLASS',
        'COL_DESCENDANT_CLASS' => 'DESCENDANT_CLASS',
        'descendant_class' => 'DESCENDANT_CLASS',
        'validate_trigger_book.descendant_class' => 'DESCENDANT_CLASS',
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
        $this->setName('validate_trigger_book');
        $this->setPhpName('ValidateTriggerBook');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerBook');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('isbn', 'ISBN', 'VARCHAR', false, 24, null);
        $this->addColumn('price', 'Price', 'FLOAT', false, null, null);
        $this->addColumn('publisher_id', 'PublisherId', 'INTEGER', false, null, null);
        $this->addColumn('author_id', 'AuthorId', 'INTEGER', false, null, null);
        $this->addColumn('descendant_class', 'DescendantClass', 'VARCHAR', false, 100, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('ValidateTriggerFiction', '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerFiction', RelationMap::ONE_TO_ONE, array (
  0 =>
  array (
    0 => ':id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('ValidateTriggerComic', '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerComic', RelationMap::ONE_TO_ONE, array (
  0 =>
  array (
    0 => ':id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('ValidateTriggerBookI18n', '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerBookI18n', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':id',
    1 => ':id',
  ),
), 'CASCADE', null, 'ValidateTriggerBookI18ns', false);
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
            'validate' => ['rule2' => ['column' => 'isbn', 'validator' => 'Regex', 'options' => ['pattern' => '/[^\\d-]+/', 'match' => false, 'message' => 'Please enter a valid ISBN']]],
            'i18n' => ['i18n_table' => '%TABLE%_i18n', 'i18n_phpname' => '%PHPNAME%I18n', 'i18n_columns' => 'title', 'i18n_pk_column' => NULL, 'locale_column' => 'locale', 'locale_length' => 5, 'default_locale' => NULL, 'locale_alias' => ''],
            'concrete_inheritance_parent' => ['descendant_column' => 'descendant_class'],
        ];
    }

    /**
     * Method to invalidate the instance pool of all tables related to validate_trigger_book     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        ValidateTriggerFictionTableMap::clearInstancePool();
        ValidateTriggerComicTableMap::clearInstancePool();
        ValidateTriggerBookI18nTableMap::clearInstancePool();
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
        return $withPrefix ? ValidateTriggerBookTableMap::CLASS_DEFAULT : ValidateTriggerBookTableMap::OM_CLASS;
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
     * @return array (ValidateTriggerBook object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ValidateTriggerBookTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ValidateTriggerBookTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ValidateTriggerBookTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ValidateTriggerBookTableMap::OM_CLASS;
            /** @var ValidateTriggerBook $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ValidateTriggerBookTableMap::addInstanceToPool($obj, $key);
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
            $key = ValidateTriggerBookTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ValidateTriggerBookTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var ValidateTriggerBook $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ValidateTriggerBookTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ValidateTriggerBookTableMap::COL_ID);
            $criteria->addSelectColumn(ValidateTriggerBookTableMap::COL_ISBN);
            $criteria->addSelectColumn(ValidateTriggerBookTableMap::COL_PRICE);
            $criteria->addSelectColumn(ValidateTriggerBookTableMap::COL_PUBLISHER_ID);
            $criteria->addSelectColumn(ValidateTriggerBookTableMap::COL_AUTHOR_ID);
            $criteria->addSelectColumn(ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.isbn');
            $criteria->addSelectColumn($alias . '.price');
            $criteria->addSelectColumn($alias . '.publisher_id');
            $criteria->addSelectColumn($alias . '.author_id');
            $criteria->addSelectColumn($alias . '.descendant_class');
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
            $criteria->removeSelectColumn(ValidateTriggerBookTableMap::COL_ID);
            $criteria->removeSelectColumn(ValidateTriggerBookTableMap::COL_ISBN);
            $criteria->removeSelectColumn(ValidateTriggerBookTableMap::COL_PRICE);
            $criteria->removeSelectColumn(ValidateTriggerBookTableMap::COL_PUBLISHER_ID);
            $criteria->removeSelectColumn(ValidateTriggerBookTableMap::COL_AUTHOR_ID);
            $criteria->removeSelectColumn(ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.isbn');
            $criteria->removeSelectColumn($alias . '.price');
            $criteria->removeSelectColumn($alias . '.publisher_id');
            $criteria->removeSelectColumn($alias . '.author_id');
            $criteria->removeSelectColumn($alias . '.descendant_class');
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
        return Propel::getServiceContainer()->getDatabaseMap(ValidateTriggerBookTableMap::DATABASE_NAME)->getTable(ValidateTriggerBookTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a ValidateTriggerBook or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or ValidateTriggerBook object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\ValidateTriggerBook) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ValidateTriggerBookTableMap::DATABASE_NAME);
            $criteria->add(ValidateTriggerBookTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ValidateTriggerBookQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ValidateTriggerBookTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ValidateTriggerBookTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the validate_trigger_book table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ValidateTriggerBookQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ValidateTriggerBook or Criteria object.
     *
     * @param mixed $criteria Criteria or ValidateTriggerBook object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ValidateTriggerBook object
        }

        if ($criteria->containsKey(ValidateTriggerBookTableMap::COL_ID) && $criteria->keyContainsValue(ValidateTriggerBookTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ValidateTriggerBookTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = ValidateTriggerBookQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
