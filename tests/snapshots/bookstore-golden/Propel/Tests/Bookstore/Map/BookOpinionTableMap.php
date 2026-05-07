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
use Propel\Tests\Bookstore\BookOpinion;
use Propel\Tests\Bookstore\BookOpinionQuery;


/**
 * This class defines the structure of the 'book_opinion' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class BookOpinionTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.BookOpinionTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'book_opinion';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'BookOpinion';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\BookOpinion';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.BookOpinion';

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
     * the column name for the book_id field
     */
    public const COL_BOOK_ID = 'book_opinion.book_id';

    /**
     * the column name for the reader_id field
     */
    public const COL_READER_ID = 'book_opinion.reader_id';

    /**
     * the column name for the rating field
     */
    public const COL_RATING = 'book_opinion.rating';

    /**
     * the column name for the recommend_to_friend field
     */
    public const COL_RECOMMEND_TO_FRIEND = 'book_opinion.recommend_to_friend';

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
        self::TYPE_PHPNAME       => ['BookId', 'ReaderId', 'Rating', 'RecommendToFriend', ],
        self::TYPE_CAMELNAME     => ['bookId', 'readerId', 'rating', 'recommendToFriend', ],
        self::TYPE_COLNAME       => [BookOpinionTableMap::COL_BOOK_ID, BookOpinionTableMap::COL_READER_ID, BookOpinionTableMap::COL_RATING, BookOpinionTableMap::COL_RECOMMEND_TO_FRIEND, ],
        self::TYPE_FIELDNAME     => ['book_id', 'reader_id', 'rating', 'recommend_to_friend', ],
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
        self::TYPE_PHPNAME       => ['BookId' => 0, 'ReaderId' => 1, 'Rating' => 2, 'RecommendToFriend' => 3, ],
        self::TYPE_CAMELNAME     => ['bookId' => 0, 'readerId' => 1, 'rating' => 2, 'recommendToFriend' => 3, ],
        self::TYPE_COLNAME       => [BookOpinionTableMap::COL_BOOK_ID => 0, BookOpinionTableMap::COL_READER_ID => 1, BookOpinionTableMap::COL_RATING => 2, BookOpinionTableMap::COL_RECOMMEND_TO_FRIEND => 3, ],
        self::TYPE_FIELDNAME     => ['book_id' => 0, 'reader_id' => 1, 'rating' => 2, 'recommend_to_friend' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'BookId' => 'BOOK_ID',
        'BookOpinion.BookId' => 'BOOK_ID',
        'bookId' => 'BOOK_ID',
        'bookOpinion.bookId' => 'BOOK_ID',
        'BookOpinionTableMap::COL_BOOK_ID' => 'BOOK_ID',
        'COL_BOOK_ID' => 'BOOK_ID',
        'book_id' => 'BOOK_ID',
        'book_opinion.book_id' => 'BOOK_ID',
        'ReaderId' => 'READER_ID',
        'BookOpinion.ReaderId' => 'READER_ID',
        'readerId' => 'READER_ID',
        'bookOpinion.readerId' => 'READER_ID',
        'BookOpinionTableMap::COL_READER_ID' => 'READER_ID',
        'COL_READER_ID' => 'READER_ID',
        'reader_id' => 'READER_ID',
        'book_opinion.reader_id' => 'READER_ID',
        'Rating' => 'RATING',
        'BookOpinion.Rating' => 'RATING',
        'rating' => 'RATING',
        'bookOpinion.rating' => 'RATING',
        'BookOpinionTableMap::COL_RATING' => 'RATING',
        'COL_RATING' => 'RATING',
        'book_opinion.rating' => 'RATING',
        'RecommendToFriend' => 'RECOMMEND_TO_FRIEND',
        'BookOpinion.RecommendToFriend' => 'RECOMMEND_TO_FRIEND',
        'recommendToFriend' => 'RECOMMEND_TO_FRIEND',
        'bookOpinion.recommendToFriend' => 'RECOMMEND_TO_FRIEND',
        'BookOpinionTableMap::COL_RECOMMEND_TO_FRIEND' => 'RECOMMEND_TO_FRIEND',
        'COL_RECOMMEND_TO_FRIEND' => 'RECOMMEND_TO_FRIEND',
        'recommend_to_friend' => 'RECOMMEND_TO_FRIEND',
        'book_opinion.recommend_to_friend' => 'RECOMMEND_TO_FRIEND',
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
        $this->setName('book_opinion');
        $this->setPhpName('BookOpinion');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\BookOpinion');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(false);
        // columns
        $this->addForeignPrimaryKey('book_id', 'BookId', 'INTEGER' , 'book', 'id', true, null, null);
        $this->addForeignPrimaryKey('reader_id', 'ReaderId', 'INTEGER' , 'book_reader', 'id', true, null, null);
        $this->addColumn('rating', 'Rating', 'DECIMAL', false, null, null);
        $this->addColumn('recommend_to_friend', 'RecommendToFriend', 'BOOLEAN', false, 1, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('Book', '\\Propel\\Tests\\Bookstore\\Book', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':book_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('BookReader', '\\Propel\\Tests\\Bookstore\\BookReader', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':reader_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('ReaderFavorite', '\\Propel\\Tests\\Bookstore\\ReaderFavorite', RelationMap::ONE_TO_ONE, array (
  0 =>
  array (
    0 => ':book_id',
    1 => ':book_id',
  ),
  1 =>
  array (
    0 => ':reader_id',
    1 => ':reader_id',
  ),
), 'CASCADE', null, null, false);
    }

    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database. In some cases you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by find*()
     * and findPk*() calls.
     *
     * @param \Propel\Tests\Bookstore\BookOpinion $obj A \Propel\Tests\Bookstore\BookOpinion object.
     * @param string|null $key Key (optional) to use for instance map (for performance boost if key was already calculated externally).
     *
     * @return void
     */
    public static function addInstanceToPool(BookOpinion $obj, ?string $key = null): void
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (null === $key) {
                $key = serialize([(null === $obj->getBookId() || is_scalar($obj->getBookId()) || is_callable([$obj->getBookId(), '__toString']) ? (string) $obj->getBookId() : $obj->getBookId()), (null === $obj->getReaderId() || is_scalar($obj->getReaderId()) || is_callable([$obj->getReaderId(), '__toString']) ? (string) $obj->getReaderId() : $obj->getReaderId())]);
            } // if key === null
            self::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param mixed $value A \Propel\Tests\Bookstore\BookOpinion object or a primary key value.
     *
     * @return void
     */
    public static function removeInstanceFromPool($value): void
    {
        if (Propel::isInstancePoolingEnabled() && null !== $value) {
            if (is_object($value) && $value instanceof \Propel\Tests\Bookstore\BookOpinion) {
                $key = serialize([(null === $value->getBookId() || is_scalar($value->getBookId()) || is_callable([$value->getBookId(), '__toString']) ? (string) $value->getBookId() : $value->getBookId()), (null === $value->getReaderId() || is_scalar($value->getReaderId()) || is_callable([$value->getReaderId(), '__toString']) ? (string) $value->getReaderId() : $value->getReaderId())]);

            } elseif (is_array($value) && count($value) === 2) {
                // assume we've been passed a primary key";
                $key = serialize([(null === $value[0] || is_scalar($value[0]) || is_callable([$value[0], '__toString']) ? (string) $value[0] : $value[0]), (null === $value[1] || is_scalar($value[1]) || is_callable([$value[1], '__toString']) ? (string) $value[1] : $value[1])]);
            } elseif ($value instanceof Criteria) {
                self::$instances = [];

                return;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or \Propel\Tests\Bookstore\BookOpinion object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value, true)));
                throw $e;
            }

            unset(self::$instances[$key]);
        }
    }

    /**
     * Method to invalidate the instance pool of all tables related to book_opinion     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        ReaderFavoriteTableMap::clearInstancePool();
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
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)] === null && $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ReaderId', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return serialize([(null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)]), (null === $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ReaderId', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ReaderId', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ReaderId', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ReaderId', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ReaderId', TableMap::TYPE_PHPNAME, $indexType)])]);
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
            $pks = [];

        $pks[] = (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)
        ];
        $pks[] = (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 1 + $offset
                : self::translateFieldName('ReaderId', TableMap::TYPE_PHPNAME, $indexType)
        ];

        return $pks;
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
        return $withPrefix ? BookOpinionTableMap::CLASS_DEFAULT : BookOpinionTableMap::OM_CLASS;
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
     * @return array (BookOpinion object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = BookOpinionTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = BookOpinionTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + BookOpinionTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = BookOpinionTableMap::OM_CLASS;
            /** @var BookOpinion $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            BookOpinionTableMap::addInstanceToPool($obj, $key);
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
            $key = BookOpinionTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = BookOpinionTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var BookOpinion $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                BookOpinionTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(BookOpinionTableMap::COL_BOOK_ID);
            $criteria->addSelectColumn(BookOpinionTableMap::COL_READER_ID);
            $criteria->addSelectColumn(BookOpinionTableMap::COL_RATING);
            $criteria->addSelectColumn(BookOpinionTableMap::COL_RECOMMEND_TO_FRIEND);
        } else {
            $criteria->addSelectColumn($alias . '.book_id');
            $criteria->addSelectColumn($alias . '.reader_id');
            $criteria->addSelectColumn($alias . '.rating');
            $criteria->addSelectColumn($alias . '.recommend_to_friend');
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
            $criteria->removeSelectColumn(BookOpinionTableMap::COL_BOOK_ID);
            $criteria->removeSelectColumn(BookOpinionTableMap::COL_READER_ID);
            $criteria->removeSelectColumn(BookOpinionTableMap::COL_RATING);
            $criteria->removeSelectColumn(BookOpinionTableMap::COL_RECOMMEND_TO_FRIEND);
        } else {
            $criteria->removeSelectColumn($alias . '.book_id');
            $criteria->removeSelectColumn($alias . '.reader_id');
            $criteria->removeSelectColumn($alias . '.rating');
            $criteria->removeSelectColumn($alias . '.recommend_to_friend');
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
        return Propel::getServiceContainer()->getDatabaseMap(BookOpinionTableMap::DATABASE_NAME)->getTable(BookOpinionTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a BookOpinion or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or BookOpinion object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(BookOpinionTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\BookOpinion) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(BookOpinionTableMap::DATABASE_NAME);
            // primary key is composite; we therefore, expect
            // the primary key passed to be an array of pkey values
            if (count($values) == count($values, COUNT_RECURSIVE)) {
                // array is not multi-dimensional
                $values = [$values];
            }
            foreach ($values as $value) {
                $criterion = $criteria->getNewCriterion(BookOpinionTableMap::COL_BOOK_ID, $value[0]);
                $criterion->addAnd($criteria->getNewCriterion(BookOpinionTableMap::COL_READER_ID, $value[1]));
                $criteria->addOr($criterion);
            }
        }

        $query = BookOpinionQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            BookOpinionTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                BookOpinionTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the book_opinion table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return BookOpinionQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a BookOpinion or Criteria object.
     *
     * @param mixed $criteria Criteria or BookOpinion object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookOpinionTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from BookOpinion object
        }


        // Set the correct dbName
        $query = BookOpinionQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
