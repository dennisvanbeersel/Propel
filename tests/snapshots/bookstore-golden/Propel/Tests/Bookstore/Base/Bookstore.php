<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Base;

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
use Propel\Tests\Bookstore\Bookstore as ChildBookstore;
use Propel\Tests\Bookstore\BookstoreContest as ChildBookstoreContest;
use Propel\Tests\Bookstore\BookstoreContestEntry as ChildBookstoreContestEntry;
use Propel\Tests\Bookstore\BookstoreContestEntryQuery as ChildBookstoreContestEntryQuery;
use Propel\Tests\Bookstore\BookstoreContestQuery as ChildBookstoreContestQuery;
use Propel\Tests\Bookstore\BookstoreQuery as ChildBookstoreQuery;
use Propel\Tests\Bookstore\BookstoreSale as ChildBookstoreSale;
use Propel\Tests\Bookstore\BookstoreSaleQuery as ChildBookstoreSaleQuery;
use Propel\Tests\Bookstore\Map\BookstoreContestEntryTableMap;
use Propel\Tests\Bookstore\Map\BookstoreContestTableMap;
use Propel\Tests\Bookstore\Map\BookstoreSaleTableMap;
use Propel\Tests\Bookstore\Map\BookstoreTableMap;

/**
 * Base class that represents a row from the 'bookstore' table.
 *
 *
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Base
 */
abstract class Bookstore implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Map\\BookstoreTableMap';


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
     * Book store ID number
     * @var        int
     */
    protected ?int $id = null;

    /**
     * The value for the store_name field.
     *
     * @var        string
     */
    protected ?string $store_name = null;

    /**
     * The value for the location field.
     *
     * @var        string|null
     */
    protected ?string $location = null;

    /**
     * The value for the population_served field.
     *
     * @var        string|null
     */
    protected ?string $population_served = null;

    /**
     * The value for the total_books field.
     *
     * @var        int|null
     */
    protected ?int $total_books = null;

    /**
     * The value for the store_open_time field.
     *
     * @var        DateTime|null
     */
    protected $store_open_time;

    /**
     * The value for the website field.
     *
     * @var        string|null
     */
    protected ?string $website = null;

    /**
     * @var        ObjectCollection|ChildBookstoreSale[] Collection to store aggregation of ChildBookstoreSale objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreSale> Collection to store aggregation of ChildBookstoreSale objects.
     */
    protected $collBookstoreSales;
    protected $collBookstoreSalesPartial;

    /**
     * @var        ObjectCollection|ChildBookstoreContest[] Collection to store aggregation of ChildBookstoreContest objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreContest> Collection to store aggregation of ChildBookstoreContest objects.
     */
    protected $collBookstoreContests;
    protected $collBookstoreContestsPartial;

    /**
     * @var        ObjectCollection|ChildBookstoreContestEntry[] Collection to store aggregation of ChildBookstoreContestEntry objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreContestEntry> Collection to store aggregation of ChildBookstoreContestEntry objects.
     */
    protected $collBookstoreContestEntries;
    protected $collBookstoreContestEntriesPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookstoreSale[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreSale>
     */
    protected $bookstoreSalesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookstoreContest[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreContest>
     */
    protected $bookstoreContestsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookstoreContestEntry[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreContestEntry>
     */
    protected $bookstoreContestEntriesScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Base\Bookstore object.
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
     * Compares this with another <code>Bookstore</code> instance.  If
     * <code>obj</code> is an instance of <code>Bookstore</code>, delegates to
     * <code>equals(Bookstore)</code>.  Otherwise, returns <code>false</code>.
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
     * Book store ID number
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [store_name] column value.
     *
     * @return string
     */
    public function getStoreName()
    {
        return $this->store_name;
    }

    /**
     * Get the [location] column value.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get the [population_served] column value.
     *
     * @return string|null
     */
    public function getPopulationServed()
    {
        return $this->population_served;
    }

    /**
     * Get the [total_books] column value.
     *
     * @return int|null
     */
    public function getTotalBooks()
    {
        return $this->total_books;
    }

    /**
     * Get the [optionally formatted] temporal [store_open_time] column value.
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
    public function getStoreOpenTime(?string $format = null)
    {
        if ($format === null) {
            return $this->store_open_time;
        } else {
            return $this->store_open_time instanceof \DateTimeInterface ? $this->store_open_time->format($format) : null;
        }
    }

    /**
     * Get the [website] column value.
     *
     * @return string|null
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set the value of [id] column.
     * Book store ID number
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
            $this->modifiedColumns[BookstoreTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [store_name] column.
     *
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setStoreName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->store_name !== $v) {
            $this->store_name = $v;
            $this->modifiedColumns[BookstoreTableMap::COL_STORE_NAME] = true;
        }

        return $this;
    }

    /**
     * Set the value of [location] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setLocation($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->location !== $v) {
            $this->location = $v;
            $this->modifiedColumns[BookstoreTableMap::COL_LOCATION] = true;
        }

        return $this;
    }

    /**
     * Set the value of [population_served] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPopulationServed($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->population_served !== $v) {
            $this->population_served = $v;
            $this->modifiedColumns[BookstoreTableMap::COL_POPULATION_SERVED] = true;
        }

        return $this;
    }

    /**
     * Set the value of [total_books] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setTotalBooks($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->total_books !== $v) {
            $this->total_books = $v;
            $this->modifiedColumns[BookstoreTableMap::COL_TOTAL_BOOKS] = true;
        }

        return $this;
    }

    /**
     * Sets the value of [store_open_time] column to a normalized version of the date/time value specified.
     *
     * @param string|integer|\DateTimeInterface|null $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this The current object (for fluent API support)
     */
    public function setStoreOpenTime($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->store_open_time !== null || $dt !== null) {
            if ($this->store_open_time === null || $dt === null || $dt->format("H:i:s.u") !== $this->store_open_time->format("H:i:s.u")) {
                $this->store_open_time = $dt === null ? null : clone $dt;
                $this->modifiedColumns[BookstoreTableMap::COL_STORE_OPEN_TIME] = true;
            }
        } // if either are not null

        return $this;
    }

    /**
     * Set the value of [website] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setWebsite($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->website !== $v) {
            $this->website = $v;
            $this->modifiedColumns[BookstoreTableMap::COL_WEBSITE] = true;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : BookstoreTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : BookstoreTableMap::translateFieldName('StoreName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->store_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : BookstoreTableMap::translateFieldName('Location', TableMap::TYPE_PHPNAME, $indexType)];
            $this->location = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : BookstoreTableMap::translateFieldName('PopulationServed', TableMap::TYPE_PHPNAME, $indexType)];
            $this->population_served = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : BookstoreTableMap::translateFieldName('TotalBooks', TableMap::TYPE_PHPNAME, $indexType)];
            $this->total_books = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : BookstoreTableMap::translateFieldName('StoreOpenTime', TableMap::TYPE_PHPNAME, $indexType)];
            $this->store_open_time = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : BookstoreTableMap::translateFieldName('Website', TableMap::TYPE_PHPNAME, $indexType)];
            $this->website = (null !== $col) ? (string) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 7; // 7 = BookstoreTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Bookstore'), 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(BookstoreTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildBookstoreQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collBookstoreSales = null;

            $this->collBookstoreContests = null;

            $this->collBookstoreContestEntries = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see Bookstore::setDeleted()
     * @see Bookstore::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildBookstoreQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreTableMap::DATABASE_NAME);
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
                BookstoreTableMap::addInstanceToPool($this);
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

            if ($this->bookstoreSalesScheduledForDeletion !== null) {
                if (!$this->bookstoreSalesScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\BookstoreSaleQuery::create()
                        ->filterByPrimaryKeys($this->bookstoreSalesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookstoreSalesScheduledForDeletion = null;
                }
            }

            if ($this->collBookstoreSales !== null) {
                foreach ($this->collBookstoreSales as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->bookstoreContestsScheduledForDeletion !== null) {
                if (!$this->bookstoreContestsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\BookstoreContestQuery::create()
                        ->filterByPrimaryKeys($this->bookstoreContestsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookstoreContestsScheduledForDeletion = null;
                }
            }

            if ($this->collBookstoreContests !== null) {
                foreach ($this->collBookstoreContests as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->bookstoreContestEntriesScheduledForDeletion !== null) {
                if (!$this->bookstoreContestEntriesScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\BookstoreContestEntryQuery::create()
                        ->filterByPrimaryKeys($this->bookstoreContestEntriesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookstoreContestEntriesScheduledForDeletion = null;
                }
            }

            if ($this->collBookstoreContestEntries !== null) {
                foreach ($this->collBookstoreContestEntries as $referrerFK) {
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

        $this->modifiedColumns[BookstoreTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . BookstoreTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(BookstoreTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_STORE_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'store_name';
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_LOCATION)) {
            $modifiedColumns[':p' . $index++]  = 'location';
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_POPULATION_SERVED)) {
            $modifiedColumns[':p' . $index++]  = 'population_served';
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_TOTAL_BOOKS)) {
            $modifiedColumns[':p' . $index++]  = 'total_books';
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_STORE_OPEN_TIME)) {
            $modifiedColumns[':p' . $index++]  = 'store_open_time';
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_WEBSITE)) {
            $modifiedColumns[':p' . $index++]  = 'website';
        }

        $sql = sprintf(
            'INSERT INTO bookstore (%s) VALUES (%s)',
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
                    case 'store_name':
                        $stmt->bindValue($identifier, $this->store_name, PDO::PARAM_STR);

                        break;
                    case 'location':
                        $stmt->bindValue($identifier, $this->location, PDO::PARAM_STR);

                        break;
                    case 'population_served':
                        $stmt->bindValue($identifier, $this->population_served, PDO::PARAM_INT);

                        break;
                    case 'total_books':
                        $stmt->bindValue($identifier, $this->total_books, PDO::PARAM_INT);

                        break;
                    case 'store_open_time':
                        $stmt->bindValue($identifier, $this->store_open_time ? $this->store_open_time->format("H:i:s.u") : null, PDO::PARAM_STR);

                        break;
                    case 'website':
                        $stmt->bindValue($identifier, $this->website, PDO::PARAM_STR);

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
        $pos = (int)BookstoreTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getStoreName();

            case 2:
                return $this->getLocation();

            case 3:
                return $this->getPopulationServed();

            case 4:
                return $this->getTotalBooks();

            case 5:
                return $this->getStoreOpenTime();

            case 6:
                return $this->getWebsite();

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
        if (isset($alreadyDumpedObjects['Bookstore'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['Bookstore'][$this->hashCode()] = true;
        $keys = BookstoreTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getStoreName(),
            $keys[2] => $this->getLocation(),
            $keys[3] => $this->getPopulationServed(),
            $keys[4] => $this->getTotalBooks(),
            $keys[5] => $this->getStoreOpenTime(),
            $keys[6] => $this->getWebsite(),
        ];
        if ($result[$keys[5]] instanceof \DateTimeInterface) {
            $result[$keys[5]] = $result[$keys[5]]->format('H:i:s.u');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collBookstoreSales) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookstoreSales';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'bookstore_sales';
                        break;
                    default:
                        $key = 'BookstoreSales';
                }

                $result[$key] = $this->collBookstoreSales->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBookstoreContests) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookstoreContests';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'bookstore_contests';
                        break;
                    default:
                        $key = 'BookstoreContests';
                }

                $result[$key] = $this->collBookstoreContests->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBookstoreContestEntries) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookstoreContestEntries';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'bookstore_contest_entries';
                        break;
                    default:
                        $key = 'BookstoreContestEntries';
                }

                $result[$key] = $this->collBookstoreContestEntries->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = (int)BookstoreTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setStoreName($value);
                break;
            case 2:
                $this->setLocation($value);
                break;
            case 3:
                $this->setPopulationServed($value);
                break;
            case 4:
                $this->setTotalBooks($value);
                break;
            case 5:
                $this->setStoreOpenTime($value);
                break;
            case 6:
                $this->setWebsite($value);
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
        $keys = BookstoreTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setStoreName($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setLocation($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setPopulationServed($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setTotalBooks($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setStoreOpenTime($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setWebsite($arr[$keys[6]]);
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
        $criteria = new Criteria(BookstoreTableMap::DATABASE_NAME);

        if ($this->isColumnModified(BookstoreTableMap::COL_ID)) {
            $criteria->add(BookstoreTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_STORE_NAME)) {
            $criteria->add(BookstoreTableMap::COL_STORE_NAME, $this->store_name);
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_LOCATION)) {
            $criteria->add(BookstoreTableMap::COL_LOCATION, $this->location);
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_POPULATION_SERVED)) {
            $criteria->add(BookstoreTableMap::COL_POPULATION_SERVED, $this->population_served);
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_TOTAL_BOOKS)) {
            $criteria->add(BookstoreTableMap::COL_TOTAL_BOOKS, $this->total_books);
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_STORE_OPEN_TIME)) {
            $criteria->add(BookstoreTableMap::COL_STORE_OPEN_TIME, $this->store_open_time);
        }
        if ($this->isColumnModified(BookstoreTableMap::COL_WEBSITE)) {
            $criteria->add(BookstoreTableMap::COL_WEBSITE, $this->website);
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
        $criteria = ChildBookstoreQuery::create();
        $criteria->add(BookstoreTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Bookstore (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setStoreName($this->getStoreName());
        $copyObj->setLocation($this->getLocation());
        $copyObj->setPopulationServed($this->getPopulationServed());
        $copyObj->setTotalBooks($this->getTotalBooks());
        $copyObj->setStoreOpenTime($this->getStoreOpenTime());
        $copyObj->setWebsite($this->getWebsite());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getBookstoreSales() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookstoreSale($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBookstoreContests() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookstoreContest($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBookstoreContestEntries() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookstoreContestEntry($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\Bookstore Clone of current object.
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
        if ('BookstoreSale' === $relationName) {
            $this->initBookstoreSales();
            return;
        }
        if ('BookstoreContest' === $relationName) {
            $this->initBookstoreContests();
            return;
        }
        if ('BookstoreContestEntry' === $relationName) {
            $this->initBookstoreContestEntries();
            return;
        }
    }

    /**
     * Clears out the collBookstoreSales collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBookstoreSales()
     */
    public function clearBookstoreSales()
    {
        $this->collBookstoreSales = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBookstoreSales collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBookstoreSales($v = true): void
    {
        $this->collBookstoreSalesPartial = $v;
    }

    /**
     * Initializes the collBookstoreSales collection.
     *
     * By default this just sets the collBookstoreSales collection to an empty array (like clearcollBookstoreSales());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookstoreSales(bool $overrideExisting = true): void
    {
        if (null !== $this->collBookstoreSales && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookstoreSaleTableMap::getTableMap()->getCollectionClassName();

        $this->collBookstoreSales = new $collectionClassName;
        $this->collBookstoreSales->setModel('\Propel\Tests\Bookstore\BookstoreSale');
    }

    /**
     * Gets an array of ChildBookstoreSale objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBookstore is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBookstoreSale[] List of ChildBookstoreSale objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreSale> List of ChildBookstoreSale objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookstoreSales(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookstoreSalesPartial && !$this->isNew();
        if (null === $this->collBookstoreSales || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookstoreSales) {
                    $this->initBookstoreSales();
                } else {
                    $collectionClassName = BookstoreSaleTableMap::getTableMap()->getCollectionClassName();

                    $collBookstoreSales = new $collectionClassName;
                    $collBookstoreSales->setModel('\Propel\Tests\Bookstore\BookstoreSale');

                    return $collBookstoreSales;
                }
            } else {
                $collBookstoreSales = ChildBookstoreSaleQuery::create(null, $criteria)
                    ->filterByBookstore($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBookstoreSalesPartial && count($collBookstoreSales)) {
                        $this->initBookstoreSales(false);

                        foreach ($collBookstoreSales as $obj) {
                            if (false === $this->collBookstoreSales->contains($obj)) {
                                $this->collBookstoreSales->append($obj);
                            }
                        }

                        $this->collBookstoreSalesPartial = true;
                    }

                    return $collBookstoreSales;
                }

                if ($partial && $this->collBookstoreSales) {
                    foreach ($this->collBookstoreSales as $obj) {
                        if ($obj->isNew()) {
                            $collBookstoreSales[] = $obj;
                        }
                    }
                }

                $this->collBookstoreSales = $collBookstoreSales;
                $this->collBookstoreSalesPartial = false;
            }
        }

        return $this->collBookstoreSales;
    }

    /**
     * Sets a collection of ChildBookstoreSale objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookstoreSales A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookstoreSales(Collection $bookstoreSales, ?ConnectionInterface $con = null)
    {
        /** @var ChildBookstoreSale[] $bookstoreSalesToDelete */
        $bookstoreSalesToDelete = $this->getBookstoreSales(new Criteria(), $con)->diff($bookstoreSales);


        $this->bookstoreSalesScheduledForDeletion = $bookstoreSalesToDelete;

        foreach ($bookstoreSalesToDelete as $bookstoreSaleRemoved) {
            $bookstoreSaleRemoved->setBookstore(null);
        }

        $this->collBookstoreSales = null;
        foreach ($bookstoreSales as $bookstoreSale) {
            $this->addBookstoreSale($bookstoreSale);
        }

        $this->collBookstoreSales = $bookstoreSales;
        $this->collBookstoreSalesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookstoreSale objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related BookstoreSale objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBookstoreSales(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookstoreSalesPartial && !$this->isNew();
        if (null === $this->collBookstoreSales || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookstoreSales) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBookstoreSales());
            }

            $query = ChildBookstoreSaleQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBookstore($this)
                ->count($con);
        }

        return count($this->collBookstoreSales);
    }

    /**
     * Method called to associate a ChildBookstoreSale object to this object
     * through the ChildBookstoreSale foreign key attribute.
     *
     * @param ChildBookstoreSale $l ChildBookstoreSale
     * @return $this The current object (for fluent API support)
     */
    public function addBookstoreSale(ChildBookstoreSale $l)
    {
        if ($this->collBookstoreSales === null) {
            $this->initBookstoreSales();
            $this->collBookstoreSalesPartial = true;
        }

        if (!$this->collBookstoreSales->contains($l)) {
            $this->doAddBookstoreSale($l);

            if ($this->bookstoreSalesScheduledForDeletion and $this->bookstoreSalesScheduledForDeletion->contains($l)) {
                $this->bookstoreSalesScheduledForDeletion->remove($this->bookstoreSalesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBookstoreSale $bookstoreSale The ChildBookstoreSale object to add.
     */
    protected function doAddBookstoreSale(ChildBookstoreSale $bookstoreSale): void
    {
        $this->collBookstoreSales[]= $bookstoreSale;
        $bookstoreSale->setBookstore($this);
    }

    /**
     * @param ChildBookstoreSale $bookstoreSale The ChildBookstoreSale object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBookstoreSale(ChildBookstoreSale $bookstoreSale)
    {
        if ($this->getBookstoreSales()->contains($bookstoreSale)) {
            $pos = $this->collBookstoreSales->search($bookstoreSale);
            $this->collBookstoreSales->remove($pos);
            if (null === $this->bookstoreSalesScheduledForDeletion) {
                $this->bookstoreSalesScheduledForDeletion = clone $this->collBookstoreSales;
                $this->bookstoreSalesScheduledForDeletion->clear();
            }
            $this->bookstoreSalesScheduledForDeletion[]= $bookstoreSale;
            $bookstoreSale->setBookstore(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Bookstore is new, it will return
     * an empty collection; or if this Bookstore has previously
     * been saved, it will retrieve related BookstoreSales from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Bookstore.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookstoreSale[] List of ChildBookstoreSale objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreSale> List of ChildBookstoreSale objects
     */
    public function getBookstoreSalesJoinPublisher(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookstoreSaleQuery::create(null, $criteria);
        $query->joinWith('Publisher', $joinBehavior);

        return $this->getBookstoreSales($query, $con);
    }

    /**
     * Clears out the collBookstoreContests collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBookstoreContests()
     */
    public function clearBookstoreContests()
    {
        $this->collBookstoreContests = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBookstoreContests collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBookstoreContests($v = true): void
    {
        $this->collBookstoreContestsPartial = $v;
    }

    /**
     * Initializes the collBookstoreContests collection.
     *
     * By default this just sets the collBookstoreContests collection to an empty array (like clearcollBookstoreContests());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookstoreContests(bool $overrideExisting = true): void
    {
        if (null !== $this->collBookstoreContests && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookstoreContestTableMap::getTableMap()->getCollectionClassName();

        $this->collBookstoreContests = new $collectionClassName;
        $this->collBookstoreContests->setModel('\Propel\Tests\Bookstore\BookstoreContest');
    }

    /**
     * Gets an array of ChildBookstoreContest objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBookstore is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBookstoreContest[] List of ChildBookstoreContest objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreContest> List of ChildBookstoreContest objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookstoreContests(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookstoreContestsPartial && !$this->isNew();
        if (null === $this->collBookstoreContests || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookstoreContests) {
                    $this->initBookstoreContests();
                } else {
                    $collectionClassName = BookstoreContestTableMap::getTableMap()->getCollectionClassName();

                    $collBookstoreContests = new $collectionClassName;
                    $collBookstoreContests->setModel('\Propel\Tests\Bookstore\BookstoreContest');

                    return $collBookstoreContests;
                }
            } else {
                $collBookstoreContests = ChildBookstoreContestQuery::create(null, $criteria)
                    ->filterByBookstore($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBookstoreContestsPartial && count($collBookstoreContests)) {
                        $this->initBookstoreContests(false);

                        foreach ($collBookstoreContests as $obj) {
                            if (false === $this->collBookstoreContests->contains($obj)) {
                                $this->collBookstoreContests->append($obj);
                            }
                        }

                        $this->collBookstoreContestsPartial = true;
                    }

                    return $collBookstoreContests;
                }

                if ($partial && $this->collBookstoreContests) {
                    foreach ($this->collBookstoreContests as $obj) {
                        if ($obj->isNew()) {
                            $collBookstoreContests[] = $obj;
                        }
                    }
                }

                $this->collBookstoreContests = $collBookstoreContests;
                $this->collBookstoreContestsPartial = false;
            }
        }

        return $this->collBookstoreContests;
    }

    /**
     * Sets a collection of ChildBookstoreContest objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookstoreContests A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookstoreContests(Collection $bookstoreContests, ?ConnectionInterface $con = null)
    {
        /** @var ChildBookstoreContest[] $bookstoreContestsToDelete */
        $bookstoreContestsToDelete = $this->getBookstoreContests(new Criteria(), $con)->diff($bookstoreContests);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->bookstoreContestsScheduledForDeletion = clone $bookstoreContestsToDelete;

        foreach ($bookstoreContestsToDelete as $bookstoreContestRemoved) {
            $bookstoreContestRemoved->setBookstore(null);
        }

        $this->collBookstoreContests = null;
        foreach ($bookstoreContests as $bookstoreContest) {
            $this->addBookstoreContest($bookstoreContest);
        }

        $this->collBookstoreContests = $bookstoreContests;
        $this->collBookstoreContestsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookstoreContest objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related BookstoreContest objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBookstoreContests(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookstoreContestsPartial && !$this->isNew();
        if (null === $this->collBookstoreContests || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookstoreContests) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBookstoreContests());
            }

            $query = ChildBookstoreContestQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBookstore($this)
                ->count($con);
        }

        return count($this->collBookstoreContests);
    }

    /**
     * Method called to associate a ChildBookstoreContest object to this object
     * through the ChildBookstoreContest foreign key attribute.
     *
     * @param ChildBookstoreContest $l ChildBookstoreContest
     * @return $this The current object (for fluent API support)
     */
    public function addBookstoreContest(ChildBookstoreContest $l)
    {
        if ($this->collBookstoreContests === null) {
            $this->initBookstoreContests();
            $this->collBookstoreContestsPartial = true;
        }

        if (!$this->collBookstoreContests->contains($l)) {
            $this->doAddBookstoreContest($l);

            if ($this->bookstoreContestsScheduledForDeletion and $this->bookstoreContestsScheduledForDeletion->contains($l)) {
                $this->bookstoreContestsScheduledForDeletion->remove($this->bookstoreContestsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBookstoreContest $bookstoreContest The ChildBookstoreContest object to add.
     */
    protected function doAddBookstoreContest(ChildBookstoreContest $bookstoreContest): void
    {
        $this->collBookstoreContests[]= $bookstoreContest;
        $bookstoreContest->setBookstore($this);
    }

    /**
     * @param ChildBookstoreContest $bookstoreContest The ChildBookstoreContest object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBookstoreContest(ChildBookstoreContest $bookstoreContest)
    {
        if ($this->getBookstoreContests()->contains($bookstoreContest)) {
            $pos = $this->collBookstoreContests->search($bookstoreContest);
            $this->collBookstoreContests->remove($pos);
            if (null === $this->bookstoreContestsScheduledForDeletion) {
                $this->bookstoreContestsScheduledForDeletion = clone $this->collBookstoreContests;
                $this->bookstoreContestsScheduledForDeletion->clear();
            }
            $this->bookstoreContestsScheduledForDeletion[]= clone $bookstoreContest;
            $bookstoreContest->setBookstore(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Bookstore is new, it will return
     * an empty collection; or if this Bookstore has previously
     * been saved, it will retrieve related BookstoreContests from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Bookstore.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookstoreContest[] List of ChildBookstoreContest objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreContest> List of ChildBookstoreContest objects
     */
    public function getBookstoreContestsJoinContest(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookstoreContestQuery::create(null, $criteria);
        $query->joinWith('Contest', $joinBehavior);

        return $this->getBookstoreContests($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Bookstore is new, it will return
     * an empty collection; or if this Bookstore has previously
     * been saved, it will retrieve related BookstoreContests from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Bookstore.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookstoreContest[] List of ChildBookstoreContest objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreContest> List of ChildBookstoreContest objects
     */
    public function getBookstoreContestsJoinWork(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookstoreContestQuery::create(null, $criteria);
        $query->joinWith('Work', $joinBehavior);

        return $this->getBookstoreContests($query, $con);
    }

    /**
     * Clears out the collBookstoreContestEntries collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBookstoreContestEntries()
     */
    public function clearBookstoreContestEntries()
    {
        $this->collBookstoreContestEntries = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBookstoreContestEntries collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBookstoreContestEntries($v = true): void
    {
        $this->collBookstoreContestEntriesPartial = $v;
    }

    /**
     * Initializes the collBookstoreContestEntries collection.
     *
     * By default this just sets the collBookstoreContestEntries collection to an empty array (like clearcollBookstoreContestEntries());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookstoreContestEntries(bool $overrideExisting = true): void
    {
        if (null !== $this->collBookstoreContestEntries && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookstoreContestEntryTableMap::getTableMap()->getCollectionClassName();

        $this->collBookstoreContestEntries = new $collectionClassName;
        $this->collBookstoreContestEntries->setModel('\Propel\Tests\Bookstore\BookstoreContestEntry');
    }

    /**
     * Gets an array of ChildBookstoreContestEntry objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBookstore is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBookstoreContestEntry[] List of ChildBookstoreContestEntry objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreContestEntry> List of ChildBookstoreContestEntry objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookstoreContestEntries(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookstoreContestEntriesPartial && !$this->isNew();
        if (null === $this->collBookstoreContestEntries || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookstoreContestEntries) {
                    $this->initBookstoreContestEntries();
                } else {
                    $collectionClassName = BookstoreContestEntryTableMap::getTableMap()->getCollectionClassName();

                    $collBookstoreContestEntries = new $collectionClassName;
                    $collBookstoreContestEntries->setModel('\Propel\Tests\Bookstore\BookstoreContestEntry');

                    return $collBookstoreContestEntries;
                }
            } else {
                $collBookstoreContestEntries = ChildBookstoreContestEntryQuery::create(null, $criteria)
                    ->filterByBookstore($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBookstoreContestEntriesPartial && count($collBookstoreContestEntries)) {
                        $this->initBookstoreContestEntries(false);

                        foreach ($collBookstoreContestEntries as $obj) {
                            if (false === $this->collBookstoreContestEntries->contains($obj)) {
                                $this->collBookstoreContestEntries->append($obj);
                            }
                        }

                        $this->collBookstoreContestEntriesPartial = true;
                    }

                    return $collBookstoreContestEntries;
                }

                if ($partial && $this->collBookstoreContestEntries) {
                    foreach ($this->collBookstoreContestEntries as $obj) {
                        if ($obj->isNew()) {
                            $collBookstoreContestEntries[] = $obj;
                        }
                    }
                }

                $this->collBookstoreContestEntries = $collBookstoreContestEntries;
                $this->collBookstoreContestEntriesPartial = false;
            }
        }

        return $this->collBookstoreContestEntries;
    }

    /**
     * Sets a collection of ChildBookstoreContestEntry objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookstoreContestEntries A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookstoreContestEntries(Collection $bookstoreContestEntries, ?ConnectionInterface $con = null)
    {
        /** @var ChildBookstoreContestEntry[] $bookstoreContestEntriesToDelete */
        $bookstoreContestEntriesToDelete = $this->getBookstoreContestEntries(new Criteria(), $con)->diff($bookstoreContestEntries);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->bookstoreContestEntriesScheduledForDeletion = clone $bookstoreContestEntriesToDelete;

        foreach ($bookstoreContestEntriesToDelete as $bookstoreContestEntryRemoved) {
            $bookstoreContestEntryRemoved->setBookstore(null);
        }

        $this->collBookstoreContestEntries = null;
        foreach ($bookstoreContestEntries as $bookstoreContestEntry) {
            $this->addBookstoreContestEntry($bookstoreContestEntry);
        }

        $this->collBookstoreContestEntries = $bookstoreContestEntries;
        $this->collBookstoreContestEntriesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookstoreContestEntry objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related BookstoreContestEntry objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBookstoreContestEntries(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookstoreContestEntriesPartial && !$this->isNew();
        if (null === $this->collBookstoreContestEntries || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookstoreContestEntries) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBookstoreContestEntries());
            }

            $query = ChildBookstoreContestEntryQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBookstore($this)
                ->count($con);
        }

        return count($this->collBookstoreContestEntries);
    }

    /**
     * Method called to associate a ChildBookstoreContestEntry object to this object
     * through the ChildBookstoreContestEntry foreign key attribute.
     *
     * @param ChildBookstoreContestEntry $l ChildBookstoreContestEntry
     * @return $this The current object (for fluent API support)
     */
    public function addBookstoreContestEntry(ChildBookstoreContestEntry $l)
    {
        if ($this->collBookstoreContestEntries === null) {
            $this->initBookstoreContestEntries();
            $this->collBookstoreContestEntriesPartial = true;
        }

        if (!$this->collBookstoreContestEntries->contains($l)) {
            $this->doAddBookstoreContestEntry($l);

            if ($this->bookstoreContestEntriesScheduledForDeletion and $this->bookstoreContestEntriesScheduledForDeletion->contains($l)) {
                $this->bookstoreContestEntriesScheduledForDeletion->remove($this->bookstoreContestEntriesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBookstoreContestEntry $bookstoreContestEntry The ChildBookstoreContestEntry object to add.
     */
    protected function doAddBookstoreContestEntry(ChildBookstoreContestEntry $bookstoreContestEntry): void
    {
        $this->collBookstoreContestEntries[]= $bookstoreContestEntry;
        $bookstoreContestEntry->setBookstore($this);
    }

    /**
     * @param ChildBookstoreContestEntry $bookstoreContestEntry The ChildBookstoreContestEntry object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBookstoreContestEntry(ChildBookstoreContestEntry $bookstoreContestEntry)
    {
        if ($this->getBookstoreContestEntries()->contains($bookstoreContestEntry)) {
            $pos = $this->collBookstoreContestEntries->search($bookstoreContestEntry);
            $this->collBookstoreContestEntries->remove($pos);
            if (null === $this->bookstoreContestEntriesScheduledForDeletion) {
                $this->bookstoreContestEntriesScheduledForDeletion = clone $this->collBookstoreContestEntries;
                $this->bookstoreContestEntriesScheduledForDeletion->clear();
            }
            $this->bookstoreContestEntriesScheduledForDeletion[]= clone $bookstoreContestEntry;
            $bookstoreContestEntry->setBookstore(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Bookstore is new, it will return
     * an empty collection; or if this Bookstore has previously
     * been saved, it will retrieve related BookstoreContestEntries from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Bookstore.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookstoreContestEntry[] List of ChildBookstoreContestEntry objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreContestEntry> List of ChildBookstoreContestEntry objects
     */
    public function getBookstoreContestEntriesJoinCustomer(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookstoreContestEntryQuery::create(null, $criteria);
        $query->joinWith('Customer', $joinBehavior);

        return $this->getBookstoreContestEntries($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Bookstore is new, it will return
     * an empty collection; or if this Bookstore has previously
     * been saved, it will retrieve related BookstoreContestEntries from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Bookstore.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookstoreContestEntry[] List of ChildBookstoreContestEntry objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreContestEntry> List of ChildBookstoreContestEntry objects
     */
    public function getBookstoreContestEntriesJoinBookstoreContest(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookstoreContestEntryQuery::create(null, $criteria);
        $query->joinWith('BookstoreContest', $joinBehavior);

        return $this->getBookstoreContestEntries($query, $con);
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
        $this->store_name = null;
        $this->location = null;
        $this->population_served = null;
        $this->total_books = null;
        $this->store_open_time = null;
        $this->website = null;
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
            if ($this->collBookstoreSales) {
                foreach ($this->collBookstoreSales as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBookstoreContests) {
                foreach ($this->collBookstoreContests as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBookstoreContestEntries) {
                foreach ($this->collBookstoreContestEntries as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collBookstoreSales = null;
        $this->collBookstoreContests = null;
        $this->collBookstoreContestEntries = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(BookstoreTableMap::DEFAULT_STRING_FORMAT);
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
