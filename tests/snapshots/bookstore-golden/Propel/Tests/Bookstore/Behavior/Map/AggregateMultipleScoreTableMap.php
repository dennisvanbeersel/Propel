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
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScore;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery;


/**
 * This class defines the structure of the 'aggregate_multiple_score' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class AggregateMultipleScoreTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.AggregateMultipleScoreTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'aggregate_multiple_score';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'AggregateMultipleScore';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScore';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.AggregateMultipleScore';

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
    public const COL_ID = 'aggregate_multiple_score.id';

    /**
     * the column name for the score field
     */
    public const COL_SCORE = 'aggregate_multiple_score.score';

    /**
     * the column name for the scored_at field
     */
    public const COL_SCORED_AT = 'aggregate_multiple_score.scored_at';

    /**
     * the column name for the score_group_id field
     */
    public const COL_SCORE_GROUP_ID = 'aggregate_multiple_score.score_group_id';

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
        self::TYPE_PHPNAME       => ['Id', 'Score', 'ScoredAt', 'ScoreGroupId', ],
        self::TYPE_CAMELNAME     => ['id', 'score', 'scoredAt', 'scoreGroupId', ],
        self::TYPE_COLNAME       => [AggregateMultipleScoreTableMap::COL_ID, AggregateMultipleScoreTableMap::COL_SCORE, AggregateMultipleScoreTableMap::COL_SCORED_AT, AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID, ],
        self::TYPE_FIELDNAME     => ['id', 'score', 'scored_at', 'score_group_id', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Score' => 1, 'ScoredAt' => 2, 'ScoreGroupId' => 3, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'score' => 1, 'scoredAt' => 2, 'scoreGroupId' => 3, ],
        self::TYPE_COLNAME       => [AggregateMultipleScoreTableMap::COL_ID => 0, AggregateMultipleScoreTableMap::COL_SCORE => 1, AggregateMultipleScoreTableMap::COL_SCORED_AT => 2, AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID => 3, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'score' => 1, 'scored_at' => 2, 'score_group_id' => 3, ],
        self::TYPE_NUM           => [0, 1, 2, 3, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'AggregateMultipleScore.Id' => 'ID',
        'id' => 'ID',
        'aggregateMultipleScore.id' => 'ID',
        'AggregateMultipleScoreTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'aggregate_multiple_score.id' => 'ID',
        'Score' => 'SCORE',
        'AggregateMultipleScore.Score' => 'SCORE',
        'score' => 'SCORE',
        'aggregateMultipleScore.score' => 'SCORE',
        'AggregateMultipleScoreTableMap::COL_SCORE' => 'SCORE',
        'COL_SCORE' => 'SCORE',
        'aggregate_multiple_score.score' => 'SCORE',
        'ScoredAt' => 'SCORED_AT',
        'AggregateMultipleScore.ScoredAt' => 'SCORED_AT',
        'scoredAt' => 'SCORED_AT',
        'aggregateMultipleScore.scoredAt' => 'SCORED_AT',
        'AggregateMultipleScoreTableMap::COL_SCORED_AT' => 'SCORED_AT',
        'COL_SCORED_AT' => 'SCORED_AT',
        'scored_at' => 'SCORED_AT',
        'aggregate_multiple_score.scored_at' => 'SCORED_AT',
        'ScoreGroupId' => 'SCORE_GROUP_ID',
        'AggregateMultipleScore.ScoreGroupId' => 'SCORE_GROUP_ID',
        'scoreGroupId' => 'SCORE_GROUP_ID',
        'aggregateMultipleScore.scoreGroupId' => 'SCORE_GROUP_ID',
        'AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID' => 'SCORE_GROUP_ID',
        'COL_SCORE_GROUP_ID' => 'SCORE_GROUP_ID',
        'score_group_id' => 'SCORE_GROUP_ID',
        'aggregate_multiple_score.score_group_id' => 'SCORE_GROUP_ID',
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
        $this->setName('aggregate_multiple_score');
        $this->setPhpName('AggregateMultipleScore');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScore');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('score', 'Score', 'INTEGER', false, null, 0);
        $this->addColumn('scored_at', 'ScoredAt', 'DATE', false, null, null);
        $this->addForeignKey('score_group_id', 'ScoreGroupId', 'INTEGER', 'aggregate_multiple_score_group', 'id', false, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('AggregateMultipleScoreGroup', '\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScoreGroup', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':score_group_id',
    1 => ':id',
  ),
), 'SET NULL', 'CASCADE', null, false);
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
            'aggregate_multiple_columns_relation_score_group_aggregates' => ['foreign_table' => 'aggregate_multiple_score_group', 'update_method' => 'updateAggregatedColumnsFromAggregateMultipleScore', 'aggregate_name' => 'AggregatedColumnsFromAggregateMultipleScore'],
            'aggregate_multiple_columns_relation_another_score_group_aggregates' => ['foreign_table' => 'aggregate_multiple_score_group', 'update_method' => 'updateAggregatedColumnsFromAggregateMultipleScore1', 'aggregate_name' => 'AggregatedColumnsFromAggregateMultipleScore1'],
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
        return $withPrefix ? AggregateMultipleScoreTableMap::CLASS_DEFAULT : AggregateMultipleScoreTableMap::OM_CLASS;
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
     * @return array (AggregateMultipleScore object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = AggregateMultipleScoreTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = AggregateMultipleScoreTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + AggregateMultipleScoreTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = AggregateMultipleScoreTableMap::OM_CLASS;
            /** @var AggregateMultipleScore $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            AggregateMultipleScoreTableMap::addInstanceToPool($obj, $key);
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
            $key = AggregateMultipleScoreTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = AggregateMultipleScoreTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var AggregateMultipleScore $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                AggregateMultipleScoreTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(AggregateMultipleScoreTableMap::COL_ID);
            $criteria->addSelectColumn(AggregateMultipleScoreTableMap::COL_SCORE);
            $criteria->addSelectColumn(AggregateMultipleScoreTableMap::COL_SCORED_AT);
            $criteria->addSelectColumn(AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.score');
            $criteria->addSelectColumn($alias . '.scored_at');
            $criteria->addSelectColumn($alias . '.score_group_id');
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
            $criteria->removeSelectColumn(AggregateMultipleScoreTableMap::COL_ID);
            $criteria->removeSelectColumn(AggregateMultipleScoreTableMap::COL_SCORE);
            $criteria->removeSelectColumn(AggregateMultipleScoreTableMap::COL_SCORED_AT);
            $criteria->removeSelectColumn(AggregateMultipleScoreTableMap::COL_SCORE_GROUP_ID);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.score');
            $criteria->removeSelectColumn($alias . '.scored_at');
            $criteria->removeSelectColumn($alias . '.score_group_id');
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
        return Propel::getServiceContainer()->getDatabaseMap(AggregateMultipleScoreTableMap::DATABASE_NAME)->getTable(AggregateMultipleScoreTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a AggregateMultipleScore or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or AggregateMultipleScore object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\AggregateMultipleScore) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(AggregateMultipleScoreTableMap::DATABASE_NAME);
            $criteria->add(AggregateMultipleScoreTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = AggregateMultipleScoreQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            AggregateMultipleScoreTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                AggregateMultipleScoreTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the aggregate_multiple_score table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return AggregateMultipleScoreQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a AggregateMultipleScore or Criteria object.
     *
     * @param mixed $criteria Criteria or AggregateMultipleScore object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from AggregateMultipleScore object
        }

        if ($criteria->containsKey(AggregateMultipleScoreTableMap::COL_ID) && $criteria->keyContainsValue(AggregateMultipleScoreTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.AggregateMultipleScoreTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = AggregateMultipleScoreQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
