<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Base;

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
use Propel\Tests\Bookstore\TypeObject as ChildTypeObject;
use Propel\Tests\Bookstore\TypeObjectQuery as ChildTypeObjectQuery;
use Propel\Tests\Bookstore\Map\TypeObjectTableMap;
use Propel\Tests\Runtime\TypeTests\DummyObjectClass;
use Propel\Tests\Runtime\TypeTests\TypeObjectInterface;

/**
 * Base class that represents a row from the 'type_object' table.
 *
 *
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Base
 */
abstract class TypeObject implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Map\\TypeObjectTableMap';


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
     * The value for the details field.
     *
     * @var        |null
     */
    protected $details;

    /**
     * The unserialized $details value - i.e. the persisted object.
     * This is necessary to avoid repeated calls to unserialize() at runtime.
     * @var object
     */
    protected $details_unserialized;

    /**
     * The value for the dummy_object field.
     *
     * @var        |null
     */
    protected $dummy_object;

    /**
     * The unserialized $dummy_object value - i.e. the persisted object.
     * This is necessary to avoid repeated calls to unserialize() at runtime.
     * @var object
     */
    protected $dummy_object_unserialized;

    /**
     * The value for the self_ref field.
     *
     * @var        int|null
     */
    protected ?int $self_ref = null;

    /**
     * The value for the some_array field.
     *
     * @var        array|null
     */
    protected $some_array;

    /**
     * The unserialized $some_array value - i.e. the persisted object.
     * This is necessary to avoid repeated calls to unserialize() at runtime.
     * @var object
     */
    protected $some_array_unserialized;

    /**
     * @var        ChildTypeObject
     */
    protected $aTypeObject;

    /**
     * @var        ObjectCollection|ChildTypeObject[] Collection to store aggregation of ChildTypeObject objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildTypeObject> Collection to store aggregation of ChildTypeObject objects.
     */
    protected $collTypeObjectsRelatedById;
    protected $collTypeObjectsRelatedByIdPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildTypeObject[]
     * @phpstan-var ObjectCollection&\Traversable<ChildTypeObject>
     */
    protected $typeObjectsRelatedByIdScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Base\TypeObject object.
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
     * Compares this with another <code>TypeObject</code> instance.  If
     * <code>obj</code> is an instance of <code>TypeObject</code>, delegates to
     * <code>equals(TypeObject)</code>.  Otherwise, returns <code>false</code>.
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
     * Get the [details] column value.
     *
     * @return mixed|null
     */
    public function getDetails()
    {
        if (null === $this->details_unserialized && is_resource($this->details)) {
            rewind($this->details);
            if ($serialisedString = stream_get_contents($this->details)) {
                $this->details_unserialized = unserialize($serialisedString, ['allowed_classes' => true]);
            }
        }

        return $this->details_unserialized;
    }

    /**
     * Get the [dummy_object] column value.
     *
     * @return Propel\Tests\Runtime\TypeTests\DummyObjectClass|null
     */
    public function getDummyObject()
    {
        if (null === $this->dummy_object_unserialized && is_resource($this->dummy_object)) {
            rewind($this->dummy_object);
            if ($serialisedString = stream_get_contents($this->dummy_object)) {
                $this->dummy_object_unserialized = unserialize($serialisedString, ['allowed_classes' => true]);
            }
        }

        return $this->dummy_object_unserialized;
    }

    /**
     * Get the [self_ref] column value.
     *
     * @return int|null
     */
    public function getSelfRef()
    {
        return $this->self_ref;
    }

    /**
     * Get the [some_array] column value.
     *
     * @return array|null
     */
    public function getSomeArray()
    {
        if (null === $this->some_array_unserialized) {
            $this->some_array_unserialized = [];
        }
        if (!$this->some_array_unserialized && null !== $this->some_array) {
            $some_array_unserialized = substr($this->some_array, 2, -2);
            $this->some_array_unserialized = '' !== $some_array_unserialized ? explode(' | ', $some_array_unserialized) : [];
        }

        return $this->some_array_unserialized;
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
            $this->modifiedColumns[TypeObjectTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [details] column.
     *
     * @param  $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setDetails($v)
    {
        if (null === $this->details || (rewind($this->details) !== false && stream_get_contents($this->details) !== serialize($v))) {
            $this->details_unserialized = $v;
            $this->details = fopen('php://memory', 'r+');
            fwrite($this->details, serialize($v));
            $this->modifiedColumns[TypeObjectTableMap::COL_DETAILS] = true;
        }
        rewind($this->details);

        return $this;
    }

    /**
     * Set the value of [dummy_object] column.
     *
     * @param  $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setDummyObject(?DummyObjectClass $v = null)
    {
        if (null === $this->dummy_object || (rewind($this->dummy_object) !== false && stream_get_contents($this->dummy_object) !== serialize($v))) {
            $this->dummy_object_unserialized = $v;
            $this->dummy_object = fopen('php://memory', 'r+');
            fwrite($this->dummy_object, serialize($v));
            $this->modifiedColumns[TypeObjectTableMap::COL_DUMMY_OBJECT] = true;
        }
        rewind($this->dummy_object);

        return $this;
    }

    /**
     * Set the value of [self_ref] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setSelfRef($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->self_ref !== $v) {
            $this->self_ref = $v;
            $this->modifiedColumns[TypeObjectTableMap::COL_SELF_REF] = true;
        }

        if ($this->aTypeObject !== null && $this->aTypeObject->getId() !== $v) {
            $this->aTypeObject = null;
        }

        return $this;
    }

    /**
     * Set the value of [some_array] column.
     *
     * @param array|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setSomeArray(?array $v = null)
    {
        if ($this->some_array_unserialized !== $v) {
            $this->some_array_unserialized = $v;
            $this->some_array = '| ' . implode(' | ', $v) . ' |';
            $this->modifiedColumns[TypeObjectTableMap::COL_SOME_ARRAY] = true;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : TypeObjectTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : TypeObjectTableMap::translateFieldName('Details', TableMap::TYPE_PHPNAME, $indexType)];
            if (null !== $col) {
                $this->details = fopen('php://memory', 'r+');
                fwrite($this->details, $col);
                rewind($this->details);
            } else {
                $this->details = null;
            }

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : TypeObjectTableMap::translateFieldName('DummyObject', TableMap::TYPE_PHPNAME, $indexType)];
            if (null !== $col) {
                $this->dummy_object = fopen('php://memory', 'r+');
                fwrite($this->dummy_object, $col);
                rewind($this->dummy_object);
            } else {
                $this->dummy_object = null;
            }

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : TypeObjectTableMap::translateFieldName('SelfRef', TableMap::TYPE_PHPNAME, $indexType)];
            $this->self_ref = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : TypeObjectTableMap::translateFieldName('SomeArray', TableMap::TYPE_PHPNAME, $indexType)];
            $this->some_array = $col;
            $this->some_array_unserialized = null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 5; // 5 = TypeObjectTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\TypeObject'), 0, $e);
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
        if ($this->aTypeObject !== null && $this->self_ref !== $this->aTypeObject->getId()) {
            $this->aTypeObject = null;
        }
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
            $con = Propel::getServiceContainer()->getReadConnection(TypeObjectTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildTypeObjectQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aTypeObject = null;
            $this->collTypeObjectsRelatedById = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see TypeObject::setDeleted()
     * @see TypeObject::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(TypeObjectTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildTypeObjectQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(TypeObjectTableMap::DATABASE_NAME);
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
                TypeObjectTableMap::addInstanceToPool($this);
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

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aTypeObject !== null) {
                if ($this->aTypeObject->isModified() || $this->aTypeObject->isNew()) {
                    $affectedRows += $this->aTypeObject->save($con);
                }
                $this->setTypeObject($this->aTypeObject);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                // Rewind the details LOB column, since PDO does not rewind after inserting value.
                if ($this->details !== null && is_resource($this->details)) {
                    rewind($this->details);
                }

                // Rewind the dummy_object LOB column, since PDO does not rewind after inserting value.
                if ($this->dummy_object !== null && is_resource($this->dummy_object)) {
                    rewind($this->dummy_object);
                }

                $this->resetModified();
            }

            if ($this->typeObjectsRelatedByIdScheduledForDeletion !== null) {
                if (!$this->typeObjectsRelatedByIdScheduledForDeletion->isEmpty()) {
                    foreach ($this->typeObjectsRelatedByIdScheduledForDeletion as $typeObjectRelatedById) {
                        // need to save related object because we set the relation to null
                        $typeObjectRelatedById->save($con);
                    }
                    $this->typeObjectsRelatedByIdScheduledForDeletion = null;
                }
            }

            if ($this->collTypeObjectsRelatedById !== null) {
                foreach ($this->collTypeObjectsRelatedById as $referrerFK) {
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

        $this->modifiedColumns[TypeObjectTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . TypeObjectTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(TypeObjectTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_DETAILS)) {
            $modifiedColumns[':p' . $index++]  = 'details';
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_DUMMY_OBJECT)) {
            $modifiedColumns[':p' . $index++]  = 'dummy_object';
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_SELF_REF)) {
            $modifiedColumns[':p' . $index++]  = 'self_ref';
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_SOME_ARRAY)) {
            $modifiedColumns[':p' . $index++]  = 'some_array';
        }

        $sql = sprintf(
            'INSERT INTO type_object (%s) VALUES (%s)',
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
                    case 'details':
                        if (is_resource($this->details)) {
                            rewind($this->details);
                        }
                        $stmt->bindValue($identifier, $this->details, PDO::PARAM_LOB);

                        break;
                    case 'dummy_object':
                        if (is_resource($this->dummy_object)) {
                            rewind($this->dummy_object);
                        }
                        $stmt->bindValue($identifier, $this->dummy_object, PDO::PARAM_LOB);

                        break;
                    case 'self_ref':
                        $stmt->bindValue($identifier, $this->self_ref, PDO::PARAM_INT);

                        break;
                    case 'some_array':
                        $stmt->bindValue($identifier, $this->some_array, PDO::PARAM_STR);

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
        $pos = (int)TypeObjectTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getDetails();

            case 2:
                return $this->getDummyObject();

            case 3:
                return $this->getSelfRef();

            case 4:
                return $this->getSomeArray();

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
        if (isset($alreadyDumpedObjects['TypeObject'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['TypeObject'][$this->hashCode()] = true;
        $keys = TypeObjectTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getDetails(),
            $keys[2] => $this->getDummyObject(),
            $keys[3] => $this->getSelfRef(),
            $keys[4] => $this->getSomeArray(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aTypeObject) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'typeObject';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'type_object';
                        break;
                    default:
                        $key = 'TypeObject';
                }

                $result[$key] = $this->aTypeObject->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collTypeObjectsRelatedById) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'typeObjects';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'type_objects';
                        break;
                    default:
                        $key = 'TypeObjects';
                }

                $result[$key] = $this->collTypeObjectsRelatedById->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = (int)TypeObjectTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setDetails($value);
                break;
            case 2:
                $this->setDummyObject($value);
                break;
            case 3:
                $this->setSelfRef($value);
                break;
            case 4:
                if (!is_array($value)) {
                    $v = trim(substr($value, 2, -2));
                    $value = $v ? explode(' | ', $v) : array();
                }
                $this->setSomeArray($value);
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
        $keys = TypeObjectTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setDetails($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setDummyObject($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setSelfRef($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setSomeArray($arr[$keys[4]]);
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
        $criteria = new Criteria(TypeObjectTableMap::DATABASE_NAME);

        if ($this->isColumnModified(TypeObjectTableMap::COL_ID)) {
            $criteria->add(TypeObjectTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_DETAILS)) {
            $criteria->add(TypeObjectTableMap::COL_DETAILS, $this->details);
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_DUMMY_OBJECT)) {
            $criteria->add(TypeObjectTableMap::COL_DUMMY_OBJECT, $this->dummy_object);
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_SELF_REF)) {
            $criteria->add(TypeObjectTableMap::COL_SELF_REF, $this->self_ref);
        }
        if ($this->isColumnModified(TypeObjectTableMap::COL_SOME_ARRAY)) {
            $criteria->add(TypeObjectTableMap::COL_SOME_ARRAY, $this->some_array);
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
        $criteria = ChildTypeObjectQuery::create();
        $criteria->add(TypeObjectTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\TypeObject (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setDetails($this->getDetails());
        $copyObj->setDummyObject($this->getDummyObject());
        $copyObj->setSelfRef($this->getSelfRef());
        $copyObj->setSomeArray($this->getSomeArray());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getTypeObjectsRelatedById() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTypeObjectRelatedById($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\TypeObject Clone of current object.
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
     * Declares an association between this object and a TypeObjectInterface object.
     *
     * @param TypeObjectInterface|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setTypeObject(?TypeObjectInterface $v = null)
    {
        if ($v === null) {
            $this->setSelfRef(NULL);
        } else {
            $this->setSelfRef($v->getId());
        }

        $this->aTypeObject = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the TypeObjectInterface object, it will not be re-added.
        if ($v !== null) {
            $v->addTypeObjectRelatedById($this);
        }


        return $this;
    }


    /**
     * Get the associated TypeObjectInterface object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return TypeObjectInterface|null
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getTypeObject(?ConnectionInterface $con = null)
    {
        if ($this->aTypeObject === null && ($this->self_ref !== null)) {
            $this->aTypeObject = ChildTypeObjectQuery::create()->findPk($this->self_ref, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aTypeObject->addTypeObjectsRelatedById($this);
             */
        }

        return $this->aTypeObject;
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
        if ('TypeObjectRelatedById' === $relationName) {
            $this->initTypeObjectsRelatedById();
            return;
        }
    }

    /**
     * Clears out the collTypeObjectsRelatedById collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addTypeObjectsRelatedById()
     */
    public function clearTypeObjectsRelatedById()
    {
        $this->collTypeObjectsRelatedById = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collTypeObjectsRelatedById collection loaded partially.
     *
     * @return void
     */
    public function resetPartialTypeObjectsRelatedById($v = true): void
    {
        $this->collTypeObjectsRelatedByIdPartial = $v;
    }

    /**
     * Initializes the collTypeObjectsRelatedById collection.
     *
     * By default this just sets the collTypeObjectsRelatedById collection to an empty array (like clearcollTypeObjectsRelatedById());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTypeObjectsRelatedById(bool $overrideExisting = true): void
    {
        if (null !== $this->collTypeObjectsRelatedById && !$overrideExisting) {
            return;
        }

        $collectionClassName = TypeObjectTableMap::getTableMap()->getCollectionClassName();

        $this->collTypeObjectsRelatedById = new $collectionClassName;
        $this->collTypeObjectsRelatedById->setModel('\Propel\Tests\Bookstore\TypeObject');
    }

    /**
     * Gets an array of ChildTypeObject objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildTypeObject is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildTypeObject[] List of ChildTypeObject objects
     * @phpstan-return ObjectCollection&\Traversable<ChildTypeObject> List of ChildTypeObject objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getTypeObjectsRelatedById(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collTypeObjectsRelatedByIdPartial && !$this->isNew();
        if (null === $this->collTypeObjectsRelatedById || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collTypeObjectsRelatedById) {
                    $this->initTypeObjectsRelatedById();
                } else {
                    $collectionClassName = TypeObjectTableMap::getTableMap()->getCollectionClassName();

                    $collTypeObjectsRelatedById = new $collectionClassName;
                    $collTypeObjectsRelatedById->setModel('\Propel\Tests\Bookstore\TypeObject');

                    return $collTypeObjectsRelatedById;
                }
            } else {
                $collTypeObjectsRelatedById = ChildTypeObjectQuery::create(null, $criteria)
                    ->filterByTypeObject($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collTypeObjectsRelatedByIdPartial && count($collTypeObjectsRelatedById)) {
                        $this->initTypeObjectsRelatedById(false);

                        foreach ($collTypeObjectsRelatedById as $obj) {
                            if (false === $this->collTypeObjectsRelatedById->contains($obj)) {
                                $this->collTypeObjectsRelatedById->append($obj);
                            }
                        }

                        $this->collTypeObjectsRelatedByIdPartial = true;
                    }

                    return $collTypeObjectsRelatedById;
                }

                if ($partial && $this->collTypeObjectsRelatedById) {
                    foreach ($this->collTypeObjectsRelatedById as $obj) {
                        if ($obj->isNew()) {
                            $collTypeObjectsRelatedById[] = $obj;
                        }
                    }
                }

                $this->collTypeObjectsRelatedById = $collTypeObjectsRelatedById;
                $this->collTypeObjectsRelatedByIdPartial = false;
            }
        }

        return $this->collTypeObjectsRelatedById;
    }

    /**
     * Sets a collection of ChildTypeObject objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $typeObjectsRelatedById A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setTypeObjectsRelatedById(Collection $typeObjectsRelatedById, ?ConnectionInterface $con = null)
    {
        /** @var ChildTypeObject[] $typeObjectsRelatedByIdToDelete */
        $typeObjectsRelatedByIdToDelete = $this->getTypeObjectsRelatedById(new Criteria(), $con)->diff($typeObjectsRelatedById);


        $this->typeObjectsRelatedByIdScheduledForDeletion = $typeObjectsRelatedByIdToDelete;

        foreach ($typeObjectsRelatedByIdToDelete as $typeObjectRelatedByIdRemoved) {
            $typeObjectRelatedByIdRemoved->setTypeObject(null);
        }

        $this->collTypeObjectsRelatedById = null;
        foreach ($typeObjectsRelatedById as $typeObjectRelatedById) {
            $this->addTypeObjectRelatedById($typeObjectRelatedById);
        }

        $this->collTypeObjectsRelatedById = $typeObjectsRelatedById;
        $this->collTypeObjectsRelatedByIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related TypeObject objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related TypeObject objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countTypeObjectsRelatedById(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collTypeObjectsRelatedByIdPartial && !$this->isNew();
        if (null === $this->collTypeObjectsRelatedById || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTypeObjectsRelatedById) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getTypeObjectsRelatedById());
            }

            $query = ChildTypeObjectQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByTypeObject($this)
                ->count($con);
        }

        return count($this->collTypeObjectsRelatedById);
    }

    /**
     * Method called to associate a ChildTypeObject object to this object
     * through the ChildTypeObject foreign key attribute.
     *
     * @param ChildTypeObject $l ChildTypeObject
     * @return $this The current object (for fluent API support)
     */
    public function addTypeObjectRelatedById(ChildTypeObject $l)
    {
        if ($this->collTypeObjectsRelatedById === null) {
            $this->initTypeObjectsRelatedById();
            $this->collTypeObjectsRelatedByIdPartial = true;
        }

        if (!$this->collTypeObjectsRelatedById->contains($l)) {
            $this->doAddTypeObjectRelatedById($l);

            if ($this->typeObjectsRelatedByIdScheduledForDeletion and $this->typeObjectsRelatedByIdScheduledForDeletion->contains($l)) {
                $this->typeObjectsRelatedByIdScheduledForDeletion->remove($this->typeObjectsRelatedByIdScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildTypeObject $typeObjectRelatedById The ChildTypeObject object to add.
     */
    protected function doAddTypeObjectRelatedById(ChildTypeObject $typeObjectRelatedById): void
    {
        $this->collTypeObjectsRelatedById[]= $typeObjectRelatedById;
        $typeObjectRelatedById->setTypeObject($this);
    }

    /**
     * @param ChildTypeObject $typeObjectRelatedById The ChildTypeObject object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeTypeObjectRelatedById(ChildTypeObject $typeObjectRelatedById)
    {
        if ($this->getTypeObjectsRelatedById()->contains($typeObjectRelatedById)) {
            $pos = $this->collTypeObjectsRelatedById->search($typeObjectRelatedById);
            $this->collTypeObjectsRelatedById->remove($pos);
            if (null === $this->typeObjectsRelatedByIdScheduledForDeletion) {
                $this->typeObjectsRelatedByIdScheduledForDeletion = clone $this->collTypeObjectsRelatedById;
                $this->typeObjectsRelatedByIdScheduledForDeletion->clear();
            }
            $this->typeObjectsRelatedByIdScheduledForDeletion[]= $typeObjectRelatedById;
            $typeObjectRelatedById->setTypeObject(null);
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
        if (null !== $this->aTypeObject) {
            $this->aTypeObject->removeTypeObjectRelatedById($this);
        }
        $this->id = null;
        $this->details = null;
        $this->details_unserialized = null;
        $this->dummy_object = null;
        $this->dummy_object_unserialized = null;
        $this->self_ref = null;
        $this->some_array = null;
        $this->some_array_unserialized = null;
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
            if ($this->collTypeObjectsRelatedById) {
                foreach ($this->collTypeObjectsRelatedById as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collTypeObjectsRelatedById = null;
        $this->aTypeObject = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(TypeObjectTableMap::DEFAULT_STRING_FORMAT);
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
