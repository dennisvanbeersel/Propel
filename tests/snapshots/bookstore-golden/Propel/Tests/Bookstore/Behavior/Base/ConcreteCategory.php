<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Base;

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
use Propel\Tests\Bookstore\Behavior\ConcreteArticle as ChildConcreteArticle;
use Propel\Tests\Bookstore\Behavior\ConcreteArticleQuery as ChildConcreteArticleQuery;
use Propel\Tests\Bookstore\Behavior\ConcreteCategory as ChildConcreteCategory;
use Propel\Tests\Bookstore\Behavior\ConcreteCategoryQuery as ChildConcreteCategoryQuery;
use Propel\Tests\Bookstore\Behavior\ConcreteContent as ChildConcreteContent;
use Propel\Tests\Bookstore\Behavior\ConcreteContentQuery as ChildConcreteContentQuery;
use Propel\Tests\Bookstore\Behavior\ConcreteNews as ChildConcreteNews;
use Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery as ChildConcreteNewsQuery;
use Propel\Tests\Bookstore\Behavior\ConcreteQuizz as ChildConcreteQuizz;
use Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery as ChildConcreteQuizzQuery;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteArticleTableMap;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteCategoryTableMap;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteContentTableMap;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteNewsTableMap;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteQuizzTableMap;

/**
 * Base class that represents a row from the 'concrete_category' table.
 *
 *
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Behavior.Base
 */
abstract class ConcreteCategory implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Behavior\\Map\\ConcreteCategoryTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var bool
     */
    protected bool $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var bool
     */
    protected bool $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected array $modifiedColumns = [];

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected array $virtualColumns = [];

    /**
     * The value for the id field.
     *
     * @var        int
     */
    public protected(set) ?int $id = null;

    /**
     * The value for the name field.
     *
     * @var        string|null
     */
    public protected(set) ?string $name = null;

    /**
     * @var        ObjectCollection|ChildConcreteContent[] Collection to store aggregation of ChildConcreteContent objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteContent> Collection to store aggregation of ChildConcreteContent objects.
     */
    protected $collConcreteContents;
    protected bool $collConcreteContentsPartial = false;

    /**
     * @var        ObjectCollection|ChildConcreteArticle[] Collection to store aggregation of ChildConcreteArticle objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteArticle> Collection to store aggregation of ChildConcreteArticle objects.
     */
    protected $collConcreteArticles;
    protected bool $collConcreteArticlesPartial = false;

    /**
     * @var        ObjectCollection|ChildConcreteNews[] Collection to store aggregation of ChildConcreteNews objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteNews> Collection to store aggregation of ChildConcreteNews objects.
     */
    protected $collConcreteNewss;
    protected bool $collConcreteNewssPartial = false;

    /**
     * @var        ObjectCollection|ChildConcreteQuizz[] Collection to store aggregation of ChildConcreteQuizz objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteQuizz> Collection to store aggregation of ChildConcreteQuizz objects.
     */
    protected $collConcreteQuizzs;
    protected bool $collConcreteQuizzsPartial = false;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildConcreteContent[]
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteContent>
     */
    protected $concreteContentsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildConcreteArticle[]
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteArticle>
     */
    protected $concreteArticlesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildConcreteNews[]
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteNews>
     */
    protected $concreteNewssScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildConcreteQuizz[]
     * @phpstan-var ObjectCollection&\Traversable<ChildConcreteQuizz>
     */
    protected $concreteQuizzsScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Behavior\Base\ConcreteCategory object.
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
     * Compares this with another <code>ConcreteCategory</code> instance.  If
     * <code>obj</code> is an instance of <code>ConcreteCategory</code>, delegates to
     * <code>equals(ConcreteCategory)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param mixed $obj The object to compare to.
     * @return bool Whether equal to the object specified.
     */
    public function equals(mixed $obj): bool
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
    public function getVirtualColumn(string $name): mixed
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
    public function setVirtualColumn(string $name, mixed $value): self
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
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $data = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach ($serializableProperties as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            $data[$name] = $property->isInitialized($this) ? $property->getValue($this) : null;
        }

        return $data;
    }

    /**
     * Restore object state after unserializing.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $cls = new \ReflectionClass($this);

        foreach ($data as $name => $value) {
            if (!$cls->hasProperty($name)) {
                continue;
            }
            $property = $cls->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($this, $value);
        }
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
     * Get the [name] column value.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setId($v): self
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[ConcreteCategoryTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [name] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setName($v): self
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[ConcreteCategoryTableMap::COL_NAME] = true;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ConcreteCategoryTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ConcreteCategoryTableMap::translateFieldName('Name', TableMap::TYPE_PHPNAME, $indexType)];
            $this->name = (null !== $col) ? (string) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 2; // 2 = ConcreteCategoryTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteCategory'), 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(ConcreteCategoryTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildConcreteCategoryQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collConcreteContents = null;

            $this->collConcreteArticles = null;

            $this->collConcreteNewss = null;

            $this->collConcreteQuizzs = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see ConcreteCategory::setDeleted()
     * @see ConcreteCategory::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteCategoryTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildConcreteCategoryQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteCategoryTableMap::DATABASE_NAME);
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
                ConcreteCategoryTableMap::addInstanceToPool($this);
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

            if ($this->concreteContentsScheduledForDeletion !== null) {
                if (!$this->concreteContentsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\Behavior\ConcreteContentQuery::create()
                        ->filterByPrimaryKeys($this->concreteContentsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->concreteContentsScheduledForDeletion = null;
                }
            }

            if ($this->collConcreteContents !== null) {
                foreach ($this->collConcreteContents as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->concreteArticlesScheduledForDeletion !== null) {
                if (!$this->concreteArticlesScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\Behavior\ConcreteArticleQuery::create()
                        ->filterByPrimaryKeys($this->concreteArticlesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->concreteArticlesScheduledForDeletion = null;
                }
            }

            if ($this->collConcreteArticles !== null) {
                foreach ($this->collConcreteArticles as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->concreteNewssScheduledForDeletion !== null) {
                if (!$this->concreteNewssScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\Behavior\ConcreteNewsQuery::create()
                        ->filterByPrimaryKeys($this->concreteNewssScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->concreteNewssScheduledForDeletion = null;
                }
            }

            if ($this->collConcreteNewss !== null) {
                foreach ($this->collConcreteNewss as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->concreteQuizzsScheduledForDeletion !== null) {
                if (!$this->concreteQuizzsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery::create()
                        ->filterByPrimaryKeys($this->concreteQuizzsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->concreteQuizzsScheduledForDeletion = null;
                }
            }

            if ($this->collConcreteQuizzs !== null) {
                foreach ($this->collConcreteQuizzs as $referrerFK) {
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

        $this->modifiedColumns[ConcreteCategoryTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ConcreteCategoryTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ConcreteCategoryTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(ConcreteCategoryTableMap::COL_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'name';
        }

        $sql = sprintf(
            'INSERT INTO concrete_category (%s) VALUES (%s)',
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
                    case 'name':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);

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
        $pos = (int)ConcreteCategoryTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getName();

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
        if (isset($alreadyDumpedObjects['ConcreteCategory'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['ConcreteCategory'][$this->hashCode()] = true;
        $keys = ConcreteCategoryTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getName(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collConcreteContents) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'concreteContents';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'concrete_contents';
                        break;
                    default:
                        $key = 'ConcreteContents';
                }

                $result[$key] = $this->collConcreteContents->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collConcreteArticles) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'concreteArticles';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'concrete_articles';
                        break;
                    default:
                        $key = 'ConcreteArticles';
                }

                $result[$key] = $this->collConcreteArticles->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collConcreteNewss) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'concreteNewss';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'concrete_newss';
                        break;
                    default:
                        $key = 'ConcreteNewss';
                }

                $result[$key] = $this->collConcreteNewss->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collConcreteQuizzs) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'concreteQuizzs';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'concrete_quizzs';
                        break;
                    default:
                        $key = 'ConcreteQuizzs';
                }

                $result[$key] = $this->collConcreteQuizzs->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = (int)ConcreteCategoryTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setName($value);
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
        $keys = ConcreteCategoryTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setName($arr[$keys[1]]);
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
        $criteria = new Criteria(ConcreteCategoryTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ConcreteCategoryTableMap::COL_ID)) {
            $criteria->add(ConcreteCategoryTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(ConcreteCategoryTableMap::COL_NAME)) {
            $criteria->add(ConcreteCategoryTableMap::COL_NAME, $this->name);
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
        $criteria = ChildConcreteCategoryQuery::create();
        $criteria->add(ConcreteCategoryTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Behavior\ConcreteCategory (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setName($this->getName());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getConcreteContents() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addConcreteContent($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getConcreteArticles() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addConcreteArticle($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getConcreteNewss() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addConcreteNews($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getConcreteQuizzs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addConcreteQuizz($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteCategory Clone of current object.
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
        if ('ConcreteContent' === $relationName) {
            $this->initConcreteContents();
            return;
        }
        if ('ConcreteArticle' === $relationName) {
            $this->initConcreteArticles();
            return;
        }
        if ('ConcreteNews' === $relationName) {
            $this->initConcreteNewss();
            return;
        }
        if ('ConcreteQuizz' === $relationName) {
            $this->initConcreteQuizzs();
            return;
        }
    }

    /**
     * Clears out the collConcreteContents collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addConcreteContents()
     */
    public function clearConcreteContents()
    {
        $this->collConcreteContents = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collConcreteContents collection loaded partially.
     *
     * @return void
     */
    public function resetPartialConcreteContents($v = true): void
    {
        $this->collConcreteContentsPartial = $v;
    }

    /**
     * Initializes the collConcreteContents collection.
     *
     * By default this just sets the collConcreteContents collection to an empty array (like clearcollConcreteContents());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initConcreteContents(bool $overrideExisting = true): void
    {
        if (null !== $this->collConcreteContents && !$overrideExisting) {
            return;
        }

        $collectionClassName = ConcreteContentTableMap::getTableMap()->getCollectionClassName();

        $this->collConcreteContents = new $collectionClassName;
        $this->collConcreteContents->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteContent');
    }

    /**
     * Gets an array of ChildConcreteContent objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildConcreteCategory is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildConcreteContent[] List of ChildConcreteContent objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteContent> List of ChildConcreteContent objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getConcreteContents(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collConcreteContentsPartial && !$this->isNew();
        if (null === $this->collConcreteContents || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collConcreteContents) {
                    $this->initConcreteContents();
                } else {
                    $collectionClassName = ConcreteContentTableMap::getTableMap()->getCollectionClassName();

                    $collConcreteContents = new $collectionClassName;
                    $collConcreteContents->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteContent');

                    return $collConcreteContents;
                }
            } else {
                $collConcreteContents = ChildConcreteContentQuery::create(null, $criteria)
                    ->filterByConcreteCategory($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collConcreteContentsPartial && count($collConcreteContents)) {
                        $this->initConcreteContents(false);

                        foreach ($collConcreteContents as $obj) {
                            if (false === $this->collConcreteContents->contains($obj)) {
                                $this->collConcreteContents->append($obj);
                            }
                        }

                        $this->collConcreteContentsPartial = true;
                    }

                    return $collConcreteContents;
                }

                if ($partial && $this->collConcreteContents) {
                    foreach ($this->collConcreteContents as $obj) {
                        if ($obj->isNew()) {
                            $collConcreteContents[] = $obj;
                        }
                    }
                }

                $this->collConcreteContents = $collConcreteContents;
                $this->collConcreteContentsPartial = false;
            }
        }

        return $this->collConcreteContents;
    }

    /**
     * Sets a collection of ChildConcreteContent objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $concreteContents A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setConcreteContents(Collection $concreteContents, ?ConnectionInterface $con = null)
    {
        /** @var ChildConcreteContent[] $concreteContentsToDelete */
        $concreteContentsToDelete = $this->getConcreteContents(new Criteria(), $con)->diff($concreteContents);


        $this->concreteContentsScheduledForDeletion = $concreteContentsToDelete;

        foreach ($concreteContentsToDelete as $concreteContentRemoved) {
            $concreteContentRemoved->setConcreteCategory(null);
        }

        $this->collConcreteContents = null;
        foreach ($concreteContents as $concreteContent) {
            $this->addConcreteContent($concreteContent);
        }

        $this->collConcreteContents = $concreteContents;
        $this->collConcreteContentsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ConcreteContent objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ConcreteContent objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countConcreteContents(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collConcreteContentsPartial && !$this->isNew();
        if (null === $this->collConcreteContents || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collConcreteContents) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getConcreteContents());
            }

            $query = ChildConcreteContentQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByConcreteCategory($this)
                ->count($con);
        }

        return count($this->collConcreteContents);
    }

    /**
     * Method called to associate a ChildConcreteContent object to this object
     * through the ChildConcreteContent foreign key attribute.
     *
     * @param ChildConcreteContent $l ChildConcreteContent
     * @return $this The current object (for fluent API support)
     */
    public function addConcreteContent(ChildConcreteContent $l)
    {
        if ($this->collConcreteContents === null) {
            $this->initConcreteContents();
            $this->collConcreteContentsPartial = true;
        }

        if (!$this->collConcreteContents->contains($l)) {
            $this->doAddConcreteContent($l);

            if ($this->concreteContentsScheduledForDeletion and $this->concreteContentsScheduledForDeletion->contains($l)) {
                $this->concreteContentsScheduledForDeletion->remove($this->concreteContentsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildConcreteContent $concreteContent The ChildConcreteContent object to add.
     */
    protected function doAddConcreteContent(ChildConcreteContent $concreteContent): void
    {
        $this->collConcreteContents[]= $concreteContent;
        $concreteContent->setConcreteCategory($this);
    }

    /**
     * @param ChildConcreteContent $concreteContent The ChildConcreteContent object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeConcreteContent(ChildConcreteContent $concreteContent)
    {
        if ($this->getConcreteContents()->contains($concreteContent)) {
            $pos = $this->collConcreteContents->search($concreteContent);
            $this->collConcreteContents->remove($pos);
            if (null === $this->concreteContentsScheduledForDeletion) {
                $this->concreteContentsScheduledForDeletion = clone $this->collConcreteContents;
                $this->concreteContentsScheduledForDeletion->clear();
            }
            $this->concreteContentsScheduledForDeletion[]= $concreteContent;
            $concreteContent->setConcreteCategory(null);
        }

        return $this;
    }

    /**
     * Clears out the collConcreteArticles collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addConcreteArticles()
     */
    public function clearConcreteArticles()
    {
        $this->collConcreteArticles = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collConcreteArticles collection loaded partially.
     *
     * @return void
     */
    public function resetPartialConcreteArticles($v = true): void
    {
        $this->collConcreteArticlesPartial = $v;
    }

    /**
     * Initializes the collConcreteArticles collection.
     *
     * By default this just sets the collConcreteArticles collection to an empty array (like clearcollConcreteArticles());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initConcreteArticles(bool $overrideExisting = true): void
    {
        if (null !== $this->collConcreteArticles && !$overrideExisting) {
            return;
        }

        $collectionClassName = ConcreteArticleTableMap::getTableMap()->getCollectionClassName();

        $this->collConcreteArticles = new $collectionClassName;
        $this->collConcreteArticles->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteArticle');
    }

    /**
     * Gets an array of ChildConcreteArticle objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildConcreteCategory is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildConcreteArticle[] List of ChildConcreteArticle objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteArticle> List of ChildConcreteArticle objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getConcreteArticles(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collConcreteArticlesPartial && !$this->isNew();
        if (null === $this->collConcreteArticles || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collConcreteArticles) {
                    $this->initConcreteArticles();
                } else {
                    $collectionClassName = ConcreteArticleTableMap::getTableMap()->getCollectionClassName();

                    $collConcreteArticles = new $collectionClassName;
                    $collConcreteArticles->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteArticle');

                    return $collConcreteArticles;
                }
            } else {
                $collConcreteArticles = ChildConcreteArticleQuery::create(null, $criteria)
                    ->filterByConcreteCategory($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collConcreteArticlesPartial && count($collConcreteArticles)) {
                        $this->initConcreteArticles(false);

                        foreach ($collConcreteArticles as $obj) {
                            if (false === $this->collConcreteArticles->contains($obj)) {
                                $this->collConcreteArticles->append($obj);
                            }
                        }

                        $this->collConcreteArticlesPartial = true;
                    }

                    return $collConcreteArticles;
                }

                if ($partial && $this->collConcreteArticles) {
                    foreach ($this->collConcreteArticles as $obj) {
                        if ($obj->isNew()) {
                            $collConcreteArticles[] = $obj;
                        }
                    }
                }

                $this->collConcreteArticles = $collConcreteArticles;
                $this->collConcreteArticlesPartial = false;
            }
        }

        return $this->collConcreteArticles;
    }

    /**
     * Sets a collection of ChildConcreteArticle objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $concreteArticles A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setConcreteArticles(Collection $concreteArticles, ?ConnectionInterface $con = null)
    {
        /** @var ChildConcreteArticle[] $concreteArticlesToDelete */
        $concreteArticlesToDelete = $this->getConcreteArticles(new Criteria(), $con)->diff($concreteArticles);


        $this->concreteArticlesScheduledForDeletion = $concreteArticlesToDelete;

        foreach ($concreteArticlesToDelete as $concreteArticleRemoved) {
            $concreteArticleRemoved->setConcreteCategory(null);
        }

        $this->collConcreteArticles = null;
        foreach ($concreteArticles as $concreteArticle) {
            $this->addConcreteArticle($concreteArticle);
        }

        $this->collConcreteArticles = $concreteArticles;
        $this->collConcreteArticlesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ConcreteArticle objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ConcreteArticle objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countConcreteArticles(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collConcreteArticlesPartial && !$this->isNew();
        if (null === $this->collConcreteArticles || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collConcreteArticles) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getConcreteArticles());
            }

            $query = ChildConcreteArticleQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByConcreteCategory($this)
                ->count($con);
        }

        return count($this->collConcreteArticles);
    }

    /**
     * Method called to associate a ChildConcreteArticle object to this object
     * through the ChildConcreteArticle foreign key attribute.
     *
     * @param ChildConcreteArticle $l ChildConcreteArticle
     * @return $this The current object (for fluent API support)
     */
    public function addConcreteArticle(ChildConcreteArticle $l)
    {
        if ($this->collConcreteArticles === null) {
            $this->initConcreteArticles();
            $this->collConcreteArticlesPartial = true;
        }

        if (!$this->collConcreteArticles->contains($l)) {
            $this->doAddConcreteArticle($l);

            if ($this->concreteArticlesScheduledForDeletion and $this->concreteArticlesScheduledForDeletion->contains($l)) {
                $this->concreteArticlesScheduledForDeletion->remove($this->concreteArticlesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildConcreteArticle $concreteArticle The ChildConcreteArticle object to add.
     */
    protected function doAddConcreteArticle(ChildConcreteArticle $concreteArticle): void
    {
        $this->collConcreteArticles[]= $concreteArticle;
        $concreteArticle->setConcreteCategory($this);
    }

    /**
     * @param ChildConcreteArticle $concreteArticle The ChildConcreteArticle object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeConcreteArticle(ChildConcreteArticle $concreteArticle)
    {
        if ($this->getConcreteArticles()->contains($concreteArticle)) {
            $pos = $this->collConcreteArticles->search($concreteArticle);
            $this->collConcreteArticles->remove($pos);
            if (null === $this->concreteArticlesScheduledForDeletion) {
                $this->concreteArticlesScheduledForDeletion = clone $this->collConcreteArticles;
                $this->concreteArticlesScheduledForDeletion->clear();
            }
            $this->concreteArticlesScheduledForDeletion[]= $concreteArticle;
            $concreteArticle->setConcreteCategory(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ConcreteCategory is new, it will return
     * an empty collection; or if this ConcreteCategory has previously
     * been saved, it will retrieve related ConcreteArticles from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ConcreteCategory.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildConcreteArticle[] List of ChildConcreteArticle objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteArticle> List of ChildConcreteArticle objects
     */
    public function getConcreteArticlesJoinConcreteAuthor(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildConcreteArticleQuery::create(null, $criteria);
        $query->joinWith('ConcreteAuthor', $joinBehavior);

        return $this->getConcreteArticles($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ConcreteCategory is new, it will return
     * an empty collection; or if this ConcreteCategory has previously
     * been saved, it will retrieve related ConcreteArticles from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ConcreteCategory.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildConcreteArticle[] List of ChildConcreteArticle objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteArticle> List of ChildConcreteArticle objects
     */
    public function getConcreteArticlesJoinConcreteContent(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildConcreteArticleQuery::create(null, $criteria);
        $query->joinWith('ConcreteContent', $joinBehavior);

        return $this->getConcreteArticles($query, $con);
    }

    /**
     * Clears out the collConcreteNewss collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addConcreteNewss()
     */
    public function clearConcreteNewss()
    {
        $this->collConcreteNewss = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collConcreteNewss collection loaded partially.
     *
     * @return void
     */
    public function resetPartialConcreteNewss($v = true): void
    {
        $this->collConcreteNewssPartial = $v;
    }

    /**
     * Initializes the collConcreteNewss collection.
     *
     * By default this just sets the collConcreteNewss collection to an empty array (like clearcollConcreteNewss());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initConcreteNewss(bool $overrideExisting = true): void
    {
        if (null !== $this->collConcreteNewss && !$overrideExisting) {
            return;
        }

        $collectionClassName = ConcreteNewsTableMap::getTableMap()->getCollectionClassName();

        $this->collConcreteNewss = new $collectionClassName;
        $this->collConcreteNewss->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteNews');
    }

    /**
     * Gets an array of ChildConcreteNews objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildConcreteCategory is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildConcreteNews[] List of ChildConcreteNews objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteNews> List of ChildConcreteNews objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getConcreteNewss(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collConcreteNewssPartial && !$this->isNew();
        if (null === $this->collConcreteNewss || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collConcreteNewss) {
                    $this->initConcreteNewss();
                } else {
                    $collectionClassName = ConcreteNewsTableMap::getTableMap()->getCollectionClassName();

                    $collConcreteNewss = new $collectionClassName;
                    $collConcreteNewss->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteNews');

                    return $collConcreteNewss;
                }
            } else {
                $collConcreteNewss = ChildConcreteNewsQuery::create(null, $criteria)
                    ->filterByConcreteCategory($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collConcreteNewssPartial && count($collConcreteNewss)) {
                        $this->initConcreteNewss(false);

                        foreach ($collConcreteNewss as $obj) {
                            if (false === $this->collConcreteNewss->contains($obj)) {
                                $this->collConcreteNewss->append($obj);
                            }
                        }

                        $this->collConcreteNewssPartial = true;
                    }

                    return $collConcreteNewss;
                }

                if ($partial && $this->collConcreteNewss) {
                    foreach ($this->collConcreteNewss as $obj) {
                        if ($obj->isNew()) {
                            $collConcreteNewss[] = $obj;
                        }
                    }
                }

                $this->collConcreteNewss = $collConcreteNewss;
                $this->collConcreteNewssPartial = false;
            }
        }

        return $this->collConcreteNewss;
    }

    /**
     * Sets a collection of ChildConcreteNews objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $concreteNewss A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setConcreteNewss(Collection $concreteNewss, ?ConnectionInterface $con = null)
    {
        /** @var ChildConcreteNews[] $concreteNewssToDelete */
        $concreteNewssToDelete = $this->getConcreteNewss(new Criteria(), $con)->diff($concreteNewss);


        $this->concreteNewssScheduledForDeletion = $concreteNewssToDelete;

        foreach ($concreteNewssToDelete as $concreteNewsRemoved) {
            $concreteNewsRemoved->setConcreteCategory(null);
        }

        $this->collConcreteNewss = null;
        foreach ($concreteNewss as $concreteNews) {
            $this->addConcreteNews($concreteNews);
        }

        $this->collConcreteNewss = $concreteNewss;
        $this->collConcreteNewssPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ConcreteNews objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ConcreteNews objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countConcreteNewss(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collConcreteNewssPartial && !$this->isNew();
        if (null === $this->collConcreteNewss || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collConcreteNewss) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getConcreteNewss());
            }

            $query = ChildConcreteNewsQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByConcreteCategory($this)
                ->count($con);
        }

        return count($this->collConcreteNewss);
    }

    /**
     * Method called to associate a ChildConcreteNews object to this object
     * through the ChildConcreteNews foreign key attribute.
     *
     * @param ChildConcreteNews $l ChildConcreteNews
     * @return $this The current object (for fluent API support)
     */
    public function addConcreteNews(ChildConcreteNews $l)
    {
        if ($this->collConcreteNewss === null) {
            $this->initConcreteNewss();
            $this->collConcreteNewssPartial = true;
        }

        if (!$this->collConcreteNewss->contains($l)) {
            $this->doAddConcreteNews($l);

            if ($this->concreteNewssScheduledForDeletion and $this->concreteNewssScheduledForDeletion->contains($l)) {
                $this->concreteNewssScheduledForDeletion->remove($this->concreteNewssScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildConcreteNews $concreteNews The ChildConcreteNews object to add.
     */
    protected function doAddConcreteNews(ChildConcreteNews $concreteNews): void
    {
        $this->collConcreteNewss[]= $concreteNews;
        $concreteNews->setConcreteCategory($this);
    }

    /**
     * @param ChildConcreteNews $concreteNews The ChildConcreteNews object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeConcreteNews(ChildConcreteNews $concreteNews)
    {
        if ($this->getConcreteNewss()->contains($concreteNews)) {
            $pos = $this->collConcreteNewss->search($concreteNews);
            $this->collConcreteNewss->remove($pos);
            if (null === $this->concreteNewssScheduledForDeletion) {
                $this->concreteNewssScheduledForDeletion = clone $this->collConcreteNewss;
                $this->concreteNewssScheduledForDeletion->clear();
            }
            $this->concreteNewssScheduledForDeletion[]= $concreteNews;
            $concreteNews->setConcreteCategory(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ConcreteCategory is new, it will return
     * an empty collection; or if this ConcreteCategory has previously
     * been saved, it will retrieve related ConcreteNewss from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ConcreteCategory.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildConcreteNews[] List of ChildConcreteNews objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteNews> List of ChildConcreteNews objects
     */
    public function getConcreteNewssJoinConcreteArticle(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildConcreteNewsQuery::create(null, $criteria);
        $query->joinWith('ConcreteArticle', $joinBehavior);

        return $this->getConcreteNewss($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ConcreteCategory is new, it will return
     * an empty collection; or if this ConcreteCategory has previously
     * been saved, it will retrieve related ConcreteNewss from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ConcreteCategory.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildConcreteNews[] List of ChildConcreteNews objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteNews> List of ChildConcreteNews objects
     */
    public function getConcreteNewssJoinConcreteAuthor(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildConcreteNewsQuery::create(null, $criteria);
        $query->joinWith('ConcreteAuthor', $joinBehavior);

        return $this->getConcreteNewss($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this ConcreteCategory is new, it will return
     * an empty collection; or if this ConcreteCategory has previously
     * been saved, it will retrieve related ConcreteNewss from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in ConcreteCategory.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildConcreteNews[] List of ChildConcreteNews objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteNews> List of ChildConcreteNews objects
     */
    public function getConcreteNewssJoinConcreteContent(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildConcreteNewsQuery::create(null, $criteria);
        $query->joinWith('ConcreteContent', $joinBehavior);

        return $this->getConcreteNewss($query, $con);
    }

    /**
     * Clears out the collConcreteQuizzs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addConcreteQuizzs()
     */
    public function clearConcreteQuizzs()
    {
        $this->collConcreteQuizzs = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collConcreteQuizzs collection loaded partially.
     *
     * @return void
     */
    public function resetPartialConcreteQuizzs($v = true): void
    {
        $this->collConcreteQuizzsPartial = $v;
    }

    /**
     * Initializes the collConcreteQuizzs collection.
     *
     * By default this just sets the collConcreteQuizzs collection to an empty array (like clearcollConcreteQuizzs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initConcreteQuizzs(bool $overrideExisting = true): void
    {
        if (null !== $this->collConcreteQuizzs && !$overrideExisting) {
            return;
        }

        $collectionClassName = ConcreteQuizzTableMap::getTableMap()->getCollectionClassName();

        $this->collConcreteQuizzs = new $collectionClassName;
        $this->collConcreteQuizzs->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteQuizz');
    }

    /**
     * Gets an array of ChildConcreteQuizz objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildConcreteCategory is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildConcreteQuizz[] List of ChildConcreteQuizz objects
     * @phpstan-return ObjectCollection&\Traversable<ChildConcreteQuizz> List of ChildConcreteQuizz objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getConcreteQuizzs(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collConcreteQuizzsPartial && !$this->isNew();
        if (null === $this->collConcreteQuizzs || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collConcreteQuizzs) {
                    $this->initConcreteQuizzs();
                } else {
                    $collectionClassName = ConcreteQuizzTableMap::getTableMap()->getCollectionClassName();

                    $collConcreteQuizzs = new $collectionClassName;
                    $collConcreteQuizzs->setModel('\Propel\Tests\Bookstore\Behavior\ConcreteQuizz');

                    return $collConcreteQuizzs;
                }
            } else {
                $collConcreteQuizzs = ChildConcreteQuizzQuery::create(null, $criteria)
                    ->filterByConcreteCategory($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collConcreteQuizzsPartial && count($collConcreteQuizzs)) {
                        $this->initConcreteQuizzs(false);

                        foreach ($collConcreteQuizzs as $obj) {
                            if (false === $this->collConcreteQuizzs->contains($obj)) {
                                $this->collConcreteQuizzs->append($obj);
                            }
                        }

                        $this->collConcreteQuizzsPartial = true;
                    }

                    return $collConcreteQuizzs;
                }

                if ($partial && $this->collConcreteQuizzs) {
                    foreach ($this->collConcreteQuizzs as $obj) {
                        if ($obj->isNew()) {
                            $collConcreteQuizzs[] = $obj;
                        }
                    }
                }

                $this->collConcreteQuizzs = $collConcreteQuizzs;
                $this->collConcreteQuizzsPartial = false;
            }
        }

        return $this->collConcreteQuizzs;
    }

    /**
     * Sets a collection of ChildConcreteQuizz objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $concreteQuizzs A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setConcreteQuizzs(Collection $concreteQuizzs, ?ConnectionInterface $con = null)
    {
        /** @var ChildConcreteQuizz[] $concreteQuizzsToDelete */
        $concreteQuizzsToDelete = $this->getConcreteQuizzs(new Criteria(), $con)->diff($concreteQuizzs);


        $this->concreteQuizzsScheduledForDeletion = $concreteQuizzsToDelete;

        foreach ($concreteQuizzsToDelete as $concreteQuizzRemoved) {
            $concreteQuizzRemoved->setConcreteCategory(null);
        }

        $this->collConcreteQuizzs = null;
        foreach ($concreteQuizzs as $concreteQuizz) {
            $this->addConcreteQuizz($concreteQuizz);
        }

        $this->collConcreteQuizzs = $concreteQuizzs;
        $this->collConcreteQuizzsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ConcreteQuizz objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ConcreteQuizz objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countConcreteQuizzs(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collConcreteQuizzsPartial && !$this->isNew();
        if (null === $this->collConcreteQuizzs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collConcreteQuizzs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getConcreteQuizzs());
            }

            $query = ChildConcreteQuizzQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByConcreteCategory($this)
                ->count($con);
        }

        return count($this->collConcreteQuizzs);
    }

    /**
     * Method called to associate a ChildConcreteQuizz object to this object
     * through the ChildConcreteQuizz foreign key attribute.
     *
     * @param ChildConcreteQuizz $l ChildConcreteQuizz
     * @return $this The current object (for fluent API support)
     */
    public function addConcreteQuizz(ChildConcreteQuizz $l)
    {
        if ($this->collConcreteQuizzs === null) {
            $this->initConcreteQuizzs();
            $this->collConcreteQuizzsPartial = true;
        }

        if (!$this->collConcreteQuizzs->contains($l)) {
            $this->doAddConcreteQuizz($l);

            if ($this->concreteQuizzsScheduledForDeletion and $this->concreteQuizzsScheduledForDeletion->contains($l)) {
                $this->concreteQuizzsScheduledForDeletion->remove($this->concreteQuizzsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildConcreteQuizz $concreteQuizz The ChildConcreteQuizz object to add.
     */
    protected function doAddConcreteQuizz(ChildConcreteQuizz $concreteQuizz): void
    {
        $this->collConcreteQuizzs[]= $concreteQuizz;
        $concreteQuizz->setConcreteCategory($this);
    }

    /**
     * @param ChildConcreteQuizz $concreteQuizz The ChildConcreteQuizz object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeConcreteQuizz(ChildConcreteQuizz $concreteQuizz)
    {
        if ($this->getConcreteQuizzs()->contains($concreteQuizz)) {
            $pos = $this->collConcreteQuizzs->search($concreteQuizz);
            $this->collConcreteQuizzs->remove($pos);
            if (null === $this->concreteQuizzsScheduledForDeletion) {
                $this->concreteQuizzsScheduledForDeletion = clone $this->collConcreteQuizzs;
                $this->concreteQuizzsScheduledForDeletion->clear();
            }
            $this->concreteQuizzsScheduledForDeletion[]= $concreteQuizz;
            $concreteQuizz->setConcreteCategory(null);
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
        $this->name = null;
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
            if ($this->collConcreteContents) {
                foreach ($this->collConcreteContents as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collConcreteArticles) {
                foreach ($this->collConcreteArticles as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collConcreteNewss) {
                foreach ($this->collConcreteNewss as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collConcreteQuizzs) {
                foreach ($this->collConcreteQuizzs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collConcreteContents = null;
        $this->collConcreteArticles = null;
        $this->collConcreteNewss = null;
        $this->collConcreteQuizzs = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string The value of the 'name' column
     */
    public function __toString(): string
    {
        return (string)$this->getName();
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
