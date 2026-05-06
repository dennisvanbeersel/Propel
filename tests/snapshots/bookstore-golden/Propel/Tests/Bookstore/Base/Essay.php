<?php

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
use Propel\Tests\Bookstore\Author as ChildAuthor;
use Propel\Tests\Bookstore\AuthorQuery as ChildAuthorQuery;
use Propel\Tests\Bookstore\Essay as ChildEssay;
use Propel\Tests\Bookstore\EssayQuery as ChildEssayQuery;
use Propel\Tests\Bookstore\Map\EssayTableMap;

/**
 * Base class that represents a row from the 'essay' table.
 *
 *
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Base
 */
abstract class Essay implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Map\\EssayTableMap';


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
    protected $id;

    /**
     * The value for the title field.
     *
     * @var        string
     */
    protected $title;

    /**
     * The value for the first_author_id field.
     * Foreign Key Author
     * @var        int|null
     */
    protected $first_author_id;

    /**
     * The value for the second_author_id field.
     * Foreign Key Author
     * @var        int|null
     */
    protected $second_author_id;

    /**
     * The value for the subtitle field.
     *
     * @var        string|null
     */
    protected $subtitle;

    /**
     * The value for the next_essay_id field.
     * Book Id
     * @var        int|null
     */
    protected $next_essay_id;

    /**
     * @var        ChildAuthor
     */
    protected $aFirstAuthor;

    /**
     * @var        ChildAuthor
     */
    protected $aSecondAuthor;

    /**
     * @var        ChildEssay
     */
    protected $aEssayRelatedByNextEssayId;

    /**
     * @var        ObjectCollection|ChildEssay[] Collection to store aggregation of ChildEssay objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildEssay> Collection to store aggregation of ChildEssay objects.
     */
    protected $collEssaysRelatedById;
    protected $collEssaysRelatedByIdPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildEssay[]
     * @phpstan-var ObjectCollection&\Traversable<ChildEssay>
     */
    protected $essaysRelatedByIdScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Base\Essay object.
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
     * Compares this with another <code>Essay</code> instance.  If
     * <code>obj</code> is an instance of <code>Essay</code>, delegates to
     * <code>equals(Essay)</code>.  Otherwise, returns <code>false</code>.
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
     * Get the [title] column value.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the [first_author_id] column value.
     * Foreign Key Author
     * @return int|null
     */
    public function getFirstAuthorId()
    {
        return $this->first_author_id;
    }

    /**
     * Get the [second_author_id] column value.
     * Foreign Key Author
     * @return int|null
     */
    public function getSecondAuthorId()
    {
        return $this->second_author_id;
    }

    /**
     * Get the [subtitle] column value.
     *
     * @return string|null
     */
    public function getSecondTitle()
    {
        return $this->subtitle;
    }

    /**
     * Get the [next_essay_id] column value.
     * Book Id
     * @return int|null
     */
    public function getNextEssayId()
    {
        return $this->next_essay_id;
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
            $this->modifiedColumns[EssayTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [title] column.
     *
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setTitle($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->title !== $v) {
            $this->title = $v;
            $this->modifiedColumns[EssayTableMap::COL_TITLE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [first_author_id] column.
     * Foreign Key Author
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setFirstAuthorId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->first_author_id !== $v) {
            $this->first_author_id = $v;
            $this->modifiedColumns[EssayTableMap::COL_FIRST_AUTHOR_ID] = true;
        }

        if ($this->aFirstAuthor !== null && $this->aFirstAuthor->getId() !== $v) {
            $this->aFirstAuthor = null;
        }

        return $this;
    }

    /**
     * Set the value of [second_author_id] column.
     * Foreign Key Author
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setSecondAuthorId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->second_author_id !== $v) {
            $this->second_author_id = $v;
            $this->modifiedColumns[EssayTableMap::COL_SECOND_AUTHOR_ID] = true;
        }

        if ($this->aSecondAuthor !== null && $this->aSecondAuthor->getId() !== $v) {
            $this->aSecondAuthor = null;
        }

        return $this;
    }

    /**
     * Set the value of [subtitle] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setSecondTitle($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->subtitle !== $v) {
            $this->subtitle = $v;
            $this->modifiedColumns[EssayTableMap::COL_SUBTITLE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [next_essay_id] column.
     * Book Id
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setNextEssayId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->next_essay_id !== $v) {
            $this->next_essay_id = $v;
            $this->modifiedColumns[EssayTableMap::COL_NEXT_ESSAY_ID] = true;
        }

        if ($this->aEssayRelatedByNextEssayId !== null && $this->aEssayRelatedByNextEssayId->getId() !== $v) {
            $this->aEssayRelatedByNextEssayId = null;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : EssayTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : EssayTableMap::translateFieldName('Title', TableMap::TYPE_PHPNAME, $indexType)];
            $this->title = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : EssayTableMap::translateFieldName('FirstAuthorId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->first_author_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : EssayTableMap::translateFieldName('SecondAuthorId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->second_author_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : EssayTableMap::translateFieldName('SecondTitle', TableMap::TYPE_PHPNAME, $indexType)];
            $this->subtitle = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : EssayTableMap::translateFieldName('NextEssayId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->next_essay_id = (null !== $col) ? (int) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 6; // 6 = EssayTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Essay'), 0, $e);
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
        if ($this->aFirstAuthor !== null && $this->first_author_id !== $this->aFirstAuthor->getId()) {
            $this->aFirstAuthor = null;
        }
        if ($this->aSecondAuthor !== null && $this->second_author_id !== $this->aSecondAuthor->getId()) {
            $this->aSecondAuthor = null;
        }
        if ($this->aEssayRelatedByNextEssayId !== null && $this->next_essay_id !== $this->aEssayRelatedByNextEssayId->getId()) {
            $this->aEssayRelatedByNextEssayId = null;
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
            $con = Propel::getServiceContainer()->getReadConnection(EssayTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildEssayQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aFirstAuthor = null;
            $this->aSecondAuthor = null;
            $this->aEssayRelatedByNextEssayId = null;
            $this->collEssaysRelatedById = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see Essay::setDeleted()
     * @see Essay::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(EssayTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildEssayQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(EssayTableMap::DATABASE_NAME);
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
                EssayTableMap::addInstanceToPool($this);
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

            if ($this->aFirstAuthor !== null) {
                if ($this->aFirstAuthor->isModified() || $this->aFirstAuthor->isNew()) {
                    $affectedRows += $this->aFirstAuthor->save($con);
                }
                $this->setFirstAuthor($this->aFirstAuthor);
            }

            if ($this->aSecondAuthor !== null) {
                if ($this->aSecondAuthor->isModified() || $this->aSecondAuthor->isNew()) {
                    $affectedRows += $this->aSecondAuthor->save($con);
                }
                $this->setSecondAuthor($this->aSecondAuthor);
            }

            if ($this->aEssayRelatedByNextEssayId !== null) {
                if ($this->aEssayRelatedByNextEssayId->isModified() || $this->aEssayRelatedByNextEssayId->isNew()) {
                    $affectedRows += $this->aEssayRelatedByNextEssayId->save($con);
                }
                $this->setEssayRelatedByNextEssayId($this->aEssayRelatedByNextEssayId);
            }

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

            if ($this->essaysRelatedByIdScheduledForDeletion !== null) {
                if (!$this->essaysRelatedByIdScheduledForDeletion->isEmpty()) {
                    foreach ($this->essaysRelatedByIdScheduledForDeletion as $essayRelatedById) {
                        // need to save related object because we set the relation to null
                        $essayRelatedById->save($con);
                    }
                    $this->essaysRelatedByIdScheduledForDeletion = null;
                }
            }

            if ($this->collEssaysRelatedById !== null) {
                foreach ($this->collEssaysRelatedById as $referrerFK) {
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

        $this->modifiedColumns[EssayTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . EssayTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(EssayTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(EssayTableMap::COL_TITLE)) {
            $modifiedColumns[':p' . $index++]  = 'title';
        }
        if ($this->isColumnModified(EssayTableMap::COL_FIRST_AUTHOR_ID)) {
            $modifiedColumns[':p' . $index++]  = 'first_author_id';
        }
        if ($this->isColumnModified(EssayTableMap::COL_SECOND_AUTHOR_ID)) {
            $modifiedColumns[':p' . $index++]  = 'second_author_id';
        }
        if ($this->isColumnModified(EssayTableMap::COL_SUBTITLE)) {
            $modifiedColumns[':p' . $index++]  = 'subtitle';
        }
        if ($this->isColumnModified(EssayTableMap::COL_NEXT_ESSAY_ID)) {
            $modifiedColumns[':p' . $index++]  = 'next_essay_id';
        }

        $sql = sprintf(
            'INSERT INTO essay (%s) VALUES (%s)',
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
                    case 'title':
                        $stmt->bindValue($identifier, $this->title, PDO::PARAM_STR);

                        break;
                    case 'first_author_id':
                        $stmt->bindValue($identifier, $this->first_author_id, PDO::PARAM_INT);

                        break;
                    case 'second_author_id':
                        $stmt->bindValue($identifier, $this->second_author_id, PDO::PARAM_INT);

                        break;
                    case 'subtitle':
                        $stmt->bindValue($identifier, $this->subtitle, PDO::PARAM_STR);

                        break;
                    case 'next_essay_id':
                        $stmt->bindValue($identifier, $this->next_essay_id, PDO::PARAM_INT);

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
        $pos = EssayTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getTitle();

            case 2:
                return $this->getFirstAuthorId();

            case 3:
                return $this->getSecondAuthorId();

            case 4:
                return $this->getSecondTitle();

            case 5:
                return $this->getNextEssayId();

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
        if (isset($alreadyDumpedObjects['Essay'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['Essay'][$this->hashCode()] = true;
        $keys = EssayTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getTitle(),
            $keys[2] => $this->getFirstAuthorId(),
            $keys[3] => $this->getSecondAuthorId(),
            $keys[4] => $this->getSecondTitle(),
            $keys[5] => $this->getNextEssayId(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aFirstAuthor) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'author';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'author';
                        break;
                    default:
                        $key = 'FirstAuthor';
                }

                $result[$key] = $this->aFirstAuthor->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aSecondAuthor) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'author';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'author';
                        break;
                    default:
                        $key = 'SecondAuthor';
                }

                $result[$key] = $this->aSecondAuthor->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aEssayRelatedByNextEssayId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'essay';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'essay';
                        break;
                    default:
                        $key = 'Essay';
                }

                $result[$key] = $this->aEssayRelatedByNextEssayId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collEssaysRelatedById) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'essays';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'essays';
                        break;
                    default:
                        $key = 'Essays';
                }

                $result[$key] = $this->collEssaysRelatedById->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = EssayTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setTitle($value);
                break;
            case 2:
                $this->setFirstAuthorId($value);
                break;
            case 3:
                $this->setSecondAuthorId($value);
                break;
            case 4:
                $this->setSecondTitle($value);
                break;
            case 5:
                $this->setNextEssayId($value);
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
        $keys = EssayTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setTitle($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setFirstAuthorId($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setSecondAuthorId($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setSecondTitle($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setNextEssayId($arr[$keys[5]]);
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
        $criteria = new Criteria(EssayTableMap::DATABASE_NAME);

        if ($this->isColumnModified(EssayTableMap::COL_ID)) {
            $criteria->add(EssayTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(EssayTableMap::COL_TITLE)) {
            $criteria->add(EssayTableMap::COL_TITLE, $this->title);
        }
        if ($this->isColumnModified(EssayTableMap::COL_FIRST_AUTHOR_ID)) {
            $criteria->add(EssayTableMap::COL_FIRST_AUTHOR_ID, $this->first_author_id);
        }
        if ($this->isColumnModified(EssayTableMap::COL_SECOND_AUTHOR_ID)) {
            $criteria->add(EssayTableMap::COL_SECOND_AUTHOR_ID, $this->second_author_id);
        }
        if ($this->isColumnModified(EssayTableMap::COL_SUBTITLE)) {
            $criteria->add(EssayTableMap::COL_SUBTITLE, $this->subtitle);
        }
        if ($this->isColumnModified(EssayTableMap::COL_NEXT_ESSAY_ID)) {
            $criteria->add(EssayTableMap::COL_NEXT_ESSAY_ID, $this->next_essay_id);
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
        $criteria = ChildEssayQuery::create();
        $criteria->add(EssayTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Essay (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setTitle($this->getTitle());
        $copyObj->setFirstAuthorId($this->getFirstAuthorId());
        $copyObj->setSecondAuthorId($this->getSecondAuthorId());
        $copyObj->setSecondTitle($this->getSecondTitle());
        $copyObj->setNextEssayId($this->getNextEssayId());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getEssaysRelatedById() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addEssayRelatedById($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\Essay Clone of current object.
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
     * Declares an association between this object and a ChildAuthor object.
     *
     * @param ChildAuthor|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setFirstAuthor(?ChildAuthor $v = null)
    {
        if ($v === null) {
            $this->setFirstAuthorId(NULL);
        } else {
            $this->setFirstAuthorId($v->getId());
        }

        $this->aFirstAuthor = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildAuthor object, it will not be re-added.
        if ($v !== null) {
            $v->addEssayRelatedByFirstAuthorId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildAuthor object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildAuthor|null The associated ChildAuthor object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getFirstAuthor(?ConnectionInterface $con = null)
    {
        if ($this->aFirstAuthor === null && ($this->first_author_id !== null)) {
            $this->aFirstAuthor = ChildAuthorQuery::create()->findPk($this->first_author_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aFirstAuthor->addEssaysRelatedByFirstAuthorId($this);
             */
        }

        return $this->aFirstAuthor;
    }

    /**
     * Declares an association between this object and a ChildAuthor object.
     *
     * @param ChildAuthor|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setSecondAuthor(?ChildAuthor $v = null)
    {
        if ($v === null) {
            $this->setSecondAuthorId(NULL);
        } else {
            $this->setSecondAuthorId($v->getId());
        }

        $this->aSecondAuthor = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildAuthor object, it will not be re-added.
        if ($v !== null) {
            $v->addEssayRelatedBySecondAuthorId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildAuthor object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildAuthor|null The associated ChildAuthor object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getSecondAuthor(?ConnectionInterface $con = null)
    {
        if ($this->aSecondAuthor === null && ($this->second_author_id !== null)) {
            $this->aSecondAuthor = ChildAuthorQuery::create()->findPk($this->second_author_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aSecondAuthor->addEssaysRelatedBySecondAuthorId($this);
             */
        }

        return $this->aSecondAuthor;
    }

    /**
     * Declares an association between this object and a ChildEssay object.
     *
     * @param ChildEssay|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setEssayRelatedByNextEssayId(?ChildEssay $v = null)
    {
        if ($v === null) {
            $this->setNextEssayId(NULL);
        } else {
            $this->setNextEssayId($v->getId());
        }

        $this->aEssayRelatedByNextEssayId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildEssay object, it will not be re-added.
        if ($v !== null) {
            $v->addEssayRelatedById($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildEssay object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildEssay|null The associated ChildEssay object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getEssayRelatedByNextEssayId(?ConnectionInterface $con = null)
    {
        if ($this->aEssayRelatedByNextEssayId === null && ($this->next_essay_id !== null)) {
            $this->aEssayRelatedByNextEssayId = ChildEssayQuery::create()->findPk($this->next_essay_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aEssayRelatedByNextEssayId->addEssaysRelatedById($this);
             */
        }

        return $this->aEssayRelatedByNextEssayId;
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
        if ('EssayRelatedById' === $relationName) {
            $this->initEssaysRelatedById();
            return;
        }
    }

    /**
     * Clears out the collEssaysRelatedById collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addEssaysRelatedById()
     */
    public function clearEssaysRelatedById()
    {
        $this->collEssaysRelatedById = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collEssaysRelatedById collection loaded partially.
     *
     * @return void
     */
    public function resetPartialEssaysRelatedById($v = true): void
    {
        $this->collEssaysRelatedByIdPartial = $v;
    }

    /**
     * Initializes the collEssaysRelatedById collection.
     *
     * By default this just sets the collEssaysRelatedById collection to an empty array (like clearcollEssaysRelatedById());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initEssaysRelatedById(bool $overrideExisting = true): void
    {
        if (null !== $this->collEssaysRelatedById && !$overrideExisting) {
            return;
        }

        $collectionClassName = EssayTableMap::getTableMap()->getCollectionClassName();

        $this->collEssaysRelatedById = new $collectionClassName;
        $this->collEssaysRelatedById->setModel('\Propel\Tests\Bookstore\Essay');
    }

    /**
     * Gets an array of ChildEssay objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildEssay is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildEssay[] List of ChildEssay objects
     * @phpstan-return ObjectCollection&\Traversable<ChildEssay> List of ChildEssay objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getEssaysRelatedById(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collEssaysRelatedByIdPartial && !$this->isNew();
        if (null === $this->collEssaysRelatedById || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collEssaysRelatedById) {
                    $this->initEssaysRelatedById();
                } else {
                    $collectionClassName = EssayTableMap::getTableMap()->getCollectionClassName();

                    $collEssaysRelatedById = new $collectionClassName;
                    $collEssaysRelatedById->setModel('\Propel\Tests\Bookstore\Essay');

                    return $collEssaysRelatedById;
                }
            } else {
                $collEssaysRelatedById = ChildEssayQuery::create(null, $criteria)
                    ->filterByEssayRelatedByNextEssayId($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collEssaysRelatedByIdPartial && count($collEssaysRelatedById)) {
                        $this->initEssaysRelatedById(false);

                        foreach ($collEssaysRelatedById as $obj) {
                            if (false === $this->collEssaysRelatedById->contains($obj)) {
                                $this->collEssaysRelatedById->append($obj);
                            }
                        }

                        $this->collEssaysRelatedByIdPartial = true;
                    }

                    return $collEssaysRelatedById;
                }

                if ($partial && $this->collEssaysRelatedById) {
                    foreach ($this->collEssaysRelatedById as $obj) {
                        if ($obj->isNew()) {
                            $collEssaysRelatedById[] = $obj;
                        }
                    }
                }

                $this->collEssaysRelatedById = $collEssaysRelatedById;
                $this->collEssaysRelatedByIdPartial = false;
            }
        }

        return $this->collEssaysRelatedById;
    }

    /**
     * Sets a collection of ChildEssay objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $essaysRelatedById A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setEssaysRelatedById(Collection $essaysRelatedById, ?ConnectionInterface $con = null)
    {
        /** @var ChildEssay[] $essaysRelatedByIdToDelete */
        $essaysRelatedByIdToDelete = $this->getEssaysRelatedById(new Criteria(), $con)->diff($essaysRelatedById);


        $this->essaysRelatedByIdScheduledForDeletion = $essaysRelatedByIdToDelete;

        foreach ($essaysRelatedByIdToDelete as $essayRelatedByIdRemoved) {
            $essayRelatedByIdRemoved->setEssayRelatedByNextEssayId(null);
        }

        $this->collEssaysRelatedById = null;
        foreach ($essaysRelatedById as $essayRelatedById) {
            $this->addEssayRelatedById($essayRelatedById);
        }

        $this->collEssaysRelatedById = $essaysRelatedById;
        $this->collEssaysRelatedByIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Essay objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related Essay objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countEssaysRelatedById(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collEssaysRelatedByIdPartial && !$this->isNew();
        if (null === $this->collEssaysRelatedById || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collEssaysRelatedById) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getEssaysRelatedById());
            }

            $query = ChildEssayQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByEssayRelatedByNextEssayId($this)
                ->count($con);
        }

        return count($this->collEssaysRelatedById);
    }

    /**
     * Method called to associate a ChildEssay object to this object
     * through the ChildEssay foreign key attribute.
     *
     * @param ChildEssay $l ChildEssay
     * @return $this The current object (for fluent API support)
     */
    public function addEssayRelatedById(ChildEssay $l)
    {
        if ($this->collEssaysRelatedById === null) {
            $this->initEssaysRelatedById();
            $this->collEssaysRelatedByIdPartial = true;
        }

        if (!$this->collEssaysRelatedById->contains($l)) {
            $this->doAddEssayRelatedById($l);

            if ($this->essaysRelatedByIdScheduledForDeletion and $this->essaysRelatedByIdScheduledForDeletion->contains($l)) {
                $this->essaysRelatedByIdScheduledForDeletion->remove($this->essaysRelatedByIdScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildEssay $essayRelatedById The ChildEssay object to add.
     */
    protected function doAddEssayRelatedById(ChildEssay $essayRelatedById): void
    {
        $this->collEssaysRelatedById[]= $essayRelatedById;
        $essayRelatedById->setEssayRelatedByNextEssayId($this);
    }

    /**
     * @param ChildEssay $essayRelatedById The ChildEssay object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeEssayRelatedById(ChildEssay $essayRelatedById)
    {
        if ($this->getEssaysRelatedById()->contains($essayRelatedById)) {
            $pos = $this->collEssaysRelatedById->search($essayRelatedById);
            $this->collEssaysRelatedById->remove($pos);
            if (null === $this->essaysRelatedByIdScheduledForDeletion) {
                $this->essaysRelatedByIdScheduledForDeletion = clone $this->collEssaysRelatedById;
                $this->essaysRelatedByIdScheduledForDeletion->clear();
            }
            $this->essaysRelatedByIdScheduledForDeletion[]= $essayRelatedById;
            $essayRelatedById->setEssayRelatedByNextEssayId(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Essay is new, it will return
     * an empty collection; or if this Essay has previously
     * been saved, it will retrieve related EssaysRelatedById from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Essay.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildEssay[] List of ChildEssay objects
     * @phpstan-return ObjectCollection&\Traversable<ChildEssay> List of ChildEssay objects
     */
    public function getEssaysRelatedByIdJoinFirstAuthor(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildEssayQuery::create(null, $criteria);
        $query->joinWith('FirstAuthor', $joinBehavior);

        return $this->getEssaysRelatedById($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Essay is new, it will return
     * an empty collection; or if this Essay has previously
     * been saved, it will retrieve related EssaysRelatedById from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Essay.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildEssay[] List of ChildEssay objects
     * @phpstan-return ObjectCollection&\Traversable<ChildEssay> List of ChildEssay objects
     */
    public function getEssaysRelatedByIdJoinSecondAuthor(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildEssayQuery::create(null, $criteria);
        $query->joinWith('SecondAuthor', $joinBehavior);

        return $this->getEssaysRelatedById($query, $con);
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
        if (null !== $this->aFirstAuthor) {
            $this->aFirstAuthor->removeEssayRelatedByFirstAuthorId($this);
        }
        if (null !== $this->aSecondAuthor) {
            $this->aSecondAuthor->removeEssayRelatedBySecondAuthorId($this);
        }
        if (null !== $this->aEssayRelatedByNextEssayId) {
            $this->aEssayRelatedByNextEssayId->removeEssayRelatedById($this);
        }
        $this->id = null;
        $this->title = null;
        $this->first_author_id = null;
        $this->second_author_id = null;
        $this->subtitle = null;
        $this->next_essay_id = null;
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
            if ($this->collEssaysRelatedById) {
                foreach ($this->collEssaysRelatedById as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collEssaysRelatedById = null;
        $this->aFirstAuthor = null;
        $this->aSecondAuthor = null;
        $this->aEssayRelatedByNextEssayId = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string The value of the 'title' column
     */
    public function __toString(): string
    {
        return (string)$this->getTitle();
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
