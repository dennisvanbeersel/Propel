<?php

namespace Propel\Tests\Bookstore\Behavior\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\PropelQuery;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBook as ChildValidateTriggerBook;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18n as ChildValidateTriggerBookI18n;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery as ChildValidateTriggerBookI18nQuery;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerBookQuery as ChildValidateTriggerBookQuery;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerComic as ChildValidateTriggerComic;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerComicQuery as ChildValidateTriggerComicQuery;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerFiction as ChildValidateTriggerFiction;
use Propel\Tests\Bookstore\Behavior\ValidateTriggerFictionQuery as ChildValidateTriggerFictionQuery;
use Propel\Tests\Bookstore\Behavior\Map\ValidateTriggerBookI18nTableMap;
use Propel\Tests\Bookstore\Behavior\Map\ValidateTriggerBookTableMap;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Base class that represents a row from the 'validate_trigger_book' table.
 *
 * Book Table
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Behavior.Base
 */
abstract class ValidateTriggerBook implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Behavior\\Map\\ValidateTriggerBookTableMap';


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
     * Book Id
     * @var        int
     */
    protected $id;

    /**
     * The value for the isbn field.
     * ISBN Number
     * @var        string|null
     */
    protected $isbn;

    /**
     * The value for the price field.
     * Price of the book.
     * @var        double|null
     */
    protected $price;

    /**
     * The value for the publisher_id field.
     * Foreign Key Publisher
     * @var        int|null
     */
    protected $publisher_id;

    /**
     * The value for the author_id field.
     * Foreign Key Author
     * @var        int|null
     */
    protected $author_id;

    /**
     * The value for the descendant_class field.
     *
     * @var        string|null
     */
    protected $descendant_class;

    /**
     * @var        ChildValidateTriggerFiction one-to-one related ChildValidateTriggerFiction object
     */
    protected $singleValidateTriggerFiction;

    /**
     * @var        ChildValidateTriggerComic one-to-one related ChildValidateTriggerComic object
     */
    protected $singleValidateTriggerComic;

    /**
     * @var        ObjectCollection|ChildValidateTriggerBookI18n[] Collection to store aggregation of ChildValidateTriggerBookI18n objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildValidateTriggerBookI18n> Collection to store aggregation of ChildValidateTriggerBookI18n objects.
     */
    protected $collValidateTriggerBookI18ns;
    protected $collValidateTriggerBookI18nsPartial;

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

    // i18n behavior

    /**
     * Current locale
     * @var        string
     */
    protected $currentLocale = 'en_US';

    /**
     * Current translation objects
     * @var        array[ChildValidateTriggerBookI18n]
     */
    protected $currentTranslations;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildValidateTriggerBookI18n[]
     * @phpstan-var ObjectCollection&\Traversable<ChildValidateTriggerBookI18n>
     */
    protected $validateTriggerBookI18nsScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Behavior\Base\ValidateTriggerBook object.
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
     * Compares this with another <code>ValidateTriggerBook</code> instance.  If
     * <code>obj</code> is an instance of <code>ValidateTriggerBook</code>, delegates to
     * <code>equals(ValidateTriggerBook)</code>.  Otherwise, returns <code>false</code>.
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
     * Book Id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [isbn] column value.
     * ISBN Number
     * @return string|null
     */
    public function getISBN()
    {
        return $this->isbn;
    }

    /**
     * Get the [price] column value.
     * Price of the book.
     * @return double|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get the [publisher_id] column value.
     * Foreign Key Publisher
     * @return int|null
     */
    public function getPublisherId()
    {
        return $this->publisher_id;
    }

    /**
     * Get the [author_id] column value.
     * Foreign Key Author
     * @return int|null
     */
    public function getAuthorId()
    {
        return $this->author_id;
    }

    /**
     * Get the [descendant_class] column value.
     *
     * @return string|null
     */
    public function getDescendantClass()
    {
        return $this->descendant_class;
    }

    /**
     * Set the value of [id] column.
     * Book Id
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
            $this->modifiedColumns[ValidateTriggerBookTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [isbn] column.
     * ISBN Number
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setISBN($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->isbn !== $v) {
            $this->isbn = $v;
            $this->modifiedColumns[ValidateTriggerBookTableMap::COL_ISBN] = true;
        }

        return $this;
    }

    /**
     * Set the value of [price] column.
     * Price of the book.
     * @param double|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPrice($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->price !== $v) {
            $this->price = $v;
            $this->modifiedColumns[ValidateTriggerBookTableMap::COL_PRICE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [publisher_id] column.
     * Foreign Key Publisher
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPublisherId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->publisher_id !== $v) {
            $this->publisher_id = $v;
            $this->modifiedColumns[ValidateTriggerBookTableMap::COL_PUBLISHER_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [author_id] column.
     * Foreign Key Author
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setAuthorId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->author_id !== $v) {
            $this->author_id = $v;
            $this->modifiedColumns[ValidateTriggerBookTableMap::COL_AUTHOR_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [descendant_class] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setDescendantClass($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->descendant_class !== $v) {
            $this->descendant_class = $v;
            $this->modifiedColumns[ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS] = true;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ValidateTriggerBookTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ValidateTriggerBookTableMap::translateFieldName('ISBN', TableMap::TYPE_PHPNAME, $indexType)];
            $this->isbn = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ValidateTriggerBookTableMap::translateFieldName('Price', TableMap::TYPE_PHPNAME, $indexType)];
            $this->price = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ValidateTriggerBookTableMap::translateFieldName('PublisherId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->publisher_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ValidateTriggerBookTableMap::translateFieldName('AuthorId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->author_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : ValidateTriggerBookTableMap::translateFieldName('DescendantClass', TableMap::TYPE_PHPNAME, $indexType)];
            $this->descendant_class = (null !== $col) ? (string) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 6; // 6 = ValidateTriggerBookTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Behavior\\ValidateTriggerBook'), 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildValidateTriggerBookQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->singleValidateTriggerFiction = null;

            $this->singleValidateTriggerComic = null;

            $this->collValidateTriggerBookI18ns = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see ValidateTriggerBook::setDeleted()
     * @see ValidateTriggerBook::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildValidateTriggerBookQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(ValidateTriggerBookTableMap::DATABASE_NAME);
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
                ValidateTriggerBookTableMap::addInstanceToPool($this);
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

            if ($this->singleValidateTriggerFiction !== null) {
                if (!$this->singleValidateTriggerFiction->isDeleted() && ($this->singleValidateTriggerFiction->isNew() || $this->singleValidateTriggerFiction->isModified())) {
                    $affectedRows += $this->singleValidateTriggerFiction->save($con);
                }
            }

            if ($this->singleValidateTriggerComic !== null) {
                if (!$this->singleValidateTriggerComic->isDeleted() && ($this->singleValidateTriggerComic->isNew() || $this->singleValidateTriggerComic->isModified())) {
                    $affectedRows += $this->singleValidateTriggerComic->save($con);
                }
            }

            if ($this->validateTriggerBookI18nsScheduledForDeletion !== null) {
                if (!$this->validateTriggerBookI18nsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18nQuery::create()
                        ->filterByPrimaryKeys($this->validateTriggerBookI18nsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->validateTriggerBookI18nsScheduledForDeletion = null;
                }
            }

            if ($this->collValidateTriggerBookI18ns !== null) {
                foreach ($this->collValidateTriggerBookI18ns as $referrerFK) {
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

        $this->modifiedColumns[ValidateTriggerBookTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ValidateTriggerBookTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_ISBN)) {
            $modifiedColumns[':p' . $index++]  = 'isbn';
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_PRICE)) {
            $modifiedColumns[':p' . $index++]  = 'price';
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_PUBLISHER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'publisher_id';
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_AUTHOR_ID)) {
            $modifiedColumns[':p' . $index++]  = 'author_id';
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS)) {
            $modifiedColumns[':p' . $index++]  = 'descendant_class';
        }

        $sql = sprintf(
            'INSERT INTO validate_trigger_book (%s) VALUES (%s)',
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
                    case 'isbn':
                        $stmt->bindValue($identifier, $this->isbn, PDO::PARAM_STR);

                        break;
                    case 'price':
                        $stmt->bindValue($identifier, $this->price, PDO::PARAM_STR);

                        break;
                    case 'publisher_id':
                        $stmt->bindValue($identifier, $this->publisher_id, PDO::PARAM_INT);

                        break;
                    case 'author_id':
                        $stmt->bindValue($identifier, $this->author_id, PDO::PARAM_INT);

                        break;
                    case 'descendant_class':
                        $stmt->bindValue($identifier, $this->descendant_class, PDO::PARAM_STR);

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
        $pos = ValidateTriggerBookTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getISBN();

            case 2:
                return $this->getPrice();

            case 3:
                return $this->getPublisherId();

            case 4:
                return $this->getAuthorId();

            case 5:
                return $this->getDescendantClass();

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
        if (isset($alreadyDumpedObjects['ValidateTriggerBook'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['ValidateTriggerBook'][$this->hashCode()] = true;
        $keys = ValidateTriggerBookTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getISBN(),
            $keys[2] => $this->getPrice(),
            $keys[3] => $this->getPublisherId(),
            $keys[4] => $this->getAuthorId(),
            $keys[5] => $this->getDescendantClass(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->singleValidateTriggerFiction) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'validateTriggerFiction';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'validate_trigger_fiction';
                        break;
                    default:
                        $key = 'ValidateTriggerFiction';
                }

                $result[$key] = $this->singleValidateTriggerFiction->toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, true);
            }
            if (null !== $this->singleValidateTriggerComic) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'validateTriggerComic';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'validate_trigger_comic';
                        break;
                    default:
                        $key = 'ValidateTriggerComic';
                }

                $result[$key] = $this->singleValidateTriggerComic->toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, true);
            }
            if (null !== $this->collValidateTriggerBookI18ns) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'validateTriggerBookI18ns';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'validate_trigger_book_i18ns';
                        break;
                    default:
                        $key = 'ValidateTriggerBookI18ns';
                }

                $result[$key] = $this->collValidateTriggerBookI18ns->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = ValidateTriggerBookTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setISBN($value);
                break;
            case 2:
                $this->setPrice($value);
                break;
            case 3:
                $this->setPublisherId($value);
                break;
            case 4:
                $this->setAuthorId($value);
                break;
            case 5:
                $this->setDescendantClass($value);
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
        $keys = ValidateTriggerBookTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setISBN($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setPrice($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setPublisherId($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setAuthorId($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setDescendantClass($arr[$keys[5]]);
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
        $criteria = new Criteria(ValidateTriggerBookTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_ID)) {
            $criteria->add(ValidateTriggerBookTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_ISBN)) {
            $criteria->add(ValidateTriggerBookTableMap::COL_ISBN, $this->isbn);
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_PRICE)) {
            $criteria->add(ValidateTriggerBookTableMap::COL_PRICE, $this->price);
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_PUBLISHER_ID)) {
            $criteria->add(ValidateTriggerBookTableMap::COL_PUBLISHER_ID, $this->publisher_id);
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_AUTHOR_ID)) {
            $criteria->add(ValidateTriggerBookTableMap::COL_AUTHOR_ID, $this->author_id);
        }
        if ($this->isColumnModified(ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS)) {
            $criteria->add(ValidateTriggerBookTableMap::COL_DESCENDANT_CLASS, $this->descendant_class);
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
        $criteria = ChildValidateTriggerBookQuery::create();
        $criteria->add(ValidateTriggerBookTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Behavior\ValidateTriggerBook (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setISBN($this->getISBN());
        $copyObj->setPrice($this->getPrice());
        $copyObj->setPublisherId($this->getPublisherId());
        $copyObj->setAuthorId($this->getAuthorId());
        $copyObj->setDescendantClass($this->getDescendantClass());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            $relObj = $this->getValidateTriggerFiction();
            if ($relObj) {
                $copyObj->setValidateTriggerFiction($relObj->copy($deepCopy));
            }

            $relObj = $this->getValidateTriggerComic();
            if ($relObj) {
                $copyObj->setValidateTriggerComic($relObj->copy($deepCopy));
            }

            foreach ($this->getValidateTriggerBookI18ns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addValidateTriggerBookI18n($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\Behavior\ValidateTriggerBook Clone of current object.
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
        if ('ValidateTriggerBookI18n' === $relationName) {
            $this->initValidateTriggerBookI18ns();
            return;
        }
    }

    /**
     * Gets a single ChildValidateTriggerFiction object, which is related to this object by a one-to-one relationship.
     *
     * @param ConnectionInterface $con optional connection object
     * @return ChildValidateTriggerFiction|null
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getValidateTriggerFiction(?ConnectionInterface $con = null)
    {

        if ($this->singleValidateTriggerFiction === null && !$this->isNew()) {
            $this->singleValidateTriggerFiction = ChildValidateTriggerFictionQuery::create()->findPk($this->getPrimaryKey(), $con);
        }

        return $this->singleValidateTriggerFiction;
    }

    /**
     * Sets a single ChildValidateTriggerFiction object as related to this object by a one-to-one relationship.
     *
     * @param ChildValidateTriggerFiction|null $v ChildValidateTriggerFiction
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setValidateTriggerFiction(?ChildValidateTriggerFiction $v = null)
    {
        $this->singleValidateTriggerFiction = $v;

        // Make sure that that the passed-in ChildValidateTriggerFiction isn't already associated with this object
        if ($v !== null && $v->getValidateTriggerBook(null, false) === null) {
            $v->setValidateTriggerBook($this);
        }

        return $this;
    }

    /**
     * Gets a single ChildValidateTriggerComic object, which is related to this object by a one-to-one relationship.
     *
     * @param ConnectionInterface $con optional connection object
     * @return ChildValidateTriggerComic|null
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getValidateTriggerComic(?ConnectionInterface $con = null)
    {

        if ($this->singleValidateTriggerComic === null && !$this->isNew()) {
            $this->singleValidateTriggerComic = ChildValidateTriggerComicQuery::create()->findPk($this->getPrimaryKey(), $con);
        }

        return $this->singleValidateTriggerComic;
    }

    /**
     * Sets a single ChildValidateTriggerComic object as related to this object by a one-to-one relationship.
     *
     * @param ChildValidateTriggerComic|null $v ChildValidateTriggerComic
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setValidateTriggerComic(?ChildValidateTriggerComic $v = null)
    {
        $this->singleValidateTriggerComic = $v;

        // Make sure that that the passed-in ChildValidateTriggerComic isn't already associated with this object
        if ($v !== null && $v->getValidateTriggerBook(null, false) === null) {
            $v->setValidateTriggerBook($this);
        }

        return $this;
    }

    /**
     * Clears out the collValidateTriggerBookI18ns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addValidateTriggerBookI18ns()
     */
    public function clearValidateTriggerBookI18ns()
    {
        $this->collValidateTriggerBookI18ns = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collValidateTriggerBookI18ns collection loaded partially.
     *
     * @return void
     */
    public function resetPartialValidateTriggerBookI18ns($v = true): void
    {
        $this->collValidateTriggerBookI18nsPartial = $v;
    }

    /**
     * Initializes the collValidateTriggerBookI18ns collection.
     *
     * By default this just sets the collValidateTriggerBookI18ns collection to an empty array (like clearcollValidateTriggerBookI18ns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initValidateTriggerBookI18ns(bool $overrideExisting = true): void
    {
        if (null !== $this->collValidateTriggerBookI18ns && !$overrideExisting) {
            return;
        }

        $collectionClassName = ValidateTriggerBookI18nTableMap::getTableMap()->getCollectionClassName();

        $this->collValidateTriggerBookI18ns = new $collectionClassName;
        $this->collValidateTriggerBookI18ns->setModel('\Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18n');
    }

    /**
     * Gets an array of ChildValidateTriggerBookI18n objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildValidateTriggerBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildValidateTriggerBookI18n[] List of ChildValidateTriggerBookI18n objects
     * @phpstan-return ObjectCollection&\Traversable<ChildValidateTriggerBookI18n> List of ChildValidateTriggerBookI18n objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getValidateTriggerBookI18ns(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collValidateTriggerBookI18nsPartial && !$this->isNew();
        if (null === $this->collValidateTriggerBookI18ns || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collValidateTriggerBookI18ns) {
                    $this->initValidateTriggerBookI18ns();
                } else {
                    $collectionClassName = ValidateTriggerBookI18nTableMap::getTableMap()->getCollectionClassName();

                    $collValidateTriggerBookI18ns = new $collectionClassName;
                    $collValidateTriggerBookI18ns->setModel('\Propel\Tests\Bookstore\Behavior\ValidateTriggerBookI18n');

                    return $collValidateTriggerBookI18ns;
                }
            } else {
                $collValidateTriggerBookI18ns = ChildValidateTriggerBookI18nQuery::create(null, $criteria)
                    ->filterByValidateTriggerBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collValidateTriggerBookI18nsPartial && count($collValidateTriggerBookI18ns)) {
                        $this->initValidateTriggerBookI18ns(false);

                        foreach ($collValidateTriggerBookI18ns as $obj) {
                            if (false === $this->collValidateTriggerBookI18ns->contains($obj)) {
                                $this->collValidateTriggerBookI18ns->append($obj);
                            }
                        }

                        $this->collValidateTriggerBookI18nsPartial = true;
                    }

                    return $collValidateTriggerBookI18ns;
                }

                if ($partial && $this->collValidateTriggerBookI18ns) {
                    foreach ($this->collValidateTriggerBookI18ns as $obj) {
                        if ($obj->isNew()) {
                            $collValidateTriggerBookI18ns[] = $obj;
                        }
                    }
                }

                $this->collValidateTriggerBookI18ns = $collValidateTriggerBookI18ns;
                $this->collValidateTriggerBookI18nsPartial = false;
            }
        }

        return $this->collValidateTriggerBookI18ns;
    }

    /**
     * Sets a collection of ChildValidateTriggerBookI18n objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $validateTriggerBookI18ns A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setValidateTriggerBookI18ns(Collection $validateTriggerBookI18ns, ?ConnectionInterface $con = null)
    {
        /** @var ChildValidateTriggerBookI18n[] $validateTriggerBookI18nsToDelete */
        $validateTriggerBookI18nsToDelete = $this->getValidateTriggerBookI18ns(new Criteria(), $con)->diff($validateTriggerBookI18ns);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->validateTriggerBookI18nsScheduledForDeletion = clone $validateTriggerBookI18nsToDelete;

        foreach ($validateTriggerBookI18nsToDelete as $validateTriggerBookI18nRemoved) {
            $validateTriggerBookI18nRemoved->setValidateTriggerBook(null);
        }

        $this->collValidateTriggerBookI18ns = null;
        foreach ($validateTriggerBookI18ns as $validateTriggerBookI18n) {
            $this->addValidateTriggerBookI18n($validateTriggerBookI18n);
        }

        $this->collValidateTriggerBookI18ns = $validateTriggerBookI18ns;
        $this->collValidateTriggerBookI18nsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ValidateTriggerBookI18n objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ValidateTriggerBookI18n objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countValidateTriggerBookI18ns(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collValidateTriggerBookI18nsPartial && !$this->isNew();
        if (null === $this->collValidateTriggerBookI18ns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collValidateTriggerBookI18ns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getValidateTriggerBookI18ns());
            }

            $query = ChildValidateTriggerBookI18nQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByValidateTriggerBook($this)
                ->count($con);
        }

        return count($this->collValidateTriggerBookI18ns);
    }

    /**
     * Method called to associate a ChildValidateTriggerBookI18n object to this object
     * through the ChildValidateTriggerBookI18n foreign key attribute.
     *
     * @param ChildValidateTriggerBookI18n $l ChildValidateTriggerBookI18n
     * @return $this The current object (for fluent API support)
     */
    public function addValidateTriggerBookI18n(ChildValidateTriggerBookI18n $l)
    {
        if ($l && $locale = $l->getLocale()) {
            $this->setLocale($locale);
            $this->currentTranslations[$locale] = $l;
        }
        if ($this->collValidateTriggerBookI18ns === null) {
            $this->initValidateTriggerBookI18ns();
            $this->collValidateTriggerBookI18nsPartial = true;
        }

        if (!$this->collValidateTriggerBookI18ns->contains($l)) {
            $this->doAddValidateTriggerBookI18n($l);

            if ($this->validateTriggerBookI18nsScheduledForDeletion and $this->validateTriggerBookI18nsScheduledForDeletion->contains($l)) {
                $this->validateTriggerBookI18nsScheduledForDeletion->remove($this->validateTriggerBookI18nsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildValidateTriggerBookI18n $validateTriggerBookI18n The ChildValidateTriggerBookI18n object to add.
     */
    protected function doAddValidateTriggerBookI18n(ChildValidateTriggerBookI18n $validateTriggerBookI18n): void
    {
        $this->collValidateTriggerBookI18ns[]= $validateTriggerBookI18n;
        $validateTriggerBookI18n->setValidateTriggerBook($this);
    }

    /**
     * @param ChildValidateTriggerBookI18n $validateTriggerBookI18n The ChildValidateTriggerBookI18n object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeValidateTriggerBookI18n(ChildValidateTriggerBookI18n $validateTriggerBookI18n)
    {
        if ($this->getValidateTriggerBookI18ns()->contains($validateTriggerBookI18n)) {
            $pos = $this->collValidateTriggerBookI18ns->search($validateTriggerBookI18n);
            $this->collValidateTriggerBookI18ns->remove($pos);
            if (null === $this->validateTriggerBookI18nsScheduledForDeletion) {
                $this->validateTriggerBookI18nsScheduledForDeletion = clone $this->collValidateTriggerBookI18ns;
                $this->validateTriggerBookI18nsScheduledForDeletion->clear();
            }
            $this->validateTriggerBookI18nsScheduledForDeletion[]= clone $validateTriggerBookI18n;
            $validateTriggerBookI18n->setValidateTriggerBook(null);
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
        $this->isbn = null;
        $this->price = null;
        $this->publisher_id = null;
        $this->author_id = null;
        $this->descendant_class = null;
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
            if ($this->singleValidateTriggerFiction) {
                $this->singleValidateTriggerFiction->clearAllReferences($deep);
            }
            if ($this->singleValidateTriggerComic) {
                $this->singleValidateTriggerComic->clearAllReferences($deep);
            }
            if ($this->collValidateTriggerBookI18ns) {
                foreach ($this->collValidateTriggerBookI18ns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        // i18n behavior
        $this->currentLocale = 'en_US';
        $this->currentTranslations = null;

        $this->singleValidateTriggerFiction = null;
        $this->singleValidateTriggerComic = null;
        $this->collValidateTriggerBookI18ns = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ValidateTriggerBookTableMap::DEFAULT_STRING_FORMAT);
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
        $metadata->addPropertyConstraint('isbn', new Regex(array ('pattern' => '/[^\\d-]+/','match' => false,'message' => 'Please enter a valid ISBN',)));
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

            if (null !== $this->collValidateTriggerBookI18ns) {
                foreach ($this->collValidateTriggerBookI18ns as $referrerFK) {
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

    // i18n behavior

    /**
     * Sets the locale for translations
     *
     * @param string $locale Locale to use for the translation, e.g. 'fr_FR'
     *
     * @return $this The current object (for fluent API support)
     */
    public function setLocale($locale = 'en_US')
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Gets the locale for translations
     *
     * @return string $locale Locale to use for the translation, e.g. 'fr_FR'
     */
    public function getLocale()
    {
        return $this->currentLocale;
    }

    /**
     * Returns the current translation for a given locale
     *
     * @param string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param ConnectionInterface|null $con an optional connection object
     *
     * @return ChildValidateTriggerBookI18n */
    public function getTranslation(string $locale = 'en_US', ?ConnectionInterface $con = null)
    {
        if (!isset($this->currentTranslations[$locale])) {
            if (null !== $this->collValidateTriggerBookI18ns) {
                foreach ($this->collValidateTriggerBookI18ns as $translation) {
                    if ($translation->getLocale() == $locale) {
                        $this->currentTranslations[$locale] = $translation;

                        return $translation;
                    }
                }
            }
            if ($this->isNew()) {
                $translation = new ChildValidateTriggerBookI18n();
                $translation->setLocale($locale);
            } else {
                $translation = ChildValidateTriggerBookI18nQuery::create()
                    ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                    ->findOneOrCreate($con);
                $this->currentTranslations[$locale] = $translation;
            }
            $this->addValidateTriggerBookI18n($translation);
        }

        return $this->currentTranslations[$locale];
    }

    /**
     * Remove the translation for a given locale
     *
     * @param string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param ConnectionInterface|null $con an optional connection object
     *
     * @return $this The current object (for fluent API support)
     */
    public function removeTranslation(string $locale = 'en_US', ?ConnectionInterface $con = null)
    {
        if (!$this->isNew()) {
            ChildValidateTriggerBookI18nQuery::create()
                ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                ->delete($con);
        }
        unset($this->currentTranslations[$locale]);
        foreach ($this->collValidateTriggerBookI18ns as $key => $translation) {
            if ($translation->getLocale() == $locale) {
                unset($this->collValidateTriggerBookI18ns[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Returns the current translation
     *
     * @param ConnectionInterface|null $con an optional connection object
     *
     * @return ChildValidateTriggerBookI18n */
    public function getCurrentTranslation(?ConnectionInterface $con = null)
    {
        return $this->getTranslation($this->getLocale(), $con);
    }


        /**
         * Get the [title] column value.
         * Book Title
         * @return string
         */
        public function getTitle()
        {
        return $this->getCurrentTranslation()->getTitle();
    }


        /**
         * Set the value of [title] column.
         * Book Title
         * @param string $v New value
         * @return $this The current object (for fluent API support)
         */
        public function setTitle($v)
        {    $this->getCurrentTranslation()->setTitle($v);

        return $this;
    }

    // concrete_inheritance_parent behavior

    /**
     * Whether this object is the parent of a child object
     *
     * @return bool
     */
    public function hasChildObject(): bool
    {
        return $this->getDescendantClass() !== null;
    }

    /**
     * Get the child object of this object
     *
     * @return mixed
     */
    public function getChildObject()
    {
        if (!$this->hasChildObject()) {
            return null;
        }
        $childObjectClass = $this->getDescendantClass();
        $childObject = PropelQuery::from($childObjectClass)->findPk($this->getPrimaryKey());

        return $childObject->hasChildObject() ? $childObject->getChildObject() : $childObject;
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
