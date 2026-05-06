<?php

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
use Propel\Runtime\Validator\Constraints\Date;
use Propel\Tests\Bookstore\Behavior\ValidateBook as ChildValidateBook;
use Propel\Tests\Bookstore\Behavior\ValidateBookQuery as ChildValidateBookQuery;
use Propel\Tests\Bookstore\Behavior\ValidateReader as ChildValidateReader;
use Propel\Tests\Bookstore\Behavior\ValidateReaderBook as ChildValidateReaderBook;
use Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery as ChildValidateReaderBookQuery;
use Propel\Tests\Bookstore\Behavior\ValidateReaderQuery as ChildValidateReaderQuery;
use Propel\Tests\Bookstore\Behavior\Map\ValidateReaderBookTableMap;
use Propel\Tests\Bookstore\Behavior\Map\ValidateReaderTableMap;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Base class that represents a row from the 'validate_reader' table.
 *
 * Reader Table
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Behavior.Base
 */
abstract class ValidateReader implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Behavior\\Map\\ValidateReaderTableMap';


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
     * The value for the birthday field.
     * The authors birthday
     * @var        DateTime|null
     */
    protected $birthday;

    /**
     * @var        ObjectCollection|ChildValidateReaderBook[] Collection to store aggregation of ChildValidateReaderBook objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildValidateReaderBook> Collection to store aggregation of ChildValidateReaderBook objects.
     */
    protected $collValidateReaderBooks;
    protected $collValidateReaderBooksPartial;

    /**
     * @var        ObjectCollection|ChildValidateBook[] Cross Collection to store aggregation of ChildValidateBook objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildValidateBook> Cross Collection to store aggregation of ChildValidateBook objects.
     */
    protected $collValidateBooks;

    /**
     * @var bool
     */
    protected $collValidateBooksPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    // validate behavior

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * ConstraintViolationList object
     *
     * @see     http://api.symfony.com/2.0/Symfony/Component/Validator/ConstraintViolationList.html
     * @var     ConstraintViolationList
     */
    protected $validationFailures;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildValidateBook[]
     * @phpstan-var ObjectCollection&\Traversable<ChildValidateBook>
     */
    protected $validateBooksScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildValidateReaderBook[]
     * @phpstan-var ObjectCollection&\Traversable<ChildValidateReaderBook>
     */
    protected $validateReaderBooksScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Behavior\Base\ValidateReader object.
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
     * Compares this with another <code>ValidateReader</code> instance.  If
     * <code>obj</code> is an instance of <code>ValidateReader</code>, delegates to
     * <code>equals(ValidateReader)</code>.  Otherwise, returns <code>false</code>.
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
     * Get the [optionally formatted] temporal [birthday] column value.
     * The authors birthday
     *
     * @param string|null $format The date/time format string (either date()-style or strftime()-style).
     *   If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime|null Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00.
     *
     * @throws \Propel\Runtime\Exception\PropelException - if unable to parse/validate the date/time value.
     *
     * @psalm-return ($format is null ? DateTime|null : string|null)
     */
    public function getBirthday(?string $format = null)
    {
        if ($format === null) {
            return $this->birthday;
        } else {
            return $this->birthday instanceof \DateTimeInterface ? $this->birthday->format($format) : null;
        }
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
            $this->modifiedColumns[ValidateReaderTableMap::COL_ID] = true;
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
            $this->modifiedColumns[ValidateReaderTableMap::COL_FIRST_NAME] = true;
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
            $this->modifiedColumns[ValidateReaderTableMap::COL_LAST_NAME] = true;
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
            $this->modifiedColumns[ValidateReaderTableMap::COL_EMAIL] = true;
        }

        return $this;
    }

    /**
     * Sets the value of [birthday] column to a normalized version of the date/time value specified.
     * The authors birthday
     * @param string|integer|\DateTimeInterface|null $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this The current object (for fluent API support)
     */
    public function setBirthday($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->birthday !== null || $dt !== null) {
            if ($this->birthday === null || $dt === null || $dt->format("Y-m-d") !== $this->birthday->format("Y-m-d")) {
                $this->birthday = $dt === null ? null : clone $dt;
                $this->modifiedColumns[ValidateReaderTableMap::COL_BIRTHDAY] = true;
            }
        } // if either are not null

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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ValidateReaderTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ValidateReaderTableMap::translateFieldName('FirstName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->first_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ValidateReaderTableMap::translateFieldName('LastName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->last_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ValidateReaderTableMap::translateFieldName('Email', TableMap::TYPE_PHPNAME, $indexType)];
            $this->email = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ValidateReaderTableMap::translateFieldName('Birthday', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00') {
                $col = null;
            }
            $this->birthday = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 5; // 5 = ValidateReaderTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateReader'), 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(ValidateReaderTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildValidateReaderQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collValidateReaderBooks = null;

            $this->collValidateBooks = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see ValidateReader::setDeleted()
     * @see ValidateReader::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateReaderTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildValidateReaderQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateReaderTableMap::DATABASE_NAME);
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
                ValidateReaderTableMap::addInstanceToPool($this);
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

            if ($this->validateBooksScheduledForDeletion !== null) {
                if (!$this->validateBooksScheduledForDeletion->isEmpty()) {
                    $pks = [];
                    foreach ($this->validateBooksScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[0] = $this->getId();
                        $entryPk[1] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->validateBooksScheduledForDeletion = null;
                }

            }

            if ($this->collValidateBooks) {
                foreach ($this->collValidateBooks as $validateBook) {
                    if (!$validateBook->isDeleted() && ($validateBook->isNew() || $validateBook->isModified())) {
                        $validateBook->save($con);
                    }
                }
            }


            if ($this->validateReaderBooksScheduledForDeletion !== null) {
                if (!$this->validateReaderBooksScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\Behavior\ValidateReaderBookQuery::create()
                        ->filterByPrimaryKeys($this->validateReaderBooksScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->validateReaderBooksScheduledForDeletion = null;
                }
            }

            if ($this->collValidateReaderBooks !== null) {
                foreach ($this->collValidateReaderBooks as $referrerFK) {
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

        $this->modifiedColumns[ValidateReaderTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ValidateReaderTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ValidateReaderTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_FIRST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'first_name';
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_LAST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'last_name';
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_EMAIL)) {
            $modifiedColumns[':p' . $index++]  = 'email';
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_BIRTHDAY)) {
            $modifiedColumns[':p' . $index++]  = 'birthday';
        }

        $sql = sprintf(
            'INSERT INTO validate_reader (%s) VALUES (%s)',
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
                    case 'birthday':
                        $stmt->bindValue($identifier, $this->birthday ? $this->birthday->format("Y-m-d") : null, PDO::PARAM_STR);

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
        $pos = ValidateReaderTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getBirthday();

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
        if (isset($alreadyDumpedObjects['ValidateReader'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['ValidateReader'][$this->hashCode()] = true;
        $keys = ValidateReaderTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getFirstName(),
            $keys[2] => $this->getLastName(),
            $keys[3] => $this->getEmail(),
            $keys[4] => $this->getBirthday(),
        ];
        if ($result[$keys[4]] instanceof \DateTimeInterface) {
            $result[$keys[4]] = $result[$keys[4]]->format('Y-m-d');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collValidateReaderBooks) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'validateReaderBooks';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'validate_reader_books';
                        break;
                    default:
                        $key = 'ValidateReaderBooks';
                }

                $result[$key] = $this->collValidateReaderBooks->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = ValidateReaderTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setBirthday($value);
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
        $keys = ValidateReaderTableMap::getFieldNames($keyType);

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
            $this->setBirthday($arr[$keys[4]]);
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
        $criteria = new Criteria(ValidateReaderTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ValidateReaderTableMap::COL_ID)) {
            $criteria->add(ValidateReaderTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_FIRST_NAME)) {
            $criteria->add(ValidateReaderTableMap::COL_FIRST_NAME, $this->first_name);
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_LAST_NAME)) {
            $criteria->add(ValidateReaderTableMap::COL_LAST_NAME, $this->last_name);
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_EMAIL)) {
            $criteria->add(ValidateReaderTableMap::COL_EMAIL, $this->email);
        }
        if ($this->isColumnModified(ValidateReaderTableMap::COL_BIRTHDAY)) {
            $criteria->add(ValidateReaderTableMap::COL_BIRTHDAY, $this->birthday);
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
        $criteria = ChildValidateReaderQuery::create();
        $criteria->add(ValidateReaderTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Behavior\ValidateReader (or compatible) type.
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
        $copyObj->setBirthday($this->getBirthday());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getValidateReaderBooks() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addValidateReaderBook($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\Behavior\ValidateReader Clone of current object.
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
        if ('ValidateReaderBook' === $relationName) {
            $this->initValidateReaderBooks();
            return;
        }
    }

    /**
     * Clears out the collValidateReaderBooks collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addValidateReaderBooks()
     */
    public function clearValidateReaderBooks()
    {
        $this->collValidateReaderBooks = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collValidateReaderBooks collection loaded partially.
     *
     * @return void
     */
    public function resetPartialValidateReaderBooks($v = true): void
    {
        $this->collValidateReaderBooksPartial = $v;
    }

    /**
     * Initializes the collValidateReaderBooks collection.
     *
     * By default this just sets the collValidateReaderBooks collection to an empty array (like clearcollValidateReaderBooks());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initValidateReaderBooks(bool $overrideExisting = true): void
    {
        if (null !== $this->collValidateReaderBooks && !$overrideExisting) {
            return;
        }

        $collectionClassName = ValidateReaderBookTableMap::getTableMap()->getCollectionClassName();

        $this->collValidateReaderBooks = new $collectionClassName;
        $this->collValidateReaderBooks->setModel('\Propel\Tests\Bookstore\Behavior\ValidateReaderBook');
    }

    /**
     * Gets an array of ChildValidateReaderBook objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildValidateReader is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildValidateReaderBook[] List of ChildValidateReaderBook objects
     * @phpstan-return ObjectCollection&\Traversable<ChildValidateReaderBook> List of ChildValidateReaderBook objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getValidateReaderBooks(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collValidateReaderBooksPartial && !$this->isNew();
        if (null === $this->collValidateReaderBooks || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collValidateReaderBooks) {
                    $this->initValidateReaderBooks();
                } else {
                    $collectionClassName = ValidateReaderBookTableMap::getTableMap()->getCollectionClassName();

                    $collValidateReaderBooks = new $collectionClassName;
                    $collValidateReaderBooks->setModel('\Propel\Tests\Bookstore\Behavior\ValidateReaderBook');

                    return $collValidateReaderBooks;
                }
            } else {
                $collValidateReaderBooks = ChildValidateReaderBookQuery::create(null, $criteria)
                    ->filterByValidateReader($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collValidateReaderBooksPartial && count($collValidateReaderBooks)) {
                        $this->initValidateReaderBooks(false);

                        foreach ($collValidateReaderBooks as $obj) {
                            if (false === $this->collValidateReaderBooks->contains($obj)) {
                                $this->collValidateReaderBooks->append($obj);
                            }
                        }

                        $this->collValidateReaderBooksPartial = true;
                    }

                    return $collValidateReaderBooks;
                }

                if ($partial && $this->collValidateReaderBooks) {
                    foreach ($this->collValidateReaderBooks as $obj) {
                        if ($obj->isNew()) {
                            $collValidateReaderBooks[] = $obj;
                        }
                    }
                }

                $this->collValidateReaderBooks = $collValidateReaderBooks;
                $this->collValidateReaderBooksPartial = false;
            }
        }

        return $this->collValidateReaderBooks;
    }

    /**
     * Sets a collection of ChildValidateReaderBook objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $validateReaderBooks A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setValidateReaderBooks(Collection $validateReaderBooks, ?ConnectionInterface $con = null)
    {
        /** @var ChildValidateReaderBook[] $validateReaderBooksToDelete */
        $validateReaderBooksToDelete = $this->getValidateReaderBooks(new Criteria(), $con)->diff($validateReaderBooks);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->validateReaderBooksScheduledForDeletion = clone $validateReaderBooksToDelete;

        foreach ($validateReaderBooksToDelete as $validateReaderBookRemoved) {
            $validateReaderBookRemoved->setValidateReader(null);
        }

        $this->collValidateReaderBooks = null;
        foreach ($validateReaderBooks as $validateReaderBook) {
            $this->addValidateReaderBook($validateReaderBook);
        }

        $this->collValidateReaderBooks = $validateReaderBooks;
        $this->collValidateReaderBooksPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ValidateReaderBook objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ValidateReaderBook objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countValidateReaderBooks(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collValidateReaderBooksPartial && !$this->isNew();
        if (null === $this->collValidateReaderBooks || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collValidateReaderBooks) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getValidateReaderBooks());
            }

            $query = ChildValidateReaderBookQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByValidateReader($this)
                ->count($con);
        }

        return count($this->collValidateReaderBooks);
    }

    /**
     * Method called to associate a ChildValidateReaderBook object to this object
     * through the ChildValidateReaderBook foreign key attribute.
     *
     * @param ChildValidateReaderBook $l ChildValidateReaderBook
     * @return $this The current object (for fluent API support)
     */
    public function addValidateReaderBook(ChildValidateReaderBook $l)
    {
        if ($this->collValidateReaderBooks === null) {
            $this->initValidateReaderBooks();
            $this->collValidateReaderBooksPartial = true;
        }

        if (!$this->collValidateReaderBooks->contains($l)) {
            $this->doAddValidateReaderBook($l);

            if ($this->validateReaderBooksScheduledForDeletion and $this->validateReaderBooksScheduledForDeletion->contains($l)) {
                $this->validateReaderBooksScheduledForDeletion->remove($this->validateReaderBooksScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildValidateReaderBook $validateReaderBook The ChildValidateReaderBook object to add.
     */
    protected function doAddValidateReaderBook(ChildValidateReaderBook $validateReaderBook): void
    {
        $this->collValidateReaderBooks[]= $validateReaderBook;
        $validateReaderBook->setValidateReader($this);
    }

    /**
     * @param ChildValidateReaderBook $validateReaderBook The ChildValidateReaderBook object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeValidateReaderBook(ChildValidateReaderBook $validateReaderBook)
    {
        if ($this->getValidateReaderBooks()->contains($validateReaderBook)) {
            $pos = $this->collValidateReaderBooks->search($validateReaderBook);
            $this->collValidateReaderBooks->remove($pos);
            if (null === $this->validateReaderBooksScheduledForDeletion) {
                $this->validateReaderBooksScheduledForDeletion = clone $this->collValidateReaderBooks;
                $this->validateReaderBooksScheduledForDeletion->clear();
            }
            $this->validateReaderBooksScheduledForDeletion[]= clone $validateReaderBook;
            $validateReaderBook->setValidateReader(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ValidateReader is new, it will return
     * an empty collection; or if this ValidateReader has previously
     * been saved, it will retrieve related ValidateReaderBooks from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ValidateReader.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildValidateReaderBook[] List of ChildValidateReaderBook objects
     * @phpstan-return ObjectCollection&\Traversable<ChildValidateReaderBook> List of ChildValidateReaderBook objects
     */
    public function getValidateReaderBooksJoinValidateBook(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildValidateReaderBookQuery::create(null, $criteria);
        $query->joinWith('ValidateBook', $joinBehavior);

        return $this->getValidateReaderBooks($query, $con);
    }

    /**
     * Clears out the collValidateBooks collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addValidateBooks()
     */
    public function clearValidateBooks()
    {
        $this->collValidateBooks = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collValidateBooks crossRef collection.
     *
     * By default this just sets the collValidateBooks collection to an empty collection (like clearValidateBooks());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initValidateBooks()
    {
        $collectionClassName = ValidateReaderBookTableMap::getTableMap()->getCollectionClassName();

        $this->collValidateBooks = new $collectionClassName;
        $this->collValidateBooksPartial = true;
        $this->collValidateBooks->setModel('\Propel\Tests\Bookstore\Behavior\ValidateBook');
    }

    /**
     * Checks if the collValidateBooks collection is loaded.
     *
     * @return bool
     */
    public function isValidateBooksLoaded(): bool
    {
        return null !== $this->collValidateBooks;
    }

    /**
     * Gets a collection of ChildValidateBook objects related by a many-to-many relationship
     * to the current object by way of the validate_reader_book cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildValidateReader is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria Optional query object to filter the query
     * @param ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildValidateBook[] List of ChildValidateBook objects
     * @phpstan-return ObjectCollection&\Traversable<ChildValidateBook> List of ChildValidateBook objects
     */
    public function getValidateBooks(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collValidateBooksPartial && !$this->isNew();
        if (null === $this->collValidateBooks || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collValidateBooks) {
                    $this->initValidateBooks();
                }
            } else {

                $query = ChildValidateBookQuery::create(null, $criteria)
                    ->filterByValidateReader($this);
                $collValidateBooks = $query->find($con);
                if (null !== $criteria) {
                    return $collValidateBooks;
                }

                if ($partial && $this->collValidateBooks) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collValidateBooks as $obj) {
                        if (!$collValidateBooks->contains($obj)) {
                            $collValidateBooks[] = $obj;
                        }
                    }
                }

                $this->collValidateBooks = $collValidateBooks;
                $this->collValidateBooksPartial = false;
            }
        }

        return $this->collValidateBooks;
    }

    /**
     * Sets a collection of ValidateBook objects related by a many-to-many relationship
     * to the current object by way of the validate_reader_book cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $validateBooks A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setValidateBooks(Collection $validateBooks, ?ConnectionInterface $con = null)
    {
        $this->clearValidateBooks();
        $currentValidateBooks = $this->getValidateBooks();

        $validateBooksScheduledForDeletion = $currentValidateBooks->diff($validateBooks);

        foreach ($validateBooksScheduledForDeletion as $toDelete) {
            $this->removeValidateBook($toDelete);
        }

        foreach ($validateBooks as $validateBook) {
            if (!$currentValidateBooks->contains($validateBook)) {
                $this->doAddValidateBook($validateBook);
            }
        }

        $this->collValidateBooksPartial = false;
        $this->collValidateBooks = $validateBooks;

        return $this;
    }

    /**
     * Gets the number of ValidateBook objects related by a many-to-many relationship
     * to the current object by way of the validate_reader_book cross-reference table.
     *
     * @param Criteria $criteria Optional query object to filter the query
     * @param bool $distinct Set to true to force count distinct
     * @param ConnectionInterface $con Optional connection object
     *
     * @return int The number of related ValidateBook objects
     */
    public function countValidateBooks(?Criteria $criteria = null, $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collValidateBooksPartial && !$this->isNew();
        if (null === $this->collValidateBooks || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collValidateBooks) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getValidateBooks());
                }

                $query = ChildValidateBookQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByValidateReader($this)
                    ->count($con);
            }
        } else {
            return count($this->collValidateBooks);
        }
    }

    /**
     * Associate a ChildValidateBook to this object
     * through the validate_reader_book cross reference table.
     *
     * @param ChildValidateBook $validateBook
     * @return ChildValidateReader The current object (for fluent API support)
     */
    public function addValidateBook(ChildValidateBook $validateBook)
    {
        if ($this->collValidateBooks === null) {
            $this->initValidateBooks();
        }

        if (!$this->getValidateBooks()->contains($validateBook)) {
            // only add it if the **same** object is not already associated
            $this->collValidateBooks->push($validateBook);
            $this->doAddValidateBook($validateBook);
        }

        return $this;
    }

    /**
     *
     * @param ChildValidateBook $validateBook
     */
    protected function doAddValidateBook(ChildValidateBook $validateBook)
    {
        $validateReaderBook = new ChildValidateReaderBook();

        $validateReaderBook->setValidateBook($validateBook);

        $validateReaderBook->setValidateReader($this);

        $this->addValidateReaderBook($validateReaderBook);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$validateBook->isValidateReadersLoaded()) {
            $validateBook->initValidateReaders();
            $validateBook->getValidateReaders()->push($this);
        } elseif (!$validateBook->getValidateReaders()->contains($this)) {
            $validateBook->getValidateReaders()->push($this);
        }

    }

    /**
     * Remove validateBook of this object
     * through the validate_reader_book cross reference table.
     *
     * @param ChildValidateBook $validateBook
     * @return ChildValidateReader The current object (for fluent API support)
     */
    public function removeValidateBook(ChildValidateBook $validateBook)
    {
        if ($this->getValidateBooks()->contains($validateBook)) {
            $validateReaderBook = new ChildValidateReaderBook();
            $validateReaderBook->setValidateBook($validateBook);
            if ($validateBook->isValidateReadersLoaded()) {
                //remove the back reference if available
                $validateBook->getValidateReaders()->removeObject($this);
            }

            $validateReaderBook->setValidateReader($this);
            $this->removeValidateReaderBook(clone $validateReaderBook);
            $validateReaderBook->clear();

            $this->collValidateBooks->remove($this->collValidateBooks->search($validateBook));

            if (null === $this->validateBooksScheduledForDeletion) {
                $this->validateBooksScheduledForDeletion = clone $this->collValidateBooks;
                $this->validateBooksScheduledForDeletion->clear();
            }

            $this->validateBooksScheduledForDeletion->push($validateBook);
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
        $this->first_name = null;
        $this->last_name = null;
        $this->email = null;
        $this->birthday = null;
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
            if ($this->collValidateReaderBooks) {
                foreach ($this->collValidateReaderBooks as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collValidateBooks) {
                foreach ($this->collValidateBooks as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collValidateReaderBooks = null;
        $this->collValidateBooks = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ValidateReaderTableMap::DEFAULT_STRING_FORMAT);
    }

    // validate behavior

    /**
     * Configure validators constraints. The Validator object uses this method
     * to perform object validation.
     *
     * @param ClassMetadata $metadata
     */
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('first_name', new NotNull());
        $metadata->addPropertyConstraint('first_name', new Length(array ('min' => 4,)));
        $metadata->addPropertyConstraint('last_name', new NotNull());
        $metadata->addPropertyConstraint('last_name', new Length(array ('max' => 128,)));
        $metadata->addPropertyConstraint('email', new Email());
        $metadata->addPropertyConstraint('birthday', new Date());
    }

    /**
     * Validates the object and all objects related to this table.
     *
     * @see        getValidationFailures()
     * @param ValidatorInterface|null $validator A Validator class instance
     * @return bool Whether all objects pass validation.
     */
    public function validate(?ValidatorInterface $validator = null)
    {
        if (null === $validator) {
            $validator = new RecursiveValidator(
                new ExecutionContextFactory(new IdentityTranslator()),
                new LazyLoadingMetadataFactory(new StaticMethodLoader()),
                new ConstraintValidatorFactory()
            );
        }

        $failureMap = new ConstraintViolationList();

        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;


            $retval = $validator->validate($this);
            if (count($retval) > 0) {
                $failureMap->addAll($retval);
            }

            if (null !== $this->collValidateReaderBooks) {
                foreach ($this->collValidateReaderBooks as $referrerFK) {
                    if (method_exists($referrerFK, 'validate')) {
                        if (!$referrerFK->validate($validator)) {
                            $failureMap->addAll($referrerFK->getValidationFailures());
                        }
                    }
                }
            }

            $this->alreadyInValidation = false;
        }

        $this->validationFailures = $failureMap;

        return (bool) (!(count($this->validationFailures) > 0));

    }

    /**
     * Gets any ConstraintViolation objects that resulted from last call to validate().
     *
     *
     * @return ConstraintViolationList
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
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
