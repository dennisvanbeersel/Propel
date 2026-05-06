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
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery;


/**
 * This class defines the structure of the 'aggregate_multiple_score_group' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class AggregateMultipleScoreGroupTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.AggregateMultipleScoreGroupTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'aggregate_multiple_score_group';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'AggregateMultipleScoreGroup';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScoreGroup';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.AggregateMultipleScoreGroup';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 10;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 10;

    /**
     * the column name for the id field
     */
    public const COL_ID = 'aggregate_multiple_score_group.id';

    /**
     * the column name for the first_score_at field
     */
    public const COL_FIRST_SCORE_AT = 'aggregate_multiple_score_group.first_score_at';

    /**
     * the column name for the last_score_at field
     */
    public const COL_LAST_SCORE_AT = 'aggregate_multiple_score_group.last_score_at';

    /**
     * the column name for the total_score field
     */
    public const COL_TOTAL_SCORE = 'aggregate_multiple_score_group.total_score';

    /**
     * the column name for the number_of_scores field
     */
    public const COL_NUMBER_OF_SCORES = 'aggregate_multiple_score_group.number_of_scores';

    /**
     * the column name for the avg_score field
     */
    public const COL_AVG_SCORE = 'aggregate_multiple_score_group.avg_score';

    /**
     * the column name for the min_score field
     */
    public const COL_MIN_SCORE = 'aggregate_multiple_score_group.min_score';

    /**
     * the column name for the max_score field
     */
    public const COL_MAX_SCORE = 'aggregate_multiple_score_group.max_score';

    /**
     * the column name for the total_big_score field
     */
    public const COL_TOTAL_BIG_SCORE = 'aggregate_multiple_score_group.total_big_score';

    /**
     * the column name for the number_of_big_scores field
     */
    public const COL_NUMBER_OF_BIG_SCORES = 'aggregate_multiple_score_group.number_of_big_scores';

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
        self::TYPE_PHPNAME       => ['Id', 'FirstScoreAt', 'LastScoreAt', 'TotalScore', 'NumberOfScores', 'AvgScore', 'MinScore', 'MaxScore', 'TotalBigScore', 'NumberOfBigScores', ],
        self::TYPE_CAMELNAME     => ['id', 'firstScoreAt', 'lastScoreAt', 'totalScore', 'numberOfScores', 'avgScore', 'minScore', 'maxScore', 'totalBigScore', 'numberOfBigScores', ],
        self::TYPE_COLNAME       => [AggregateMultipleScoreGroupTableMap::COL_ID, AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT, AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT, AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE, AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES, AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE, AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE, AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE, AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE, AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES, ],
        self::TYPE_FIELDNAME     => ['id', 'first_score_at', 'last_score_at', 'total_score', 'number_of_scores', 'avg_score', 'min_score', 'max_score', 'total_big_score', 'number_of_big_scores', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ]
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'FirstScoreAt' => 1, 'LastScoreAt' => 2, 'TotalScore' => 3, 'NumberOfScores' => 4, 'AvgScore' => 5, 'MinScore' => 6, 'MaxScore' => 7, 'TotalBigScore' => 8, 'NumberOfBigScores' => 9, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'firstScoreAt' => 1, 'lastScoreAt' => 2, 'totalScore' => 3, 'numberOfScores' => 4, 'avgScore' => 5, 'minScore' => 6, 'maxScore' => 7, 'totalBigScore' => 8, 'numberOfBigScores' => 9, ],
        self::TYPE_COLNAME       => [AggregateMultipleScoreGroupTableMap::COL_ID => 0, AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT => 1, AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT => 2, AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE => 3, AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES => 4, AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE => 5, AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE => 6, AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE => 7, AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE => 8, AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES => 9, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'first_score_at' => 1, 'last_score_at' => 2, 'total_score' => 3, 'number_of_scores' => 4, 'avg_score' => 5, 'min_score' => 6, 'max_score' => 7, 'total_big_score' => 8, 'number_of_big_scores' => 9, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'AggregateMultipleScoreGroup.Id' => 'ID',
        'id' => 'ID',
        'aggregateMultipleScoreGroup.id' => 'ID',
        'AggregateMultipleScoreGroupTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'aggregate_multiple_score_group.id' => 'ID',
        'FirstScoreAt' => 'FIRST_SCORE_AT',
        'AggregateMultipleScoreGroup.FirstScoreAt' => 'FIRST_SCORE_AT',
        'firstScoreAt' => 'FIRST_SCORE_AT',
        'aggregateMultipleScoreGroup.firstScoreAt' => 'FIRST_SCORE_AT',
        'AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT' => 'FIRST_SCORE_AT',
        'COL_FIRST_SCORE_AT' => 'FIRST_SCORE_AT',
        'first_score_at' => 'FIRST_SCORE_AT',
        'aggregate_multiple_score_group.first_score_at' => 'FIRST_SCORE_AT',
        'LastScoreAt' => 'LAST_SCORE_AT',
        'AggregateMultipleScoreGroup.LastScoreAt' => 'LAST_SCORE_AT',
        'lastScoreAt' => 'LAST_SCORE_AT',
        'aggregateMultipleScoreGroup.lastScoreAt' => 'LAST_SCORE_AT',
        'AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT' => 'LAST_SCORE_AT',
        'COL_LAST_SCORE_AT' => 'LAST_SCORE_AT',
        'last_score_at' => 'LAST_SCORE_AT',
        'aggregate_multiple_score_group.last_score_at' => 'LAST_SCORE_AT',
        'TotalScore' => 'TOTAL_SCORE',
        'AggregateMultipleScoreGroup.TotalScore' => 'TOTAL_SCORE',
        'totalScore' => 'TOTAL_SCORE',
        'aggregateMultipleScoreGroup.totalScore' => 'TOTAL_SCORE',
        'AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE' => 'TOTAL_SCORE',
        'COL_TOTAL_SCORE' => 'TOTAL_SCORE',
        'total_score' => 'TOTAL_SCORE',
        'aggregate_multiple_score_group.total_score' => 'TOTAL_SCORE',
        'NumberOfScores' => 'NUMBER_OF_SCORES',
        'AggregateMultipleScoreGroup.NumberOfScores' => 'NUMBER_OF_SCORES',
        'numberOfScores' => 'NUMBER_OF_SCORES',
        'aggregateMultipleScoreGroup.numberOfScores' => 'NUMBER_OF_SCORES',
        'AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES' => 'NUMBER_OF_SCORES',
        'COL_NUMBER_OF_SCORES' => 'NUMBER_OF_SCORES',
        'number_of_scores' => 'NUMBER_OF_SCORES',
        'aggregate_multiple_score_group.number_of_scores' => 'NUMBER_OF_SCORES',
        'AvgScore' => 'AVG_SCORE',
        'AggregateMultipleScoreGroup.AvgScore' => 'AVG_SCORE',
        'avgScore' => 'AVG_SCORE',
        'aggregateMultipleScoreGroup.avgScore' => 'AVG_SCORE',
        'AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE' => 'AVG_SCORE',
        'COL_AVG_SCORE' => 'AVG_SCORE',
        'avg_score' => 'AVG_SCORE',
        'aggregate_multiple_score_group.avg_score' => 'AVG_SCORE',
        'MinScore' => 'MIN_SCORE',
        'AggregateMultipleScoreGroup.MinScore' => 'MIN_SCORE',
        'minScore' => 'MIN_SCORE',
        'aggregateMultipleScoreGroup.minScore' => 'MIN_SCORE',
        'AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE' => 'MIN_SCORE',
        'COL_MIN_SCORE' => 'MIN_SCORE',
        'min_score' => 'MIN_SCORE',
        'aggregate_multiple_score_group.min_score' => 'MIN_SCORE',
        'MaxScore' => 'MAX_SCORE',
        'AggregateMultipleScoreGroup.MaxScore' => 'MAX_SCORE',
        'maxScore' => 'MAX_SCORE',
        'aggregateMultipleScoreGroup.maxScore' => 'MAX_SCORE',
        'AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE' => 'MAX_SCORE',
        'COL_MAX_SCORE' => 'MAX_SCORE',
        'max_score' => 'MAX_SCORE',
        'aggregate_multiple_score_group.max_score' => 'MAX_SCORE',
        'TotalBigScore' => 'TOTAL_BIG_SCORE',
        'AggregateMultipleScoreGroup.TotalBigScore' => 'TOTAL_BIG_SCORE',
        'totalBigScore' => 'TOTAL_BIG_SCORE',
        'aggregateMultipleScoreGroup.totalBigScore' => 'TOTAL_BIG_SCORE',
        'AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE' => 'TOTAL_BIG_SCORE',
        'COL_TOTAL_BIG_SCORE' => 'TOTAL_BIG_SCORE',
        'total_big_score' => 'TOTAL_BIG_SCORE',
        'aggregate_multiple_score_group.total_big_score' => 'TOTAL_BIG_SCORE',
        'NumberOfBigScores' => 'NUMBER_OF_BIG_SCORES',
        'AggregateMultipleScoreGroup.NumberOfBigScores' => 'NUMBER_OF_BIG_SCORES',
        'numberOfBigScores' => 'NUMBER_OF_BIG_SCORES',
        'aggregateMultipleScoreGroup.numberOfBigScores' => 'NUMBER_OF_BIG_SCORES',
        'AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES' => 'NUMBER_OF_BIG_SCORES',
        'COL_NUMBER_OF_BIG_SCORES' => 'NUMBER_OF_BIG_SCORES',
        'number_of_big_scores' => 'NUMBER_OF_BIG_SCORES',
        'aggregate_multiple_score_group.number_of_big_scores' => 'NUMBER_OF_BIG_SCORES',
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
        $this->setName('aggregate_multiple_score_group');
        $this->setPhpName('AggregateMultipleScoreGroup');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScoreGroup');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('first_score_at', 'FirstScoreAt', 'DATE', false, null, null);
        $this->addColumn('last_score_at', 'LastScoreAt', 'DATE', false, null, null);
        $this->addColumn('total_score', 'TotalScore', 'INTEGER', false, null, null);
        $this->addColumn('number_of_scores', 'NumberOfScores', 'INTEGER', false, null, null);
        $this->addColumn('avg_score', 'AvgScore', 'INTEGER', false, null, null);
        $this->addColumn('min_score', 'MinScore', 'INTEGER', false, null, null);
        $this->addColumn('max_score', 'MaxScore', 'INTEGER', false, null, null);
        $this->addColumn('total_big_score', 'TotalBigScore', 'INTEGER', false, null, null);
        $this->addColumn('number_of_big_scores', 'NumberOfBigScores', 'INTEGER', false, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('AggregateMultipleScore', '\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScore', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':score_group_id',
    1 => ':id',
  ),
), 'SET NULL', 'CASCADE', 'AggregateMultipleScores', false);
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
            'score_group_aggregates' => ['foreign_table' => 'aggregate_multiple_score', 'foreign_schema' => NULL, 'condition' => NULL, 'columns' => [0 => ['column_name' => 'total_score', 'expression' => 'SUM(score)'], 1 => ['column_name' => 'number_of_scores', 'expression' => 'COUNT(score)'], 2 => ['column_name' => 'avg_score', 'expression' => 'AVG(score)'], 3 => ['column_name' => 'min_score', 'expression' => 'MIN(score)'], 4 => ['column_name' => 'max_score', 'expression' => 'MAX(score)'], 5 => ['column_name' => 'first_score_at', 'expression' => 'MIN(scored_at)'], 6 => ['column_name' => 'last_score_at', 'expression' => 'MAX(scored_at)']]],
            'another_score_group_aggregates' => ['foreign_table' => 'aggregate_multiple_score', 'foreign_schema' => NULL, 'condition' => 'score > 20', 'columns' => [0 => ['column_name' => 'total_big_score', 'expression' => 'SUM(score)'], 1 => ['column_name' => 'number_of_big_scores', 'expression' => 'COUNT(score)']]],
        ];
    }

    /**
     * Method to invalidate the instance pool of all tables related to aggregate_multiple_score_group     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        AggregateMultipleScoreTableMap::clearInstancePool();
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
        return $withPrefix ? AggregateMultipleScoreGroupTableMap::CLASS_DEFAULT : AggregateMultipleScoreGroupTableMap::OM_CLASS;
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
     * @return array (AggregateMultipleScoreGroup object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = AggregateMultipleScoreGroupTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = AggregateMultipleScoreGroupTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + AggregateMultipleScoreGroupTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = AggregateMultipleScoreGroupTableMap::OM_CLASS;
            /** @var AggregateMultipleScoreGroup $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            AggregateMultipleScoreGroupTableMap::addInstanceToPool($obj, $key);
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
            $key = AggregateMultipleScoreGroupTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = AggregateMultipleScoreGroupTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var AggregateMultipleScoreGroup $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                AggregateMultipleScoreGroupTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_ID);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE);
            $criteria->addSelectColumn(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.first_score_at');
            $criteria->addSelectColumn($alias . '.last_score_at');
            $criteria->addSelectColumn($alias . '.total_score');
            $criteria->addSelectColumn($alias . '.number_of_scores');
            $criteria->addSelectColumn($alias . '.avg_score');
            $criteria->addSelectColumn($alias . '.min_score');
            $criteria->addSelectColumn($alias . '.max_score');
            $criteria->addSelectColumn($alias . '.total_big_score');
            $criteria->addSelectColumn($alias . '.number_of_big_scores');
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
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_ID);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE);
            $criteria->removeSelectColumn(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.first_score_at');
            $criteria->removeSelectColumn($alias . '.last_score_at');
            $criteria->removeSelectColumn($alias . '.total_score');
            $criteria->removeSelectColumn($alias . '.number_of_scores');
            $criteria->removeSelectColumn($alias . '.avg_score');
            $criteria->removeSelectColumn($alias . '.min_score');
            $criteria->removeSelectColumn($alias . '.max_score');
            $criteria->removeSelectColumn($alias . '.total_big_score');
            $criteria->removeSelectColumn($alias . '.number_of_big_scores');
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
        return Propel::getServiceContainer()->getDatabaseMap(AggregateMultipleScoreGroupTableMap::DATABASE_NAME)->getTable(AggregateMultipleScoreGroupTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a AggregateMultipleScoreGroup or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or AggregateMultipleScoreGroup object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = AggregateMultipleScoreGroupQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            AggregateMultipleScoreGroupTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                AggregateMultipleScoreGroupTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the aggregate_multiple_score_group table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return AggregateMultipleScoreGroupQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a AggregateMultipleScoreGroup or Criteria object.
     *
     * @param mixed $criteria Criteria or AggregateMultipleScoreGroup object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from AggregateMultipleScoreGroup object
        }

        if ($criteria->containsKey(AggregateMultipleScoreGroupTableMap::COL_ID) && $criteria->keyContainsValue(AggregateMultipleScoreGroupTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.AggregateMultipleScoreGroupTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = AggregateMultipleScoreGroupQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
