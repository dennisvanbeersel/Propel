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
use Propel\Tests\Bookstore\Book as ChildBook;
use Propel\Tests\Bookstore\BookQuery as ChildBookQuery;
use Propel\Tests\Bookstore\Essay as ChildEssay;
use Propel\Tests\Bookstore\EssayQuery as ChildEssayQuery;
use Propel\Tests\Bookstore\PolymorphicRelationLog as ChildPolymorphicRelationLog;
use Propel\Tests\Bookstore\PolymorphicRelationLogQuery as ChildPolymorphicRelationLogQuery;
use Propel\Tests\Bookstore\Map\AuthorTableMap;
use Propel\Tests\Bookstore\Map\BookTableMap;
use Propel\Tests\Bookstore\Map\EssayTableMap;
use Propel\Tests\Bookstore\Map\PolymorphicRelationLogTableMap;

/**
 * Base class that represents a row from the 'author' table.
 *
 * Author Table
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Base
 */
abstract class Author implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Map\\AuthorTableMap';


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
     * Author Id
     * @var        int
     */
    protected $id;

    /**
     * The value for the first_name field.
     * First Name
     * @var        string
     */
    protected $first_name;

    /**
     * The value for the last_name field.
     * Last Name
     * @var        string
     */
    protected $last_name;

    /**
     * The value for the email field.
     * E-Mail Address
     * @var        string|null
     */
    protected $email;

    /**
     * The value for the age field.
     * The authors age
     * @var        int|null
     */
    protected $age;

    /**
     * @var        ObjectCollection|ChildBook[] Collection to store aggregation of ChildBook objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBook> Collection to store aggregation of ChildBook objects.
     */
    protected $collBooks;
    protected $collBooksPartial;

    /**
     * @var        ObjectCollection|ChildEssay[] Collection to store aggregation of ChildEssay objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildEssay> Collection to store aggregation of ChildEssay objects.
     */
    protected $collEssaysRelatedByFirstAuthorId;
    protected $collEssaysRelatedByFirstAuthorIdPartial;

    /**
     * @var        ObjectCollection|ChildEssay[] Collection to store aggregation of ChildEssay objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildEssay> Collection to store aggregation of ChildEssay objects.
     */
    protected $collEssaysRelatedBySecondAuthorId;
    protected $collEssaysRelatedBySecondAuthorIdPartial;

    /**
     * @var        ObjectCollection|ChildPolymorphicRelationLog[] Collection to store aggregation of ChildPolymorphicRelationLog objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildPolymorphicRelationLog> Collection to store aggregation of ChildPolymorphicRelationLog objects.
     */
    protected $collPolymorphicRelationLogs;
    protected $collPolymorphicRelationLogsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBook[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBook>
     */
    protected $booksScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildEssay[]
     * @phpstan-var ObjectCollection&\Traversable<ChildEssay>
     */
    protected $essaysRelatedByFirstAuthorIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildEssay[]
     * @phpstan-var ObjectCollection&\Traversable<ChildEssay>
     */
    protected $essaysRelatedBySecondAuthorIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildPolymorphicRelationLog[]
     * @phpstan-var ObjectCollection&\Traversable<ChildPolymorphicRelationLog>
     */
    protected $polymorphicRelationLogsScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Base\Author object.
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
     * Compares this with another <code>Author</code> instance.  If
     * <code>obj</code> is an instance of <code>Author</code>, delegates to
     * <code>equals(Author)</code>.  Otherwise, returns <code>false</code>.
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
     * Author Id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [first_name] column value.
     * First Name
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Get the [last_name] column value.
     * Last Name
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Get the [email] column value.
     * E-Mail Address
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the [age] column value.
     * The authors age
     * @return int|null
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set the value of [id] column.
     * Author Id
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
            $this->modifiedColumns[AuthorTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [first_name] column.
     * First Name
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setFirstName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->first_name !== $v) {
            $this->first_name = $v;
            $this->modifiedColumns[AuthorTableMap::COL_FIRST_NAME] = true;
        }

        return $this;
    }

    /**
     * Set the value of [last_name] column.
     * Last Name
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setLastName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->last_name !== $v) {
            $this->last_name = $v;
            $this->modifiedColumns[AuthorTableMap::COL_LAST_NAME] = true;
        }

        return $this;
    }

    /**
     * Set the value of [email] column.
     * E-Mail Address
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setEmail($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->email !== $v) {
            $this->email = $v;
            $this->modifiedColumns[AuthorTableMap::COL_EMAIL] = true;
        }

        return $this;
    }

    /**
     * Set the value of [age] column.
     * The authors age
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setAge($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->age !== $v) {
            $this->age = $v;
            $this->modifiedColumns[AuthorTableMap::COL_AGE] = true;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : AuthorTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : AuthorTableMap::translateFieldName('FirstName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->first_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : AuthorTableMap::translateFieldName('LastName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->last_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : AuthorTableMap::translateFieldName('Email', TableMap::TYPE_PHPNAME, $indexType)];
            $this->email = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : AuthorTableMap::translateFieldName('Age', TableMap::TYPE_PHPNAME, $indexType)];
            $this->age = (null !== $col) ? (int) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 5; // 5 = AuthorTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Author'), 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(AuthorTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildAuthorQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collBooks = null;

            $this->collEssaysRelatedByFirstAuthorId = null;

            $this->collEssaysRelatedBySecondAuthorId = null;

            $this->collPolymorphicRelationLogs = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see Author::setDeleted()
     * @see Author::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(AuthorTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildAuthorQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(AuthorTableMap::DATABASE_NAME);
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
                AuthorTableMap::addInstanceToPool($this);
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

            if ($this->booksScheduledForDeletion !== null) {
                if (!$this->booksScheduledForDeletion->isEmpty()) {
                    foreach ($this->booksScheduledForDeletion as $book) {
                        // need to save related object because we set the relation to null
                        $book->save($con);
                    }
                    $this->booksScheduledForDeletion = null;
                }
            }

            if ($this->collBooks !== null) {
                foreach ($this->collBooks as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->essaysRelatedByFirstAuthorIdScheduledForDeletion !== null) {
                if (!$this->essaysRelatedByFirstAuthorIdScheduledForDeletion->isEmpty()) {
                    foreach ($this->essaysRelatedByFirstAuthorIdScheduledForDeletion as $essayRelatedByFirstAuthorId) {
                        // need to save related object because we set the relation to null
                        $essayRelatedByFirstAuthorId->save($con);
                    }
                    $this->essaysRelatedByFirstAuthorIdScheduledForDeletion = null;
                }
            }

            if ($this->collEssaysRelatedByFirstAuthorId !== null) {
                foreach ($this->collEssaysRelatedByFirstAuthorId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->essaysRelatedBySecondAuthorIdScheduledForDeletion !== null) {
                if (!$this->essaysRelatedBySecondAuthorIdScheduledForDeletion->isEmpty()) {
                    foreach ($this->essaysRelatedBySecondAuthorIdScheduledForDeletion as $essayRelatedBySecondAuthorId) {
                        // need to save related object because we set the relation to null
                        $essayRelatedBySecondAuthorId->save($con);
                    }
                    $this->essaysRelatedBySecondAuthorIdScheduledForDeletion = null;
                }
            }

            if ($this->collEssaysRelatedBySecondAuthorId !== null) {
                foreach ($this->collEssaysRelatedBySecondAuthorId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->polymorphicRelationLogsScheduledForDeletion !== null) {
                if (!$this->polymorphicRelationLogsScheduledForDeletion->isEmpty()) {
                    foreach ($this->polymorphicRelationLogsScheduledForDeletion as $polymorphicRelationLog) {
                        // need to save related object because we set the relation to null
                        $polymorphicRelationLog->save($con);
                    }
                    $this->polymorphicRelationLogsScheduledForDeletion = null;
                }
            }

            if ($this->collPolymorphicRelationLogs !== null) {
                foreach ($this->collPolymorphicRelationLogs as $referrerFK) {
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

        $this->modifiedColumns[AuthorTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . AuthorTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(AuthorTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(AuthorTableMap::COL_FIRST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'first_name';
        }
        if ($this->isColumnModified(AuthorTableMap::COL_LAST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'last_name';
        }
        if ($this->isColumnModified(AuthorTableMap::COL_EMAIL)) {
            $modifiedColumns[':p' . $index++]  = 'email';
        }
        if ($this->isColumnModified(AuthorTableMap::COL_AGE)) {
            $modifiedColumns[':p' . $index++]  = 'age';
        }

        $sql = sprintf(
            'INSERT INTO author (%s) VALUES (%s)',
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
                    case 'first_name':
                        $stmt->bindValue($identifier, $this->first_name, PDO::PARAM_STR);

                        break;
                    case 'last_name':
                        $stmt->bindValue($identifier, $this->last_name, PDO::PARAM_STR);

                        break;
                    case 'email':
                        $stmt->bindValue($identifier, $this->email, PDO::PARAM_STR);

                        break;
                    case 'age':
                        $stmt->bindValue($identifier, $this->age, PDO::PARAM_INT);

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
        $pos = AuthorTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getFirstName();

            case 2:
                return $this->getLastName();

            case 3:
                return $this->getEmail();

            case 4:
                return $this->getAge();

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
        if (isset($alreadyDumpedObjects['Author'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['Author'][$this->hashCode()] = true;
        $keys = AuthorTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getFirstName(),
            $keys[2] => $this->getLastName(),
            $keys[3] => $this->getEmail(),
            $keys[4] => $this->getAge(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collBooks) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'books';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'books';
                        break;
                    default:
                        $key = 'Books';
                }

                $result[$key] = $this->collBooks->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collEssaysRelatedByFirstAuthorId) {

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

                $result[$key] = $this->collEssaysRelatedByFirstAuthorId->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collEssaysRelatedBySecondAuthorId) {

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

                $result[$key] = $this->collEssaysRelatedBySecondAuthorId->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPolymorphicRelationLogs) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'polymorphicRelationLogs';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'polymorphic_relation_logs';
                        break;
                    default:
                        $key = 'PolymorphicRelationLogs';
                }

                $result[$key] = $this->collPolymorphicRelationLogs->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = AuthorTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setFirstName($value);
                break;
            case 2:
                $this->setLastName($value);
                break;
            case 3:
                $this->setEmail($value);
                break;
            case 4:
                $this->setAge($value);
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
        $keys = AuthorTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setFirstName($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setLastName($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setEmail($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setAge($arr[$keys[4]]);
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
        $criteria = new Criteria(AuthorTableMap::DATABASE_NAME);

        if ($this->isColumnModified(AuthorTableMap::COL_ID)) {
            $criteria->add(AuthorTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(AuthorTableMap::COL_FIRST_NAME)) {
            $criteria->add(AuthorTableMap::COL_FIRST_NAME, $this->first_name);
        }
        if ($this->isColumnModified(AuthorTableMap::COL_LAST_NAME)) {
            $criteria->add(AuthorTableMap::COL_LAST_NAME, $this->last_name);
        }
        if ($this->isColumnModified(AuthorTableMap::COL_EMAIL)) {
            $criteria->add(AuthorTableMap::COL_EMAIL, $this->email);
        }
        if ($this->isColumnModified(AuthorTableMap::COL_AGE)) {
            $criteria->add(AuthorTableMap::COL_AGE, $this->age);
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
        $criteria = ChildAuthorQuery::create();
        $criteria->add(AuthorTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Author (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setFirstName($this->getFirstName());
        $copyObj->setLastName($this->getLastName());
        $copyObj->setEmail($this->getEmail());
        $copyObj->setAge($this->getAge());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getBooks() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBook($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getEssaysRelatedByFirstAuthorId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addEssayRelatedByFirstAuthorId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getEssaysRelatedBySecondAuthorId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addEssayRelatedBySecondAuthorId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPolymorphicRelationLogs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPolymorphicRelationLog($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\Author Clone of current object.
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
        if ('Book' === $relationName) {
            $this->initBooks();
            return;
        }
        if ('EssayRelatedByFirstAuthorId' === $relationName) {
            $this->initEssaysRelatedByFirstAuthorId();
            return;
        }
        if ('EssayRelatedBySecondAuthorId' === $relationName) {
            $this->initEssaysRelatedBySecondAuthorId();
            return;
        }
        if ('PolymorphicRelationLog' === $relationName) {
            $this->initPolymorphicRelationLogs();
            return;
        }
    }

    /**
     * Clears out the collBooks collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBooks()
     */
    public function clearBooks()
    {
        $this->collBooks = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBooks collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBooks($v = true): void
    {
        $this->collBooksPartial = $v;
    }

    /**
     * Initializes the collBooks collection.
     *
     * By default this just sets the collBooks collection to an empty array (like clearcollBooks());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBooks(bool $overrideExisting = true): void
    {
        if (null !== $this->collBooks && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookTableMap::getTableMap()->getCollectionClassName();

        $this->collBooks = new $collectionClassName;
        $this->collBooks->setModel('\Propel\Tests\Bookstore\Book');
    }

    /**
     * Gets an array of ChildBook objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildAuthor is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBook[] List of ChildBook objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBook> List of ChildBook objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBooks(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBooksPartial && !$this->isNew();
        if (null === $this->collBooks || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBooks) {
                    $this->initBooks();
                } else {
                    $collectionClassName = BookTableMap::getTableMap()->getCollectionClassName();

                    $collBooks = new $collectionClassName;
                    $collBooks->setModel('\Propel\Tests\Bookstore\Book');

                    return $collBooks;
                }
            } else {
                $collBooks = ChildBookQuery::create(null, $criteria)
                    ->filterByAuthor($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBooksPartial && count($collBooks)) {
                        $this->initBooks(false);

                        foreach ($collBooks as $obj) {
                            if (false === $this->collBooks->contains($obj)) {
                                $this->collBooks->append($obj);
                            }
                        }

                        $this->collBooksPartial = true;
                    }

                    return $collBooks;
                }

                if ($partial && $this->collBooks) {
                    foreach ($this->collBooks as $obj) {
                        if ($obj->isNew()) {
                            $collBooks[] = $obj;
                        }
                    }
                }

                $this->collBooks = $collBooks;
                $this->collBooksPartial = false;
            }
        }

        return $this->collBooks;
    }

    /**
     * Sets a collection of ChildBook objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $books A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBooks(Collection $books, ?ConnectionInterface $con = null)
    {
        /** @var ChildBook[] $booksToDelete */
        $booksToDelete = $this->getBooks(new Criteria(), $con)->diff($books);


        $this->booksScheduledForDeletion = $booksToDelete;

        foreach ($booksToDelete as $bookRemoved) {
            $bookRemoved->setAuthor(null);
        }

        $this->collBooks = null;
        foreach ($books as $book) {
            $this->addBook($book);
        }

        $this->collBooks = $books;
        $this->collBooksPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Book objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related Book objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBooks(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBooksPartial && !$this->isNew();
        if (null === $this->collBooks || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBooks) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBooks());
            }

            $query = ChildBookQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthor($this)
                ->count($con);
        }

        return count($this->collBooks);
    }

    /**
     * Method called to associate a ChildBook object to this object
     * through the ChildBook foreign key attribute.
     *
     * @param ChildBook $l ChildBook
     * @return $this The current object (for fluent API support)
     */
    public function addBook(ChildBook $l)
    {
        if ($this->collBooks === null) {
            $this->initBooks();
            $this->collBooksPartial = true;
        }

        if (!$this->collBooks->contains($l)) {
            $this->doAddBook($l);

            if ($this->booksScheduledForDeletion and $this->booksScheduledForDeletion->contains($l)) {
                $this->booksScheduledForDeletion->remove($this->booksScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBook $book The ChildBook object to add.
     */
    protected function doAddBook(ChildBook $book): void
    {
        $this->collBooks[]= $book;
        $book->setAuthor($this);
    }

    /**
     * @param ChildBook $book The ChildBook object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBook(ChildBook $book)
    {
        if ($this->getBooks()->contains($book)) {
            $pos = $this->collBooks->search($book);
            $this->collBooks->remove($pos);
            if (null === $this->booksScheduledForDeletion) {
                $this->booksScheduledForDeletion = clone $this->collBooks;
                $this->booksScheduledForDeletion->clear();
            }
            $this->booksScheduledForDeletion[]= $book;
            $book->setAuthor(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Author is new, it will return
     * an empty collection; or if this Author has previously
     * been saved, it will retrieve related Books from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Author.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBook[] List of ChildBook objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBook> List of ChildBook objects
     */
    public function getBooksJoinPublisher(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookQuery::create(null, $criteria);
        $query->joinWith('Publisher', $joinBehavior);

        return $this->getBooks($query, $con);
    }

    /**
     * Clears out the collEssaysRelatedByFirstAuthorId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addEssaysRelatedByFirstAuthorId()
     */
    public function clearEssaysRelatedByFirstAuthorId()
    {
        $this->collEssaysRelatedByFirstAuthorId = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collEssaysRelatedByFirstAuthorId collection loaded partially.
     *
     * @return void
     */
    public function resetPartialEssaysRelatedByFirstAuthorId($v = true): void
    {
        $this->collEssaysRelatedByFirstAuthorIdPartial = $v;
    }

    /**
     * Initializes the collEssaysRelatedByFirstAuthorId collection.
     *
     * By default this just sets the collEssaysRelatedByFirstAuthorId collection to an empty array (like clearcollEssaysRelatedByFirstAuthorId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initEssaysRelatedByFirstAuthorId(bool $overrideExisting = true): void
    {
        if (null !== $this->collEssaysRelatedByFirstAuthorId && !$overrideExisting) {
            return;
        }

        $collectionClassName = EssayTableMap::getTableMap()->getCollectionClassName();

        $this->collEssaysRelatedByFirstAuthorId = new $collectionClassName;
        $this->collEssaysRelatedByFirstAuthorId->setModel('\Propel\Tests\Bookstore\Essay');
    }

    /**
     * Gets an array of ChildEssay objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildAuthor is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildEssay[] List of ChildEssay objects
     * @phpstan-return ObjectCollection&\Traversable<ChildEssay> List of ChildEssay objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getEssaysRelatedByFirstAuthorId(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collEssaysRelatedByFirstAuthorIdPartial && !$this->isNew();
        if (null === $this->collEssaysRelatedByFirstAuthorId || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collEssaysRelatedByFirstAuthorId) {
                    $this->initEssaysRelatedByFirstAuthorId();
                } else {
                    $collectionClassName = EssayTableMap::getTableMap()->getCollectionClassName();

                    $collEssaysRelatedByFirstAuthorId = new $collectionClassName;
                    $collEssaysRelatedByFirstAuthorId->setModel('\Propel\Tests\Bookstore\Essay');

                    return $collEssaysRelatedByFirstAuthorId;
                }
            } else {
                $collEssaysRelatedByFirstAuthorId = ChildEssayQuery::create(null, $criteria)
                    ->filterByFirstAuthor($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collEssaysRelatedByFirstAuthorIdPartial && count($collEssaysRelatedByFirstAuthorId)) {
                        $this->initEssaysRelatedByFirstAuthorId(false);

                        foreach ($collEssaysRelatedByFirstAuthorId as $obj) {
                            if (false === $this->collEssaysRelatedByFirstAuthorId->contains($obj)) {
                                $this->collEssaysRelatedByFirstAuthorId->append($obj);
                            }
                        }

                        $this->collEssaysRelatedByFirstAuthorIdPartial = true;
                    }

                    return $collEssaysRelatedByFirstAuthorId;
                }

                if ($partial && $this->collEssaysRelatedByFirstAuthorId) {
                    foreach ($this->collEssaysRelatedByFirstAuthorId as $obj) {
                        if ($obj->isNew()) {
                            $collEssaysRelatedByFirstAuthorId[] = $obj;
                        }
                    }
                }

                $this->collEssaysRelatedByFirstAuthorId = $collEssaysRelatedByFirstAuthorId;
                $this->collEssaysRelatedByFirstAuthorIdPartial = false;
            }
        }

        return $this->collEssaysRelatedByFirstAuthorId;
    }

    /**
     * Sets a collection of ChildEssay objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $essaysRelatedByFirstAuthorId A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setEssaysRelatedByFirstAuthorId(Collection $essaysRelatedByFirstAuthorId, ?ConnectionInterface $con = null)
    {
        /** @var ChildEssay[] $essaysRelatedByFirstAuthorIdToDelete */
        $essaysRelatedByFirstAuthorIdToDelete = $this->getEssaysRelatedByFirstAuthorId(new Criteria(), $con)->diff($essaysRelatedByFirstAuthorId);


        $this->essaysRelatedByFirstAuthorIdScheduledForDeletion = $essaysRelatedByFirstAuthorIdToDelete;

        foreach ($essaysRelatedByFirstAuthorIdToDelete as $essayRelatedByFirstAuthorIdRemoved) {
            $essayRelatedByFirstAuthorIdRemoved->setFirstAuthor(null);
        }

        $this->collEssaysRelatedByFirstAuthorId = null;
        foreach ($essaysRelatedByFirstAuthorId as $essayRelatedByFirstAuthorId) {
            $this->addEssayRelatedByFirstAuthorId($essayRelatedByFirstAuthorId);
        }

        $this->collEssaysRelatedByFirstAuthorId = $essaysRelatedByFirstAuthorId;
        $this->collEssaysRelatedByFirstAuthorIdPartial = false;

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
    public function countEssaysRelatedByFirstAuthorId(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collEssaysRelatedByFirstAuthorIdPartial && !$this->isNew();
        if (null === $this->collEssaysRelatedByFirstAuthorId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collEssaysRelatedByFirstAuthorId) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getEssaysRelatedByFirstAuthorId());
            }

            $query = ChildEssayQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByFirstAuthor($this)
                ->count($con);
        }

        return count($this->collEssaysRelatedByFirstAuthorId);
    }

    /**
     * Method called to associate a ChildEssay object to this object
     * through the ChildEssay foreign key attribute.
     *
     * @param ChildEssay $l ChildEssay
     * @return $this The current object (for fluent API support)
     */
    public function addEssayRelatedByFirstAuthorId(ChildEssay $l)
    {
        if ($this->collEssaysRelatedByFirstAuthorId === null) {
            $this->initEssaysRelatedByFirstAuthorId();
            $this->collEssaysRelatedByFirstAuthorIdPartial = true;
        }

        if (!$this->collEssaysRelatedByFirstAuthorId->contains($l)) {
            $this->doAddEssayRelatedByFirstAuthorId($l);

            if ($this->essaysRelatedByFirstAuthorIdScheduledForDeletion and $this->essaysRelatedByFirstAuthorIdScheduledForDeletion->contains($l)) {
                $this->essaysRelatedByFirstAuthorIdScheduledForDeletion->remove($this->essaysRelatedByFirstAuthorIdScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildEssay $essayRelatedByFirstAuthorId The ChildEssay object to add.
     */
    protected function doAddEssayRelatedByFirstAuthorId(ChildEssay $essayRelatedByFirstAuthorId): void
    {
        $this->collEssaysRelatedByFirstAuthorId[]= $essayRelatedByFirstAuthorId;
        $essayRelatedByFirstAuthorId->setFirstAuthor($this);
    }

    /**
     * @param ChildEssay $essayRelatedByFirstAuthorId The ChildEssay object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeEssayRelatedByFirstAuthorId(ChildEssay $essayRelatedByFirstAuthorId)
    {
        if ($this->getEssaysRelatedByFirstAuthorId()->contains($essayRelatedByFirstAuthorId)) {
            $pos = $this->collEssaysRelatedByFirstAuthorId->search($essayRelatedByFirstAuthorId);
            $this->collEssaysRelatedByFirstAuthorId->remove($pos);
            if (null === $this->essaysRelatedByFirstAuthorIdScheduledForDeletion) {
                $this->essaysRelatedByFirstAuthorIdScheduledForDeletion = clone $this->collEssaysRelatedByFirstAuthorId;
                $this->essaysRelatedByFirstAuthorIdScheduledForDeletion->clear();
            }
            $this->essaysRelatedByFirstAuthorIdScheduledForDeletion[]= $essayRelatedByFirstAuthorId;
            $essayRelatedByFirstAuthorId->setFirstAuthor(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Author is new, it will return
     * an empty collection; or if this Author has previously
     * been saved, it will retrieve related EssaysRelatedByFirstAuthorId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Author.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildEssay[] List of ChildEssay objects
     * @phpstan-return ObjectCollection&\Traversable<ChildEssay> List of ChildEssay objects
     */
    public function getEssaysRelatedByFirstAuthorIdJoinEssayRelatedByNextEssayId(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildEssayQuery::create(null, $criteria);
        $query->joinWith('EssayRelatedByNextEssayId', $joinBehavior);

        return $this->getEssaysRelatedByFirstAuthorId($query, $con);
    }

    /**
     * Clears out the collEssaysRelatedBySecondAuthorId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addEssaysRelatedBySecondAuthorId()
     */
    public function clearEssaysRelatedBySecondAuthorId()
    {
        $this->collEssaysRelatedBySecondAuthorId = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collEssaysRelatedBySecondAuthorId collection loaded partially.
     *
     * @return void
     */
    public function resetPartialEssaysRelatedBySecondAuthorId($v = true): void
    {
        $this->collEssaysRelatedBySecondAuthorIdPartial = $v;
    }

    /**
     * Initializes the collEssaysRelatedBySecondAuthorId collection.
     *
     * By default this just sets the collEssaysRelatedBySecondAuthorId collection to an empty array (like clearcollEssaysRelatedBySecondAuthorId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initEssaysRelatedBySecondAuthorId(bool $overrideExisting = true): void
    {
        if (null !== $this->collEssaysRelatedBySecondAuthorId && !$overrideExisting) {
            return;
        }

        $collectionClassName = EssayTableMap::getTableMap()->getCollectionClassName();

        $this->collEssaysRelatedBySecondAuthorId = new $collectionClassName;
        $this->collEssaysRelatedBySecondAuthorId->setModel('\Propel\Tests\Bookstore\Essay');
    }

    /**
     * Gets an array of ChildEssay objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildAuthor is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildEssay[] List of ChildEssay objects
     * @phpstan-return ObjectCollection&\Traversable<ChildEssay> List of ChildEssay objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getEssaysRelatedBySecondAuthorId(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collEssaysRelatedBySecondAuthorIdPartial && !$this->isNew();
        if (null === $this->collEssaysRelatedBySecondAuthorId || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collEssaysRelatedBySecondAuthorId) {
                    $this->initEssaysRelatedBySecondAuthorId();
                } else {
                    $collectionClassName = EssayTableMap::getTableMap()->getCollectionClassName();

                    $collEssaysRelatedBySecondAuthorId = new $collectionClassName;
                    $collEssaysRelatedBySecondAuthorId->setModel('\Propel\Tests\Bookstore\Essay');

                    return $collEssaysRelatedBySecondAuthorId;
                }
            } else {
                $collEssaysRelatedBySecondAuthorId = ChildEssayQuery::create(null, $criteria)
                    ->filterBySecondAuthor($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collEssaysRelatedBySecondAuthorIdPartial && count($collEssaysRelatedBySecondAuthorId)) {
                        $this->initEssaysRelatedBySecondAuthorId(false);

                        foreach ($collEssaysRelatedBySecondAuthorId as $obj) {
                            if (false === $this->collEssaysRelatedBySecondAuthorId->contains($obj)) {
                                $this->collEssaysRelatedBySecondAuthorId->append($obj);
                            }
                        }

                        $this->collEssaysRelatedBySecondAuthorIdPartial = true;
                    }

                    return $collEssaysRelatedBySecondAuthorId;
                }

                if ($partial && $this->collEssaysRelatedBySecondAuthorId) {
                    foreach ($this->collEssaysRelatedBySecondAuthorId as $obj) {
                        if ($obj->isNew()) {
                            $collEssaysRelatedBySecondAuthorId[] = $obj;
                        }
                    }
                }

                $this->collEssaysRelatedBySecondAuthorId = $collEssaysRelatedBySecondAuthorId;
                $this->collEssaysRelatedBySecondAuthorIdPartial = false;
            }
        }

        return $this->collEssaysRelatedBySecondAuthorId;
    }

    /**
     * Sets a collection of ChildEssay objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $essaysRelatedBySecondAuthorId A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setEssaysRelatedBySecondAuthorId(Collection $essaysRelatedBySecondAuthorId, ?ConnectionInterface $con = null)
    {
        /** @var ChildEssay[] $essaysRelatedBySecondAuthorIdToDelete */
        $essaysRelatedBySecondAuthorIdToDelete = $this->getEssaysRelatedBySecondAuthorId(new Criteria(), $con)->diff($essaysRelatedBySecondAuthorId);


        $this->essaysRelatedBySecondAuthorIdScheduledForDeletion = $essaysRelatedBySecondAuthorIdToDelete;

        foreach ($essaysRelatedBySecondAuthorIdToDelete as $essayRelatedBySecondAuthorIdRemoved) {
            $essayRelatedBySecondAuthorIdRemoved->setSecondAuthor(null);
        }

        $this->collEssaysRelatedBySecondAuthorId = null;
        foreach ($essaysRelatedBySecondAuthorId as $essayRelatedBySecondAuthorId) {
            $this->addEssayRelatedBySecondAuthorId($essayRelatedBySecondAuthorId);
        }

        $this->collEssaysRelatedBySecondAuthorId = $essaysRelatedBySecondAuthorId;
        $this->collEssaysRelatedBySecondAuthorIdPartial = false;

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
    public function countEssaysRelatedBySecondAuthorId(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collEssaysRelatedBySecondAuthorIdPartial && !$this->isNew();
        if (null === $this->collEssaysRelatedBySecondAuthorId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collEssaysRelatedBySecondAuthorId) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getEssaysRelatedBySecondAuthorId());
            }

            $query = ChildEssayQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterBySecondAuthor($this)
                ->count($con);
        }

        return count($this->collEssaysRelatedBySecondAuthorId);
    }

    /**
     * Method called to associate a ChildEssay object to this object
     * through the ChildEssay foreign key attribute.
     *
     * @param ChildEssay $l ChildEssay
     * @return $this The current object (for fluent API support)
     */
    public function addEssayRelatedBySecondAuthorId(ChildEssay $l)
    {
        if ($this->collEssaysRelatedBySecondAuthorId === null) {
            $this->initEssaysRelatedBySecondAuthorId();
            $this->collEssaysRelatedBySecondAuthorIdPartial = true;
        }

        if (!$this->collEssaysRelatedBySecondAuthorId->contains($l)) {
            $this->doAddEssayRelatedBySecondAuthorId($l);

            if ($this->essaysRelatedBySecondAuthorIdScheduledForDeletion and $this->essaysRelatedBySecondAuthorIdScheduledForDeletion->contains($l)) {
                $this->essaysRelatedBySecondAuthorIdScheduledForDeletion->remove($this->essaysRelatedBySecondAuthorIdScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildEssay $essayRelatedBySecondAuthorId The ChildEssay object to add.
     */
    protected function doAddEssayRelatedBySecondAuthorId(ChildEssay $essayRelatedBySecondAuthorId): void
    {
        $this->collEssaysRelatedBySecondAuthorId[]= $essayRelatedBySecondAuthorId;
        $essayRelatedBySecondAuthorId->setSecondAuthor($this);
    }

    /**
     * @param ChildEssay $essayRelatedBySecondAuthorId The ChildEssay object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeEssayRelatedBySecondAuthorId(ChildEssay $essayRelatedBySecondAuthorId)
    {
        if ($this->getEssaysRelatedBySecondAuthorId()->contains($essayRelatedBySecondAuthorId)) {
            $pos = $this->collEssaysRelatedBySecondAuthorId->search($essayRelatedBySecondAuthorId);
            $this->collEssaysRelatedBySecondAuthorId->remove($pos);
            if (null === $this->essaysRelatedBySecondAuthorIdScheduledForDeletion) {
                $this->essaysRelatedBySecondAuthorIdScheduledForDeletion = clone $this->collEssaysRelatedBySecondAuthorId;
                $this->essaysRelatedBySecondAuthorIdScheduledForDeletion->clear();
            }
            $this->essaysRelatedBySecondAuthorIdScheduledForDeletion[]= $essayRelatedBySecondAuthorId;
            $essayRelatedBySecondAuthorId->setSecondAuthor(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Author is new, it will return
     * an empty collection; or if this Author has previously
     * been saved, it will retrieve related EssaysRelatedBySecondAuthorId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Author.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildEssay[] List of ChildEssay objects
     * @phpstan-return ObjectCollection&\Traversable<ChildEssay> List of ChildEssay objects
     */
    public function getEssaysRelatedBySecondAuthorIdJoinEssayRelatedByNextEssayId(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildEssayQuery::create(null, $criteria);
        $query->joinWith('EssayRelatedByNextEssayId', $joinBehavior);

        return $this->getEssaysRelatedBySecondAuthorId($query, $con);
    }

    /**
     * Clears out the collPolymorphicRelationLogs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addPolymorphicRelationLogs()
     */
    public function clearPolymorphicRelationLogs()
    {
        $this->collPolymorphicRelationLogs = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collPolymorphicRelationLogs collection loaded partially.
     *
     * @return void
     */
    public function resetPartialPolymorphicRelationLogs($v = true): void
    {
        $this->collPolymorphicRelationLogsPartial = $v;
    }

    /**
     * Initializes the collPolymorphicRelationLogs collection.
     *
     * By default this just sets the collPolymorphicRelationLogs collection to an empty array (like clearcollPolymorphicRelationLogs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPolymorphicRelationLogs(bool $overrideExisting = true): void
    {
        if (null !== $this->collPolymorphicRelationLogs && !$overrideExisting) {
            return;
        }

        $collectionClassName = PolymorphicRelationLogTableMap::getTableMap()->getCollectionClassName();

        $this->collPolymorphicRelationLogs = new $collectionClassName;
        $this->collPolymorphicRelationLogs->setModel('\Propel\Tests\Bookstore\PolymorphicRelationLog');
    }

    /**
     * Gets an array of ChildPolymorphicRelationLog objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildAuthor is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildPolymorphicRelationLog[] List of ChildPolymorphicRelationLog objects
     * @phpstan-return ObjectCollection&\Traversable<ChildPolymorphicRelationLog> List of ChildPolymorphicRelationLog objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getPolymorphicRelationLogs(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collPolymorphicRelationLogsPartial && !$this->isNew();
        if (null === $this->collPolymorphicRelationLogs || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collPolymorphicRelationLogs) {
                    $this->initPolymorphicRelationLogs();
                } else {
                    $collectionClassName = PolymorphicRelationLogTableMap::getTableMap()->getCollectionClassName();

                    $collPolymorphicRelationLogs = new $collectionClassName;
                    $collPolymorphicRelationLogs->setModel('\Propel\Tests\Bookstore\PolymorphicRelationLog');

                    return $collPolymorphicRelationLogs;
                }
            } else {
                $collPolymorphicRelationLogs = ChildPolymorphicRelationLogQuery::create(null, $criteria)
                    ->filterByAuthor($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collPolymorphicRelationLogsPartial && count($collPolymorphicRelationLogs)) {
                        $this->initPolymorphicRelationLogs(false);

                        foreach ($collPolymorphicRelationLogs as $obj) {
                            if (false === $this->collPolymorphicRelationLogs->contains($obj)) {
                                $this->collPolymorphicRelationLogs->append($obj);
                            }
                        }

                        $this->collPolymorphicRelationLogsPartial = true;
                    }

                    return $collPolymorphicRelationLogs;
                }

                if ($partial && $this->collPolymorphicRelationLogs) {
                    foreach ($this->collPolymorphicRelationLogs as $obj) {
                        if ($obj->isNew()) {
                            $collPolymorphicRelationLogs[] = $obj;
                        }
                    }
                }

                $this->collPolymorphicRelationLogs = $collPolymorphicRelationLogs;
                $this->collPolymorphicRelationLogsPartial = false;
            }
        }

        return $this->collPolymorphicRelationLogs;
    }

    /**
     * Sets a collection of ChildPolymorphicRelationLog objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $polymorphicRelationLogs A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setPolymorphicRelationLogs(Collection $polymorphicRelationLogs, ?ConnectionInterface $con = null)
    {
        /** @var ChildPolymorphicRelationLog[] $polymorphicRelationLogsToDelete */
        $polymorphicRelationLogsToDelete = $this->getPolymorphicRelationLogs(new Criteria(), $con)->diff($polymorphicRelationLogs);


        $this->polymorphicRelationLogsScheduledForDeletion = $polymorphicRelationLogsToDelete;

        foreach ($polymorphicRelationLogsToDelete as $polymorphicRelationLogRemoved) {
            $polymorphicRelationLogRemoved->setAuthor(null);
        }

        $this->collPolymorphicRelationLogs = null;
        foreach ($polymorphicRelationLogs as $polymorphicRelationLog) {
            $this->addPolymorphicRelationLog($polymorphicRelationLog);
        }

        $this->collPolymorphicRelationLogs = $polymorphicRelationLogs;
        $this->collPolymorphicRelationLogsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related PolymorphicRelationLog objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related PolymorphicRelationLog objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countPolymorphicRelationLogs(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collPolymorphicRelationLogsPartial && !$this->isNew();
        if (null === $this->collPolymorphicRelationLogs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPolymorphicRelationLogs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getPolymorphicRelationLogs());
            }

            $query = ChildPolymorphicRelationLogQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthor($this)
                ->count($con);
        }

        return count($this->collPolymorphicRelationLogs);
    }

    /**
     * Method called to associate a ChildPolymorphicRelationLog object to this object
     * through the ChildPolymorphicRelationLog foreign key attribute.
     *
     * @param ChildPolymorphicRelationLog $l ChildPolymorphicRelationLog
     * @return $this The current object (for fluent API support)
     */
    public function addPolymorphicRelationLog(ChildPolymorphicRelationLog $l)
    {
        if ($this->collPolymorphicRelationLogs === null) {
            $this->initPolymorphicRelationLogs();
            $this->collPolymorphicRelationLogsPartial = true;
        }

        if (!$this->collPolymorphicRelationLogs->contains($l)) {
            $this->doAddPolymorphicRelationLog($l);

            if ($this->polymorphicRelationLogsScheduledForDeletion and $this->polymorphicRelationLogsScheduledForDeletion->contains($l)) {
                $this->polymorphicRelationLogsScheduledForDeletion->remove($this->polymorphicRelationLogsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildPolymorphicRelationLog $polymorphicRelationLog The ChildPolymorphicRelationLog object to add.
     */
    protected function doAddPolymorphicRelationLog(ChildPolymorphicRelationLog $polymorphicRelationLog): void
    {
        $this->collPolymorphicRelationLogs[]= $polymorphicRelationLog;
        $polymorphicRelationLog->setAuthor($this);
    }

    /**
     * @param ChildPolymorphicRelationLog $polymorphicRelationLog The ChildPolymorphicRelationLog object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removePolymorphicRelationLog(ChildPolymorphicRelationLog $polymorphicRelationLog)
    {
        if ($this->getPolymorphicRelationLogs()->contains($polymorphicRelationLog)) {
            $pos = $this->collPolymorphicRelationLogs->search($polymorphicRelationLog);
            $this->collPolymorphicRelationLogs->remove($pos);
            if (null === $this->polymorphicRelationLogsScheduledForDeletion) {
                $this->polymorphicRelationLogsScheduledForDeletion = clone $this->collPolymorphicRelationLogs;
                $this->polymorphicRelationLogsScheduledForDeletion->clear();
            }
            $this->polymorphicRelationLogsScheduledForDeletion[]= clone $polymorphicRelationLog;
            $polymorphicRelationLog->setAuthor(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Author is new, it will return
     * an empty collection; or if this Author has previously
     * been saved, it will retrieve related PolymorphicRelationLogs from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Author.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildPolymorphicRelationLog[] List of ChildPolymorphicRelationLog objects
     * @phpstan-return ObjectCollection&\Traversable<ChildPolymorphicRelationLog> List of ChildPolymorphicRelationLog objects
     */
    public function getPolymorphicRelationLogsJoinBook(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildPolymorphicRelationLogQuery::create(null, $criteria);
        $query->joinWith('Book', $joinBehavior);

        return $this->getPolymorphicRelationLogs($query, $con);
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
        $this->first_name = null;
        $this->last_name = null;
        $this->email = null;
        $this->age = null;
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
            if ($this->collBooks) {
                foreach ($this->collBooks as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collEssaysRelatedByFirstAuthorId) {
                foreach ($this->collEssaysRelatedByFirstAuthorId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collEssaysRelatedBySecondAuthorId) {
                foreach ($this->collEssaysRelatedBySecondAuthorId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPolymorphicRelationLogs) {
                foreach ($this->collPolymorphicRelationLogs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collBooks = null;
        $this->collEssaysRelatedByFirstAuthorId = null;
        $this->collEssaysRelatedBySecondAuthorId = null;
        $this->collPolymorphicRelationLogs = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(AuthorTableMap::DEFAULT_STRING_FORMAT);
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
