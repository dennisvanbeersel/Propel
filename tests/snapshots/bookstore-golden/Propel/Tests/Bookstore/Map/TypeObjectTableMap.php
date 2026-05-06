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
use Propel\Tests\Bookstore\TypeObject;
use Propel\Tests\Bookstore\TypeObjectQuery;


/**
 * This class defines the structure of the 'type_object' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class TypeObjectTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.TypeObjectTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'type_object';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'TypeObject';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\TypeObject';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.TypeObject';

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
    public const COL_ID = 'type_object.id';

    /**
     * the column name for the details field
     */
    public const COL_DETAILS = 'type_object.details';

    /**
     * the column name for the dummy_object field
     */
    public const COL_DUMMY_OBJECT = 'type_object.dummy_object';

    /**
     * the column name for the self_ref field
     */
    public const COL_SELF_REF = 'type_object.self_ref';

    /**
     * the column name for the some_array field
     */
    public const COL_SOME_ARRAY = 'type_object.some_array';

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
        self::TYPE_PHPNAME       => ['Id', 'Details', 'DummyObject', 'SelfRef', 'SomeArray', ],
        self::TYPE_CAMELNAME     => ['id', 'details', 'dummyObject', 'selfRef', 'someArray', ],
        self::TYPE_COLNAME       => [TypeObjectTableMap::COL_ID, TypeObjectTableMap::COL_DETAILS, TypeObjectTableMap::COL_DUMMY_OBJECT, TypeObjectTableMap::COL_SELF_REF, TypeObjectTableMap::COL_SOME_ARRAY, ],
        self::TYPE_FIELDNAME     => ['id', 'details', 'dummy_object', 'self_ref', 'some_array', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Details' => 1, 'DummyObject' => 2, 'SelfRef' => 3, 'SomeArray' => 4, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'details' => 1, 'dummyObject' => 2, 'selfRef' => 3, 'someArray' => 4, ],
        self::TYPE_COLNAME       => [TypeObjectTableMap::COL_ID => 0, TypeObjectTableMap::COL_DETAILS => 1, TypeObjectTableMap::COL_DUMMY_OBJECT => 2, TypeObjectTableMap::COL_SELF_REF => 3, TypeObjectTableMap::COL_SOME_ARRAY => 4, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'details' => 1, 'dummy_object' => 2, 'self_ref' => 3, 'some_array' => 4, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'TypeObject.Id' => 'ID',
        'id' => 'ID',
        'typeObject.id' => 'ID',
        'TypeObjectTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'type_object.id' => 'ID',
        'Details' => 'DETAILS',
        'TypeObject.Details' => 'DETAILS',
        'details' => 'DETAILS',
        'typeObject.details' => 'DETAILS',
        'TypeObjectTableMap::COL_DETAILS' => 'DETAILS',
        'COL_DETAILS' => 'DETAILS',
        'type_object.details' => 'DETAILS',
        'DummyObject' => 'DUMMY_OBJECT',
        'TypeObject.DummyObject' => 'DUMMY_OBJECT',
        'dummyObject' => 'DUMMY_OBJECT',
        'typeObject.dummyObject' => 'DUMMY_OBJECT',
        'TypeObjectTableMap::COL_DUMMY_OBJECT' => 'DUMMY_OBJECT',
        'COL_DUMMY_OBJECT' => 'DUMMY_OBJECT',
        'dummy_object' => 'DUMMY_OBJECT',
        'type_object.dummy_object' => 'DUMMY_OBJECT',
        'SelfRef' => 'SELF_REF',
        'TypeObject.SelfRef' => 'SELF_REF',
        'selfRef' => 'SELF_REF',
        'typeObject.selfRef' => 'SELF_REF',
        'TypeObjectTableMap::COL_SELF_REF' => 'SELF_REF',
        'COL_SELF_REF' => 'SELF_REF',
        'self_ref' => 'SELF_REF',
        'type_object.self_ref' => 'SELF_REF',
        'SomeArray' => 'SOME_ARRAY',
        'TypeObject.SomeArray' => 'SOME_ARRAY',
        'someArray' => 'SOME_ARRAY',
        'typeObject.someArray' => 'SOME_ARRAY',
        'TypeObjectTableMap::COL_SOME_ARRAY' => 'SOME_ARRAY',
        'COL_SOME_ARRAY' => 'SOME_ARRAY',
        'some_array' => 'SOME_ARRAY',
        'type_object.some_array' => 'SOME_ARRAY',
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
        $this->setName('type_object');
        $this->setPhpName('TypeObject');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\TypeObject');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('details', 'Details', 'OBJECT', false, null, null);
        $this->addColumn('dummy_object', 'DummyObject', 'OBJECT', false, null, null);
        $this->addForeignKey('self_ref', 'SelfRef', 'INTEGER', 'type_object', 'id', false, null, null);
        $this->addColumn('some_array', 'SomeArray', 'ARRAY', false, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('TypeObject', '\\Propel\\Tests\\Bookstore\\TypeObject', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':self_ref',
    1 => ':id',
  ),
), null, null, null, false);
        $this->addRelation('TypeObjectRelatedById', '\\Propel\\Tests\\Bookstore\\TypeObject', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':self_ref',
    1 => ':id',
  ),
), null, null, 'TypeObjectsRelatedById', false);
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
        return $withPrefix ? TypeObjectTableMap::CLASS_DEFAULT : TypeObjectTableMap::OM_CLASS;
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
     * @return array (TypeObject object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = TypeObjectTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = TypeObjectTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + TypeObjectTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = TypeObjectTableMap::OM_CLASS;
            /** @var TypeObject $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            TypeObjectTableMap::addInstanceToPool($obj, $key);
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
            $key = TypeObjectTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = TypeObjectTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var TypeObject $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                TypeObjectTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(TypeObjectTableMap::COL_ID);
            $criteria->addSelectColumn(TypeObjectTableMap::COL_DETAILS);
            $criteria->addSelectColumn(TypeObjectTableMap::COL_DUMMY_OBJECT);
            $criteria->addSelectColumn(TypeObjectTableMap::COL_SELF_REF);
            $criteria->addSelectColumn(TypeObjectTableMap::COL_SOME_ARRAY);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.details');
            $criteria->addSelectColumn($alias . '.dummy_object');
            $criteria->addSelectColumn($alias . '.self_ref');
            $criteria->addSelectColumn($alias . '.some_array');
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
            $criteria->removeSelectColumn(TypeObjectTableMap::COL_ID);
            $criteria->removeSelectColumn(TypeObjectTableMap::COL_DETAILS);
            $criteria->removeSelectColumn(TypeObjectTableMap::COL_DUMMY_OBJECT);
            $criteria->removeSelectColumn(TypeObjectTableMap::COL_SELF_REF);
            $criteria->removeSelectColumn(TypeObjectTableMap::COL_SOME_ARRAY);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.details');
            $criteria->removeSelectColumn($alias . '.dummy_object');
            $criteria->removeSelectColumn($alias . '.self_ref');
            $criteria->removeSelectColumn($alias . '.some_array');
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
        return Propel::getServiceContainer()->getDatabaseMap(TypeObjectTableMap::DATABASE_NAME)->getTable(TypeObjectTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a TypeObject or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or TypeObject object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(TypeObjectTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\TypeObject) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(TypeObjectTableMap::DATABASE_NAME);
            $criteria->add(TypeObjectTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = TypeObjectQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            TypeObjectTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                TypeObjectTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the type_object table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return TypeObjectQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a TypeObject or Criteria object.
     *
     * @param mixed $criteria Criteria or TypeObject object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(TypeObjectTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from TypeObject object
        }

        if ($criteria->containsKey(TypeObjectTableMap::COL_ID) && $criteria->keyContainsValue(TypeObjectTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.TypeObjectTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = TypeObjectQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
