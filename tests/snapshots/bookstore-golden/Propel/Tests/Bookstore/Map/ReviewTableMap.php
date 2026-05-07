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
use Propel\Tests\Bookstore\Review;
use Propel\Tests\Bookstore\ReviewQuery;


/**
 * This class defines the structure of the 'review' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ReviewTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Map.ReviewTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'review';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'Review';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Review';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Review';

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
    public const COL_ID = 'review.id';

    /**
     * the column name for the reviewed_by field
     */
    public const COL_REVIEWED_BY = 'review.reviewed_by';

    /**
     * the column name for the review_date field
     */
    public const COL_REVIEW_DATE = 'review.review_date';

    /**
     * the column name for the recommended field
     */
    public const COL_RECOMMENDED = 'review.recommended';

    /**
     * the column name for the status field
     */
    public const COL_STATUS = 'review.status';

    /**
     * the column name for the book_id field
     */
    public const COL_BOOK_ID = 'review.book_id';

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
        self::TYPE_PHPNAME       => ['Id', 'ReviewedBy', 'ReviewDate', 'Recommended', 'Status', 'BookId', ],
        self::TYPE_CAMELNAME     => ['id', 'reviewedBy', 'reviewDate', 'recommended', 'status', 'bookId', ],
        self::TYPE_COLNAME       => [ReviewTableMap::COL_ID, ReviewTableMap::COL_REVIEWED_BY, ReviewTableMap::COL_REVIEW_DATE, ReviewTableMap::COL_RECOMMENDED, ReviewTableMap::COL_STATUS, ReviewTableMap::COL_BOOK_ID, ],
        self::TYPE_FIELDNAME     => ['id', 'reviewed_by', 'review_date', 'recommended', 'status', 'book_id', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'ReviewedBy' => 1, 'ReviewDate' => 2, 'Recommended' => 3, 'Status' => 4, 'BookId' => 5, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'reviewedBy' => 1, 'reviewDate' => 2, 'recommended' => 3, 'status' => 4, 'bookId' => 5, ],
        self::TYPE_COLNAME       => [ReviewTableMap::COL_ID => 0, ReviewTableMap::COL_REVIEWED_BY => 1, ReviewTableMap::COL_REVIEW_DATE => 2, ReviewTableMap::COL_RECOMMENDED => 3, ReviewTableMap::COL_STATUS => 4, ReviewTableMap::COL_BOOK_ID => 5, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'reviewed_by' => 1, 'review_date' => 2, 'recommended' => 3, 'status' => 4, 'book_id' => 5, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'Review.Id' => 'ID',
        'id' => 'ID',
        'review.id' => 'ID',
        'ReviewTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'ReviewedBy' => 'REVIEWED_BY',
        'Review.ReviewedBy' => 'REVIEWED_BY',
        'reviewedBy' => 'REVIEWED_BY',
        'review.reviewedBy' => 'REVIEWED_BY',
        'ReviewTableMap::COL_REVIEWED_BY' => 'REVIEWED_BY',
        'COL_REVIEWED_BY' => 'REVIEWED_BY',
        'reviewed_by' => 'REVIEWED_BY',
        'review.reviewed_by' => 'REVIEWED_BY',
        'ReviewDate' => 'REVIEW_DATE',
        'Review.ReviewDate' => 'REVIEW_DATE',
        'reviewDate' => 'REVIEW_DATE',
        'review.reviewDate' => 'REVIEW_DATE',
        'ReviewTableMap::COL_REVIEW_DATE' => 'REVIEW_DATE',
        'COL_REVIEW_DATE' => 'REVIEW_DATE',
        'review_date' => 'REVIEW_DATE',
        'review.review_date' => 'REVIEW_DATE',
        'Recommended' => 'RECOMMENDED',
        'Review.Recommended' => 'RECOMMENDED',
        'recommended' => 'RECOMMENDED',
        'review.recommended' => 'RECOMMENDED',
        'ReviewTableMap::COL_RECOMMENDED' => 'RECOMMENDED',
        'COL_RECOMMENDED' => 'RECOMMENDED',
        'Status' => 'STATUS',
        'Review.Status' => 'STATUS',
        'status' => 'STATUS',
        'review.status' => 'STATUS',
        'ReviewTableMap::COL_STATUS' => 'STATUS',
        'COL_STATUS' => 'STATUS',
        'BookId' => 'BOOK_ID',
        'Review.BookId' => 'BOOK_ID',
        'bookId' => 'BOOK_ID',
        'review.bookId' => 'BOOK_ID',
        'ReviewTableMap::COL_BOOK_ID' => 'BOOK_ID',
        'COL_BOOK_ID' => 'BOOK_ID',
        'book_id' => 'BOOK_ID',
        'review.book_id' => 'BOOK_ID',
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
        $this->setName('review');
        $this->setPhpName('Review');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Review');
        $this->setPackage('Propel.Tests.Bookstore');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('reviewed_by', 'ReviewedBy', 'VARCHAR', true, 128, null);
        $this->addColumn('review_date', 'ReviewDate', 'DATE', true, null, '2001-01-01');
        $this->addColumn('recommended', 'Recommended', 'BOOLEAN', true, 1, null);
        $this->addColumn('status', 'Status', 'VARCHAR', false, 8, null);
        $this->addForeignKey('book_id', 'BookId', 'INTEGER', 'book', 'id', false, null, null);
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
        return $withPrefix ? ReviewTableMap::CLASS_DEFAULT : ReviewTableMap::OM_CLASS;
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
     * @return array (Review object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ReviewTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ReviewTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ReviewTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ReviewTableMap::OM_CLASS;
            /** @var Review $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ReviewTableMap::addInstanceToPool($obj, $key);
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
            $key = ReviewTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ReviewTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Review $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ReviewTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ReviewTableMap::COL_ID);
            $criteria->addSelectColumn(ReviewTableMap::COL_REVIEWED_BY);
            $criteria->addSelectColumn(ReviewTableMap::COL_REVIEW_DATE);
            $criteria->addSelectColumn(ReviewTableMap::COL_RECOMMENDED);
            $criteria->addSelectColumn(ReviewTableMap::COL_STATUS);
            $criteria->addSelectColumn(ReviewTableMap::COL_BOOK_ID);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.reviewed_by');
            $criteria->addSelectColumn($alias . '.review_date');
            $criteria->addSelectColumn($alias . '.recommended');
            $criteria->addSelectColumn($alias . '.status');
            $criteria->addSelectColumn($alias . '.book_id');
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
            $criteria->removeSelectColumn(ReviewTableMap::COL_ID);
            $criteria->removeSelectColumn(ReviewTableMap::COL_REVIEWED_BY);
            $criteria->removeSelectColumn(ReviewTableMap::COL_REVIEW_DATE);
            $criteria->removeSelectColumn(ReviewTableMap::COL_RECOMMENDED);
            $criteria->removeSelectColumn(ReviewTableMap::COL_STATUS);
            $criteria->removeSelectColumn(ReviewTableMap::COL_BOOK_ID);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.reviewed_by');
            $criteria->removeSelectColumn($alias . '.review_date');
            $criteria->removeSelectColumn($alias . '.recommended');
            $criteria->removeSelectColumn($alias . '.status');
            $criteria->removeSelectColumn($alias . '.book_id');
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
        return Propel::getServiceContainer()->getDatabaseMap(ReviewTableMap::DATABASE_NAME)->getTable(ReviewTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a Review or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or Review object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ReviewTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Review) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ReviewTableMap::DATABASE_NAME);
            $criteria->add(ReviewTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ReviewQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ReviewTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ReviewTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the review table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ReviewQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Review or Criteria object.
     *
     * @param mixed $criteria Criteria or Review object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ReviewTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Review object
        }

        if ($criteria->containsKey(ReviewTableMap::COL_ID) && $criteria->keyContainsValue(ReviewTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ReviewTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = ReviewQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
