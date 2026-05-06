<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Base;

use \DateTime;
use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScore as ChildAggregateMultipleScore;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup as ChildAggregateMultipleScoreGroup;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroupQuery as ChildAggregateMultipleScoreGroupQuery;
use Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreQuery as ChildAggregateMultipleScoreQuery;
use Propel\Tests\Bookstore\Behavior\Map\AggregateMultipleScoreGroupTableMap;
use Propel\Tests\Bookstore\Behavior\Map\AggregateMultipleScoreTableMap;

/**
 * Base class that represents a row from the 'aggregate_multiple_score_group' table.
 *
 *
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Behavior.Base
 */
abstract class AggregateMultipleScoreGroup implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Behavior\\Map\\AggregateMultipleScoreGroupTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var bool
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var bool
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = [];

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = [];

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected ?int $id = null;

    /**
     * The value for the first_score_at field.
     *
     * @var        DateTime|null
     */
    protected $first_score_at;

    /**
     * The value for the last_score_at field.
     *
     * @var        DateTime|null
     */
    protected $last_score_at;

    /**
     * The value for the total_score field.
     *
     * @var        int|null
     */
    protected ?int $total_score = null;

    /**
     * The value for the number_of_scores field.
     *
     * @var        int|null
     */
    protected ?int $number_of_scores = null;

    /**
     * The value for the avg_score field.
     *
     * @var        int|null
     */
    protected ?int $avg_score = null;

    /**
     * The value for the min_score field.
     *
     * @var        int|null
     */
    protected ?int $min_score = null;

    /**
     * The value for the max_score field.
     *
     * @var        int|null
     */
    protected ?int $max_score = null;

    /**
     * The value for the total_big_score field.
     *
     * @var        int|null
     */
    protected ?int $total_big_score = null;

    /**
     * The value for the number_of_big_scores field.
     *
     * @var        int|null
     */
    protected ?int $number_of_big_scores = null;

    /**
     * @var        ObjectCollection|ChildAggregateMultipleScore[] Collection to store aggregation of ChildAggregateMultipleScore objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildAggregateMultipleScore> Collection to store aggregation of ChildAggregateMultipleScore objects.
     */
    protected $collAggregateMultipleScores;
    protected bool $collAggregateMultipleScoresPartial = false;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildAggregateMultipleScore[]
     * @phpstan-var ObjectCollection&\Traversable<ChildAggregateMultipleScore>
     */
    protected $aggregateMultipleScoresScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Behavior\Base\AggregateMultipleScoreGroup object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return bool True if the object has been modified.
     */
    public function isModified(): bool
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param string $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return bool True if $col has been modified.
     */
    public function isColumnModified(string $col): bool
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns(): array
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return bool True, if the object has never been persisted.
     */
    public function isNew(): bool
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param bool $b the state of the object.
     */
    public function setNew(bool $b): void
    {
        $this->new = $b;
    }

    /**
     * Whether this object has been deleted.
     * @return bool The deleted state of this object.
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param bool $b The deleted state of this object.
     * @return void
     */
    public function setDeleted(bool $b): void
    {
        $this->deleted = $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified(?string $col = null): void
    {
        if (null !== $col) {
            unset($this->modifiedColumns[$col]);
        } else {
            $this->modifiedColumns = [];
        }
    }

    /**
     * Compares this with another <code>AggregateMultipleScoreGroup</code> instance.  If
     * <code>obj</code> is an instance of <code>AggregateMultipleScoreGroup</code>, delegates to
     * <code>equals(AggregateMultipleScoreGroup)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param mixed $obj The object to compare to.
     * @return bool Whether equal to the object specified.
     */
    public function equals($obj): bool
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns(): array
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @return bool
     */
    public function hasVirtualColumn(string $name): bool
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @return mixed
     *
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getVirtualColumn(string $name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of nonexistent virtual column `%s`.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name The virtual column name
     * @param mixed $value The value to give to the virtual column
     *
     * @return $this The current object, for fluid interface
     */
    public function setVirtualColumn(string $name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param string $msg
     * @param int $priority One of the Propel::LOG_* logging levels
     * @return void
     */
    protected function log(string $msg, int $priority = Propel::LOG_INFO): void
    {
        Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param \Propel\Runtime\Parser\AbstractParser|string $parser An AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param bool $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @param string $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME, TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM. Defaults to TableMap::TYPE_PHPNAME.
     * @return string The exported data
     */
    public function exportTo($parser, bool $includeLazyLoadColumns = true, string $keyType = TableMap::TYPE_PHPNAME): string
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray($keyType, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     *
     * @return array<string>
     */
    public function __sleep(): array
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [optionally formatted] temporal [first_score_at] column value.
     *
     *
     * @param string|null $format The date/time format string (either date()-style or strftime()-style).
     *   If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime|null Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL.
     *
     * @throws \Propel\Runtime\Exception\PropelException - if unable to parse/validate the date/time value.
     *
     * @psalm-return ($format is null ? DateTime|null : string|null)
     */
    public function getFirstScoreAt(?string $format = null)
    {
        if ($format === null) {
            return $this->first_score_at;
        } else {
            return $this->first_score_at instanceof \DateTimeInterface ? $this->first_score_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [last_score_at] column value.
     *
     *
     * @param string|null $format The date/time format string (either date()-style or strftime()-style).
     *   If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime|null Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL.
     *
     * @throws \Propel\Runtime\Exception\PropelException - if unable to parse/validate the date/time value.
     *
     * @psalm-return ($format is null ? DateTime|null : string|null)
     */
    public function getLastScoreAt(?string $format = null)
    {
        if ($format === null) {
            return $this->last_score_at;
        } else {
            return $this->last_score_at instanceof \DateTimeInterface ? $this->last_score_at->format($format) : null;
        }
    }

    /**
     * Get the [total_score] column value.
     *
     * @return int|null
     */
    public function getTotalScore()
    {
        return $this->total_score;
    }

    /**
     * Get the [number_of_scores] column value.
     *
     * @return int|null
     */
    public function getNumberOfScores()
    {
        return $this->number_of_scores;
    }

    /**
     * Get the [avg_score] column value.
     *
     * @return int|null
     */
    public function getAvgScore()
    {
        return $this->avg_score;
    }

    /**
     * Get the [min_score] column value.
     *
     * @return int|null
     */
    public function getMinScore()
    {
        return $this->min_score;
    }

    /**
     * Get the [max_score] column value.
     *
     * @return int|null
     */
    public function getMaxScore()
    {
        return $this->max_score;
    }

    /**
     * Get the [total_big_score] column value.
     *
     * @return int|null
     */
    public function getTotalBigScore()
    {
        return $this->total_big_score;
    }

    /**
     * Get the [number_of_big_scores] column value.
     *
     * @return int|null
     */
    public function getNumberOfBigScores()
    {
        return $this->number_of_big_scores;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Sets the value of [first_score_at] column to a normalized version of the date/time value specified.
     *
     * @param string|integer|\DateTimeInterface|null $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this The current object (for fluent API support)
     */
    public function setFirstScoreAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->first_score_at !== null || $dt !== null) {
            if ($this->first_score_at === null || $dt === null || $dt->format("Y-m-d") !== $this->first_score_at->format("Y-m-d")) {
                $this->first_score_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT] = true;
            }
        } // if either are not null

        return $this;
    }

    /**
     * Sets the value of [last_score_at] column to a normalized version of the date/time value specified.
     *
     * @param string|integer|\DateTimeInterface|null $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this The current object (for fluent API support)
     */
    public function setLastScoreAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->last_score_at !== null || $dt !== null) {
            if ($this->last_score_at === null || $dt === null || $dt->format("Y-m-d") !== $this->last_score_at->format("Y-m-d")) {
                $this->last_score_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT] = true;
            }
        } // if either are not null

        return $this;
    }

    /**
     * Set the value of [total_score] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setTotalScore($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->total_score !== $v) {
            $this->total_score = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [number_of_scores] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setNumberOfScores($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->number_of_scores !== $v) {
            $this->number_of_scores = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES] = true;
        }

        return $this;
    }

    /**
     * Set the value of [avg_score] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setAvgScore($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->avg_score !== $v) {
            $this->avg_score = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [min_score] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setMinScore($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->min_score !== $v) {
            $this->min_score = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [max_score] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setMaxScore($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->max_score !== $v) {
            $this->max_score = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [total_big_score] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setTotalBigScore($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->total_big_score !== $v) {
            $this->total_big_score = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [number_of_big_scores] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setNumberOfBigScores($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->number_of_big_scores !== $v) {
            $this->number_of_big_scores = $v;
            $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES] = true;
        }

        return $this;
    }

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return bool Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues(): bool
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    }

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by DataFetcher->fetch().
     * @param int $startcol 0-based offset column which indicates which resultset column to start with.
     * @param bool $rehydrate Whether this object is being re-hydrated from the database.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int next starting column
     * @throws \Propel\Runtime\Exception\PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate(array $row, int $startcol = 0, bool $rehydrate = false, string $indexType = TableMap::TYPE_NUM): int
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('FirstScoreAt', TableMap::TYPE_PHPNAME, $indexType)];
            $this->first_score_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('LastScoreAt', TableMap::TYPE_PHPNAME, $indexType)];
            $this->last_score_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('TotalScore', TableMap::TYPE_PHPNAME, $indexType)];
            $this->total_score = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('NumberOfScores', TableMap::TYPE_PHPNAME, $indexType)];
            $this->number_of_scores = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('AvgScore', TableMap::TYPE_PHPNAME, $indexType)];
            $this->avg_score = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('MinScore', TableMap::TYPE_PHPNAME, $indexType)];
            $this->min_score = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('MaxScore', TableMap::TYPE_PHPNAME, $indexType)];
            $this->max_score = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('TotalBigScore', TableMap::TYPE_PHPNAME, $indexType)];
            $this->total_big_score = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : AggregateMultipleScoreGroupTableMap::translateFieldName('NumberOfBigScores', TableMap::TYPE_PHPNAME, $indexType)];
            $this->number_of_big_scores = (null !== $col) ? (int) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 10; // 10 = AggregateMultipleScoreGroupTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Behavior\\AggregateMultipleScoreGroup'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function ensureConsistency(): void
    {
    }

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param bool $deep (optional) Whether to also de-associated any related objects.
     * @param ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload(bool $deep = false, ?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildAggregateMultipleScoreGroupQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collAggregateMultipleScores = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see AggregateMultipleScoreGroup::setDeleted()
     * @see AggregateMultipleScoreGroup::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildAggregateMultipleScoreGroupQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param ConnectionInterface $con
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see doSave()
     */
    public function save(?ConnectionInterface $con = null): int
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                AggregateMultipleScoreGroupTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param ConnectionInterface $con
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con): int
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->aggregateMultipleScoresScheduledForDeletion !== null) {
                if (!$this->aggregateMultipleScoresScheduledForDeletion->isEmpty()) {
                    foreach ($this->aggregateMultipleScoresScheduledForDeletion as $aggregateMultipleScore) {
                        // need to save related object because we set the relation to null
                        $aggregateMultipleScore->save($con);
                    }
                    $this->aggregateMultipleScoresScheduledForDeletion = null;
                }
            }

            if ($this->collAggregateMultipleScores !== null) {
                foreach ($this->collAggregateMultipleScores as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    }

    /**
     * Insert the row in the database.
     *
     * @param ConnectionInterface $con
     *
     * @throws \Propel\Runtime\Exception\PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con): void
    {
        $modifiedColumns = [];
        $index = 0;

        $this->modifiedColumns[AggregateMultipleScoreGroupTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . AggregateMultipleScoreGroupTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT)) {
            $modifiedColumns[':p' . $index++]  = 'first_score_at';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT)) {
            $modifiedColumns[':p' . $index++]  = 'last_score_at';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE)) {
            $modifiedColumns[':p' . $index++]  = 'total_score';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES)) {
            $modifiedColumns[':p' . $index++]  = 'number_of_scores';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE)) {
            $modifiedColumns[':p' . $index++]  = 'avg_score';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE)) {
            $modifiedColumns[':p' . $index++]  = 'min_score';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE)) {
            $modifiedColumns[':p' . $index++]  = 'max_score';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE)) {
            $modifiedColumns[':p' . $index++]  = 'total_big_score';
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES)) {
            $modifiedColumns[':p' . $index++]  = 'number_of_big_scores';
        }

        $sql = sprintf(
            'INSERT INTO aggregate_multiple_score_group (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'id':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);

                        break;
                    case 'first_score_at':
                        $stmt->bindValue($identifier, $this->first_score_at ? $this->first_score_at->format("Y-m-d") : null, PDO::PARAM_STR);

                        break;
                    case 'last_score_at':
                        $stmt->bindValue($identifier, $this->last_score_at ? $this->last_score_at->format("Y-m-d") : null, PDO::PARAM_STR);

                        break;
                    case 'total_score':
                        $stmt->bindValue($identifier, $this->total_score, PDO::PARAM_INT);

                        break;
                    case 'number_of_scores':
                        $stmt->bindValue($identifier, $this->number_of_scores, PDO::PARAM_INT);

                        break;
                    case 'avg_score':
                        $stmt->bindValue($identifier, $this->avg_score, PDO::PARAM_INT);

                        break;
                    case 'min_score':
                        $stmt->bindValue($identifier, $this->min_score, PDO::PARAM_INT);

                        break;
                    case 'max_score':
                        $stmt->bindValue($identifier, $this->max_score, PDO::PARAM_INT);

                        break;
                    case 'total_big_score':
                        $stmt->bindValue($identifier, $this->total_big_score, PDO::PARAM_INT);

                        break;
                    case 'number_of_big_scores':
                        $stmt->bindValue($identifier, $this->number_of_big_scores, PDO::PARAM_INT);

                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param ConnectionInterface $con
     *
     * @return int Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con): int
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName(string $name, string $type = TableMap::TYPE_PHPNAME)
    {
        $pos = (int)AggregateMultipleScoreGroupTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos Position in XML schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition(int $pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();

            case 1:
                return $this->getFirstScoreAt();

            case 2:
                return $this->getLastScoreAt();

            case 3:
                return $this->getTotalScore();

            case 4:
                return $this->getNumberOfScores();

            case 5:
                return $this->getAvgScore();

            case 6:
                return $this->getMinScore();

            case 7:
                return $this->getMaxScore();

            case 8:
                return $this->getTotalBigScore();

            case 9:
                return $this->getNumberOfBigScores();

            default:
                return null;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param string $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param bool $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param bool $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array An associative array containing the field names (as keys) and field values
     */
    public function toArray(string $keyType = TableMap::TYPE_PHPNAME, bool $includeLazyLoadColumns = true, array $alreadyDumpedObjects = [], bool $includeForeignObjects = false): array
    {
        if (isset($alreadyDumpedObjects['AggregateMultipleScoreGroup'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['AggregateMultipleScoreGroup'][$this->hashCode()] = true;
        $keys = AggregateMultipleScoreGroupTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getFirstScoreAt(),
            $keys[2] => $this->getLastScoreAt(),
            $keys[3] => $this->getTotalScore(),
            $keys[4] => $this->getNumberOfScores(),
            $keys[5] => $this->getAvgScore(),
            $keys[6] => $this->getMinScore(),
            $keys[7] => $this->getMaxScore(),
            $keys[8] => $this->getTotalBigScore(),
            $keys[9] => $this->getNumberOfBigScores(),
        ];
        if ($result[$keys[1]] instanceof \DateTimeInterface) {
            $result[$keys[1]] = $result[$keys[1]]->format('Y-m-d');
        }

        if ($result[$keys[2]] instanceof \DateTimeInterface) {
            $result[$keys[2]] = $result[$keys[2]]->format('Y-m-d');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collAggregateMultipleScores) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'aggregateMultipleScores';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'aggregate_multiple_scores';
                        break;
                    default:
                        $key = 'AggregateMultipleScores';
                }

                $result[$key] = $this->collAggregateMultipleScores->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this
     */
    public function setByName(string $name, $value, string $type = TableMap::TYPE_PHPNAME)
    {
        $pos = (int)AggregateMultipleScoreGroupTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        $this->setByPosition($pos, $value);

        return $this;
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return $this
     */
    public function setByPosition(int $pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setFirstScoreAt($value);
                break;
            case 2:
                $this->setLastScoreAt($value);
                break;
            case 3:
                $this->setTotalScore($value);
                break;
            case 4:
                $this->setNumberOfScores($value);
                break;
            case 5:
                $this->setAvgScore($value);
                break;
            case 6:
                $this->setMinScore($value);
                break;
            case 7:
                $this->setMaxScore($value);
                break;
            case 8:
                $this->setTotalBigScore($value);
                break;
            case 9:
                $this->setNumberOfBigScores($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param array $arr An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return $this
     */
    public function fromArray(array $arr, string $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = AggregateMultipleScoreGroupTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setFirstScoreAt($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setLastScoreAt($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setTotalScore($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setNumberOfScores($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setAvgScore($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setMinScore($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setMaxScore($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setTotalBigScore($arr[$keys[8]]);
        }
        if (array_key_exists($keys[9], $arr)) {
            $this->setNumberOfBigScores($arr[$keys[9]]);
        }

        return $this;
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this The current object, for fluid interface
     */
    public function importFrom($parser, string $data, string $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return \Propel\Runtime\ActiveQuery\Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria(): Criteria
    {
        $criteria = new Criteria(AggregateMultipleScoreGroupTableMap::DATABASE_NAME);

        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_ID)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_FIRST_SCORE_AT, $this->first_score_at);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_LAST_SCORE_AT, $this->last_score_at);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_TOTAL_SCORE, $this->total_score);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_SCORES, $this->number_of_scores);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_AVG_SCORE, $this->avg_score);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_MIN_SCORE, $this->min_score);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_MAX_SCORE, $this->max_score);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_TOTAL_BIG_SCORE, $this->total_big_score);
        }
        if ($this->isColumnModified(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES)) {
            $criteria->add(AggregateMultipleScoreGroupTableMap::COL_NUMBER_OF_BIG_SCORES, $this->number_of_big_scores);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return \Propel\Runtime\ActiveQuery\Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria(): Criteria
    {
        $criteria = ChildAggregateMultipleScoreGroupQuery::create();
        $criteria->add(AggregateMultipleScoreGroupTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int|string Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param int|null $key Primary key.
     * @return void
     */
    public function setPrimaryKey(?int $key = null): void
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     *
     * @return bool
     */
    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setFirstScoreAt($this->getFirstScoreAt());
        $copyObj->setLastScoreAt($this->getLastScoreAt());
        $copyObj->setTotalScore($this->getTotalScore());
        $copyObj->setNumberOfScores($this->getNumberOfScores());
        $copyObj->setAvgScore($this->getAvgScore());
        $copyObj->setMinScore($this->getMinScore());
        $copyObj->setMaxScore($this->getMaxScore());
        $copyObj->setTotalBigScore($this->getTotalBigScore());
        $copyObj->setNumberOfBigScores($this->getNumberOfBigScores());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getAggregateMultipleScores() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAggregateMultipleScore($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \Propel\Tests\Bookstore\Behavior\AggregateMultipleScoreGroup Clone of current object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function copy(bool $deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName): void
    {
        if ('AggregateMultipleScore' === $relationName) {
            $this->initAggregateMultipleScores();
            return;
        }
    }

    /**
     * Clears out the collAggregateMultipleScores collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addAggregateMultipleScores()
     */
    public function clearAggregateMultipleScores()
    {
        $this->collAggregateMultipleScores = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collAggregateMultipleScores collection loaded partially.
     *
     * @return void
     */
    public function resetPartialAggregateMultipleScores($v = true): void
    {
        $this->collAggregateMultipleScoresPartial = $v;
    }

    /**
     * Initializes the collAggregateMultipleScores collection.
     *
     * By default this just sets the collAggregateMultipleScores collection to an empty array (like clearcollAggregateMultipleScores());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAggregateMultipleScores(bool $overrideExisting = true): void
    {
        if (null !== $this->collAggregateMultipleScores && !$overrideExisting) {
            return;
        }

        $collectionClassName = AggregateMultipleScoreTableMap::getTableMap()->getCollectionClassName();

        $this->collAggregateMultipleScores = new $collectionClassName;
        $this->collAggregateMultipleScores->setModel('\Propel\Tests\Bookstore\Behavior\AggregateMultipleScore');
    }

    /**
     * Gets an array of ChildAggregateMultipleScore objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildAggregateMultipleScoreGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildAggregateMultipleScore[] List of ChildAggregateMultipleScore objects
     * @phpstan-return ObjectCollection&\Traversable<ChildAggregateMultipleScore> List of ChildAggregateMultipleScore objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getAggregateMultipleScores(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collAggregateMultipleScoresPartial && !$this->isNew();
        if (null === $this->collAggregateMultipleScores || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collAggregateMultipleScores) {
                    $this->initAggregateMultipleScores();
                } else {
                    $collectionClassName = AggregateMultipleScoreTableMap::getTableMap()->getCollectionClassName();

                    $collAggregateMultipleScores = new $collectionClassName;
                    $collAggregateMultipleScores->setModel('\Propel\Tests\Bookstore\Behavior\AggregateMultipleScore');

                    return $collAggregateMultipleScores;
                }
            } else {
                $collAggregateMultipleScores = ChildAggregateMultipleScoreQuery::create(null, $criteria)
                    ->filterByAggregateMultipleScoreGroup($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collAggregateMultipleScoresPartial && count($collAggregateMultipleScores)) {
                        $this->initAggregateMultipleScores(false);

                        foreach ($collAggregateMultipleScores as $obj) {
                            if (false === $this->collAggregateMultipleScores->contains($obj)) {
                                $this->collAggregateMultipleScores->append($obj);
                            }
                        }

                        $this->collAggregateMultipleScoresPartial = true;
                    }

                    return $collAggregateMultipleScores;
                }

                if ($partial && $this->collAggregateMultipleScores) {
                    foreach ($this->collAggregateMultipleScores as $obj) {
                        if ($obj->isNew()) {
                            $collAggregateMultipleScores[] = $obj;
                        }
                    }
                }

                $this->collAggregateMultipleScores = $collAggregateMultipleScores;
                $this->collAggregateMultipleScoresPartial = false;
            }
        }

        return $this->collAggregateMultipleScores;
    }

    /**
     * Sets a collection of ChildAggregateMultipleScore objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $aggregateMultipleScores A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setAggregateMultipleScores(Collection $aggregateMultipleScores, ?ConnectionInterface $con = null)
    {
        /** @var ChildAggregateMultipleScore[] $aggregateMultipleScoresToDelete */
        $aggregateMultipleScoresToDelete = $this->getAggregateMultipleScores(new Criteria(), $con)->diff($aggregateMultipleScores);


        $this->aggregateMultipleScoresScheduledForDeletion = $aggregateMultipleScoresToDelete;

        foreach ($aggregateMultipleScoresToDelete as $aggregateMultipleScoreRemoved) {
            $aggregateMultipleScoreRemoved->setAggregateMultipleScoreGroup(null);
        }

        $this->collAggregateMultipleScores = null;
        foreach ($aggregateMultipleScores as $aggregateMultipleScore) {
            $this->addAggregateMultipleScore($aggregateMultipleScore);
        }

        $this->collAggregateMultipleScores = $aggregateMultipleScores;
        $this->collAggregateMultipleScoresPartial = false;

        return $this;
    }

    /**
     * Returns the number of related AggregateMultipleScore objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related AggregateMultipleScore objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countAggregateMultipleScores(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collAggregateMultipleScoresPartial && !$this->isNew();
        if (null === $this->collAggregateMultipleScores || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAggregateMultipleScores) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAggregateMultipleScores());
            }

            $query = ChildAggregateMultipleScoreQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAggregateMultipleScoreGroup($this)
                ->count($con);
        }

        return count($this->collAggregateMultipleScores);
    }

    /**
     * Method called to associate a ChildAggregateMultipleScore object to this object
     * through the ChildAggregateMultipleScore foreign key attribute.
     *
     * @param ChildAggregateMultipleScore $l ChildAggregateMultipleScore
     * @return $this The current object (for fluent API support)
     */
    public function addAggregateMultipleScore(ChildAggregateMultipleScore $l)
    {
        if ($this->collAggregateMultipleScores === null) {
            $this->initAggregateMultipleScores();
            $this->collAggregateMultipleScoresPartial = true;
        }

        if (!$this->collAggregateMultipleScores->contains($l)) {
            $this->doAddAggregateMultipleScore($l);

            if ($this->aggregateMultipleScoresScheduledForDeletion and $this->aggregateMultipleScoresScheduledForDeletion->contains($l)) {
                $this->aggregateMultipleScoresScheduledForDeletion->remove($this->aggregateMultipleScoresScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildAggregateMultipleScore $aggregateMultipleScore The ChildAggregateMultipleScore object to add.
     */
    protected function doAddAggregateMultipleScore(ChildAggregateMultipleScore $aggregateMultipleScore): void
    {
        $this->collAggregateMultipleScores[]= $aggregateMultipleScore;
        $aggregateMultipleScore->setAggregateMultipleScoreGroup($this);
    }

    /**
     * @param ChildAggregateMultipleScore $aggregateMultipleScore The ChildAggregateMultipleScore object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeAggregateMultipleScore(ChildAggregateMultipleScore $aggregateMultipleScore)
    {
        if ($this->getAggregateMultipleScores()->contains($aggregateMultipleScore)) {
            $pos = $this->collAggregateMultipleScores->search($aggregateMultipleScore);
            $this->collAggregateMultipleScores->remove($pos);
            if (null === $this->aggregateMultipleScoresScheduledForDeletion) {
                $this->aggregateMultipleScoresScheduledForDeletion = clone $this->collAggregateMultipleScores;
                $this->aggregateMultipleScoresScheduledForDeletion->clear();
            }
            $this->aggregateMultipleScoresScheduledForDeletion[]= $aggregateMultipleScore;
            $aggregateMultipleScore->setAggregateMultipleScoreGroup(null);
        }

        return $this;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     *
     * @return $this
     */
    public function clear()
    {
        $this->id = null;
        $this->first_score_at = null;
        $this->last_score_at = null;
        $this->total_score = null;
        $this->number_of_scores = null;
        $this->avg_score = null;
        $this->min_score = null;
        $this->max_score = null;
        $this->total_big_score = null;
        $this->number_of_big_scores = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);

        return $this;
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param bool $deep Whether to also clear the references on all referrer objects.
     * @return $this
     */
    public function clearAllReferences(bool $deep = false)
    {
        if ($deep) {
            if ($this->collAggregateMultipleScores) {
                foreach ($this->collAggregateMultipleScores as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collAggregateMultipleScores = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(AggregateMultipleScoreGroupTableMap::DEFAULT_STRING_FORMAT);
    }

    // score_group_aggregates behavior

    /**
     * Computes the value of the aggregate columns defined as AggregatedColumnsFromAggregateMultipleScore ?>
     *
     * @param ConnectionInterface $con A connection object
     *
     * @return array The result row from the aggregate query
     */
    public function computeAggregatedColumnsFromAggregateMultipleScore(ConnectionInterface $con)
    {
        $stmt = $con->prepare('SELECT SUM(score) AS TotalScore, COUNT(score) AS NumberOfScores, AVG(score) AS AvgScore, MIN(score) AS MinScore, MAX(score) AS MaxScore, MIN(scored_at) AS FirstScoreAt, MAX(scored_at) AS LastScoreAt FROM aggregate_multiple_score WHERE aggregate_multiple_score.SCORE_GROUP_ID = :p1');
        $stmt->bindValue(':p1', $this->getId());
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_NUM);
    }

    /**
     * Updates the aggregate columns defined as AggregatedColumnsFromAggregateMultipleScore *
     * @param ConnectionInterface $con A connection object
     */
    public function updateAggregatedColumnsFromAggregateMultipleScore(ConnectionInterface $con)
    {
        $aggregatedValues = $this->computeAggregatedColumnsFromAggregateMultipleScore($con);
        $this->setTotalScore($aggregatedValues[0]);
        $this->setNumberOfScores($aggregatedValues[1]);
        $this->setAvgScore($aggregatedValues[2]);
        $this->setMinScore($aggregatedValues[3]);
        $this->setMaxScore($aggregatedValues[4]);
        $this->setFirstScoreAt($aggregatedValues[5]);
        $this->setLastScoreAt($aggregatedValues[6]);
        $this->save($con);
    }

    // another_score_group_aggregates behavior

    /**
     * Computes the value of the aggregate columns defined as AggregatedColumnsFromAggregateMultipleScore1 ?>
     *
     * @param ConnectionInterface $con A connection object
     *
     * @return array The result row from the aggregate query
     */
    public function computeAggregatedColumnsFromAggregateMultipleScore1(ConnectionInterface $con)
    {
        $stmt = $con->prepare('SELECT SUM(score) AS TotalBigScore, COUNT(score) AS NumberOfBigScores FROM aggregate_multiple_score WHERE score > 20 AND aggregate_multiple_score.SCORE_GROUP_ID = :p1');
        $stmt->bindValue(':p1', $this->getId());
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_NUM);
    }

    /**
     * Updates the aggregate columns defined as AggregatedColumnsFromAggregateMultipleScore1 *
     * @param ConnectionInterface $con A connection object
     */
    public function updateAggregatedColumnsFromAggregateMultipleScore1(ConnectionInterface $con)
    {
        $aggregatedValues = $this->computeAggregatedColumnsFromAggregateMultipleScore1($con);
        $this->setTotalBigScore($aggregatedValues[0]);
        $this->setNumberOfBigScores($aggregatedValues[1]);
        $this->save($con);
    }

    /**
     * Code to be run before persisting the object
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preSave(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postSave(?ConnectionInterface $con = null): void
    {
            }

    /**
     * Code to be run before inserting to database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preInsert(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postInsert(?ConnectionInterface $con = null): void
    {
            }

    /**
     * Code to be run before updating the object in database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preUpdate(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postUpdate(?ConnectionInterface $con = null): void
    {
            }

    /**
     * Code to be run before deleting the object in database
     * @param ConnectionInterface|null $con
     * @return bool
     */
    public function preDelete(?ConnectionInterface $con = null): bool
    {
                return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface|null $con
     * @return void
     */
    public function postDelete(?ConnectionInterface $con = null): void
    {
            }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);
            $inputData = $params[0];
            $keyType = $params[1] ?? TableMap::TYPE_PHPNAME;

            return $this->importFrom($format, $inputData, $keyType);
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = $params[0] ?? true;
            $keyType = $params[1] ?? TableMap::TYPE_PHPNAME;

            return $this->exportTo($format, $includeLazyLoadColumns, $keyType);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
