<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Base;

use \Exception;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Tests\Bookstore\Contest as ChildContest;
use Propel\Tests\Bookstore\ContestQuery as ChildContestQuery;
use Propel\Tests\Bookstore\Country as ChildCountry;
use Propel\Tests\Bookstore\CountryQuery as ChildCountryQuery;
use Propel\Tests\Bookstore\CountryTranslation as ChildCountryTranslation;
use Propel\Tests\Bookstore\CountryTranslationQuery as ChildCountryTranslationQuery;
use Propel\Tests\Bookstore\Map\ContestTableMap;
use Propel\Tests\Bookstore\Map\CountryTableMap;
use Propel\Tests\Bookstore\Map\CountryTranslationTableMap;

/**
 * Base class that represents a row from the 'country' table.
 *
 *
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Base
 */
abstract class Country implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Map\\CountryTableMap';


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
     * The value for the code field.
     *
     * @var        string
     */
    protected ?string $code = null;

    /**
     * The value for the capital field.
     *
     * @var        string|null
     */
    protected ?string $capital = null;

    /**
     * @var        ObjectCollection|ChildContest[] Collection to store aggregation of ChildContest objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildContest> Collection to store aggregation of ChildContest objects.
     */
    protected $collContests;
    protected $collContestsPartial;

    /**
     * @var        ObjectCollection|ChildCountryTranslation[] Collection to store aggregation of ChildCountryTranslation objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildCountryTranslation> Collection to store aggregation of ChildCountryTranslation objects.
     */
    protected $collCountryTranslations;
    protected $collCountryTranslationsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildContest[]
     * @phpstan-var ObjectCollection&\Traversable<ChildContest>
     */
    protected $contestsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildCountryTranslation[]
     * @phpstan-var ObjectCollection&\Traversable<ChildCountryTranslation>
     */
    protected $countryTranslationsScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Base\Country object.
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
     * Compares this with another <code>Country</code> instance.  If
     * <code>obj</code> is an instance of <code>Country</code>, delegates to
     * <code>equals(Country)</code>.  Otherwise, returns <code>false</code>.
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
     * Get the [code] column value.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the [capital] column value.
     *
     * @return string|null
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set the value of [code] column.
     *
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    protected function setCode($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->code !== $v) {
            $this->code = $v;
            $this->modifiedColumns[CountryTableMap::COL_CODE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [capital] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    protected function setCapital($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->capital !== $v) {
            $this->capital = $v;
            $this->modifiedColumns[CountryTableMap::COL_CAPITAL] = true;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : CountryTableMap::translateFieldName('Code', TableMap::TYPE_PHPNAME, $indexType)];
            $this->code = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : CountryTableMap::translateFieldName('Capital', TableMap::TYPE_PHPNAME, $indexType)];
            $this->capital = (null !== $col) ? (string) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 2; // 2 = CountryTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Country'), 0, $e);
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
        $pos = (int)CountryTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getCode();

            case 1:
                return $this->getCapital();

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
        if (isset($alreadyDumpedObjects['Country'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['Country'][$this->hashCode()] = true;
        $keys = CountryTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getCode(),
            $keys[1] => $this->getCapital(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collContests) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'contests';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'contests';
                        break;
                    default:
                        $key = 'Contests';
                }

                $result[$key] = $this->collContests->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collCountryTranslations) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'countryTranslations';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'country_translations';
                        break;
                    default:
                        $key = 'CountryTranslations';
                }

                $result[$key] = $this->collCountryTranslations->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return \Propel\Runtime\ActiveQuery\Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria(): Criteria
    {
        $criteria = new Criteria(CountryTableMap::DATABASE_NAME);

        if ($this->isColumnModified(CountryTableMap::COL_CODE)) {
            $criteria->add(CountryTableMap::COL_CODE, $this->code);
        }
        if ($this->isColumnModified(CountryTableMap::COL_CAPITAL)) {
            $criteria->add(CountryTableMap::COL_CAPITAL, $this->capital);
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
        $criteria = ChildCountryQuery::create();
        $criteria->add(CountryTableMap::COL_CODE, $this->code);

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
        $validPk = null !== $this->getCode();

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
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getCode();
    }

    /**
     * Generic method to set the primary key (code column).
     *
     * @param string|null $key Primary key.
     * @return void
     */
    public function setPrimaryKey(?string $key = null): void
    {
        $this->setCode($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     *
     * @return bool
     */
    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getCode();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Country (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setCode($this->getCode());
        $copyObj->setCapital($this->getCapital());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getContests() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addContest($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getCountryTranslations() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCountryTranslation($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
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
     * @return \Propel\Tests\Bookstore\Country Clone of current object.
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
        if ('Contest' === $relationName) {
            $this->initContests();
            return;
        }
        if ('CountryTranslation' === $relationName) {
            $this->initCountryTranslations();
            return;
        }
    }

    /**
     * Clears out the collContests collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addContests()
     */
    public function clearContests()
    {
        $this->collContests = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collContests collection loaded partially.
     *
     * @return void
     */
    public function resetPartialContests($v = true): void
    {
        $this->collContestsPartial = $v;
    }

    /**
     * Initializes the collContests collection.
     *
     * By default this just sets the collContests collection to an empty array (like clearcollContests());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initContests(bool $overrideExisting = true): void
    {
        if (null !== $this->collContests && !$overrideExisting) {
            return;
        }

        $collectionClassName = ContestTableMap::getTableMap()->getCollectionClassName();

        $this->collContests = new $collectionClassName;
        $this->collContests->setModel('\Propel\Tests\Bookstore\Contest');
    }

    /**
     * Gets an array of ChildContest objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCountry is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildContest[] List of ChildContest objects
     * @phpstan-return ObjectCollection&\Traversable<ChildContest> List of ChildContest objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getContests(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collContestsPartial && !$this->isNew();
        if (null === $this->collContests || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collContests) {
                    $this->initContests();
                } else {
                    $collectionClassName = ContestTableMap::getTableMap()->getCollectionClassName();

                    $collContests = new $collectionClassName;
                    $collContests->setModel('\Propel\Tests\Bookstore\Contest');

                    return $collContests;
                }
            } else {
                $collContests = ChildContestQuery::create(null, $criteria)
                    ->filterByCountry($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collContestsPartial && count($collContests)) {
                        $this->initContests(false);

                        foreach ($collContests as $obj) {
                            if (false === $this->collContests->contains($obj)) {
                                $this->collContests->append($obj);
                            }
                        }

                        $this->collContestsPartial = true;
                    }

                    return $collContests;
                }

                if ($partial && $this->collContests) {
                    foreach ($this->collContests as $obj) {
                        if ($obj->isNew()) {
                            $collContests[] = $obj;
                        }
                    }
                }

                $this->collContests = $collContests;
                $this->collContestsPartial = false;
            }
        }

        return $this->collContests;
    }

    /**
     * Sets a collection of ChildContest objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $contests A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setContests(Collection $contests, ?ConnectionInterface $con = null)
    {
        /** @var ChildContest[] $contestsToDelete */
        $contestsToDelete = $this->getContests(new Criteria(), $con)->diff($contests);


        $this->contestsScheduledForDeletion = $contestsToDelete;

        foreach ($contestsToDelete as $contestRemoved) {
            $contestRemoved->setCountry(null);
        }

        $this->collContests = null;
        foreach ($contests as $contest) {
            $this->addContest($contest);
        }

        $this->collContests = $contests;
        $this->collContestsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Contest objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related Contest objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countContests(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collContestsPartial && !$this->isNew();
        if (null === $this->collContests || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collContests) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getContests());
            }

            $query = ChildContestQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCountry($this)
                ->count($con);
        }

        return count($this->collContests);
    }

    /**
     * Method called to associate a ChildContest object to this object
     * through the ChildContest foreign key attribute.
     *
     * @param ChildContest $l ChildContest
     * @return $this The current object (for fluent API support)
     */
    public function addContest(ChildContest $l)
    {
        if ($this->collContests === null) {
            $this->initContests();
            $this->collContestsPartial = true;
        }

        if (!$this->collContests->contains($l)) {
            $this->doAddContest($l);

            if ($this->contestsScheduledForDeletion and $this->contestsScheduledForDeletion->contains($l)) {
                $this->contestsScheduledForDeletion->remove($this->contestsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildContest $contest The ChildContest object to add.
     */
    protected function doAddContest(ChildContest $contest): void
    {
        $this->collContests[]= $contest;
        $contest->setCountry($this);
    }

    /**
     * @param ChildContest $contest The ChildContest object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeContest(ChildContest $contest)
    {
        if ($this->getContests()->contains($contest)) {
            $pos = $this->collContests->search($contest);
            $this->collContests->remove($pos);
            if (null === $this->contestsScheduledForDeletion) {
                $this->contestsScheduledForDeletion = clone $this->collContests;
                $this->contestsScheduledForDeletion->clear();
            }
            $this->contestsScheduledForDeletion[]= $contest;
            $contest->setCountry(null);
        }

        return $this;
    }

    /**
     * Clears out the collCountryTranslations collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addCountryTranslations()
     */
    public function clearCountryTranslations()
    {
        $this->collCountryTranslations = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collCountryTranslations collection loaded partially.
     *
     * @return void
     */
    public function resetPartialCountryTranslations($v = true): void
    {
        $this->collCountryTranslationsPartial = $v;
    }

    /**
     * Initializes the collCountryTranslations collection.
     *
     * By default this just sets the collCountryTranslations collection to an empty array (like clearcollCountryTranslations());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCountryTranslations(bool $overrideExisting = true): void
    {
        if (null !== $this->collCountryTranslations && !$overrideExisting) {
            return;
        }

        $collectionClassName = CountryTranslationTableMap::getTableMap()->getCollectionClassName();

        $this->collCountryTranslations = new $collectionClassName;
        $this->collCountryTranslations->setModel('\Propel\Tests\Bookstore\CountryTranslation');
    }

    /**
     * Gets an array of ChildCountryTranslation objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCountry is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildCountryTranslation[] List of ChildCountryTranslation objects
     * @phpstan-return ObjectCollection&\Traversable<ChildCountryTranslation> List of ChildCountryTranslation objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getCountryTranslations(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collCountryTranslationsPartial && !$this->isNew();
        if (null === $this->collCountryTranslations || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collCountryTranslations) {
                    $this->initCountryTranslations();
                } else {
                    $collectionClassName = CountryTranslationTableMap::getTableMap()->getCollectionClassName();

                    $collCountryTranslations = new $collectionClassName;
                    $collCountryTranslations->setModel('\Propel\Tests\Bookstore\CountryTranslation');

                    return $collCountryTranslations;
                }
            } else {
                $collCountryTranslations = ChildCountryTranslationQuery::create(null, $criteria)
                    ->filterByCountry($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCountryTranslationsPartial && count($collCountryTranslations)) {
                        $this->initCountryTranslations(false);

                        foreach ($collCountryTranslations as $obj) {
                            if (false === $this->collCountryTranslations->contains($obj)) {
                                $this->collCountryTranslations->append($obj);
                            }
                        }

                        $this->collCountryTranslationsPartial = true;
                    }

                    return $collCountryTranslations;
                }

                if ($partial && $this->collCountryTranslations) {
                    foreach ($this->collCountryTranslations as $obj) {
                        if ($obj->isNew()) {
                            $collCountryTranslations[] = $obj;
                        }
                    }
                }

                $this->collCountryTranslations = $collCountryTranslations;
                $this->collCountryTranslationsPartial = false;
            }
        }

        return $this->collCountryTranslations;
    }

    /**
     * Sets a collection of ChildCountryTranslation objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $countryTranslations A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setCountryTranslations(Collection $countryTranslations, ?ConnectionInterface $con = null)
    {
        /** @var ChildCountryTranslation[] $countryTranslationsToDelete */
        $countryTranslationsToDelete = $this->getCountryTranslations(new Criteria(), $con)->diff($countryTranslations);


        $this->countryTranslationsScheduledForDeletion = $countryTranslationsToDelete;

        foreach ($countryTranslationsToDelete as $countryTranslationRemoved) {
            $countryTranslationRemoved->setCountry(null);
        }

        $this->collCountryTranslations = null;
        foreach ($countryTranslations as $countryTranslation) {
            $this->addCountryTranslation($countryTranslation);
        }

        $this->collCountryTranslations = $countryTranslations;
        $this->collCountryTranslationsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CountryTranslation objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related CountryTranslation objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countCountryTranslations(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collCountryTranslationsPartial && !$this->isNew();
        if (null === $this->collCountryTranslations || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCountryTranslations) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCountryTranslations());
            }

            $query = ChildCountryTranslationQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCountry($this)
                ->count($con);
        }

        return count($this->collCountryTranslations);
    }

    /**
     * Method called to associate a ChildCountryTranslation object to this object
     * through the ChildCountryTranslation foreign key attribute.
     *
     * @param ChildCountryTranslation $l ChildCountryTranslation
     * @return $this The current object (for fluent API support)
     */
    public function addCountryTranslation(ChildCountryTranslation $l)
    {
        if ($this->collCountryTranslations === null) {
            $this->initCountryTranslations();
            $this->collCountryTranslationsPartial = true;
        }

        if (!$this->collCountryTranslations->contains($l)) {
            $this->doAddCountryTranslation($l);

            if ($this->countryTranslationsScheduledForDeletion and $this->countryTranslationsScheduledForDeletion->contains($l)) {
                $this->countryTranslationsScheduledForDeletion->remove($this->countryTranslationsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildCountryTranslation $countryTranslation The ChildCountryTranslation object to add.
     */
    protected function doAddCountryTranslation(ChildCountryTranslation $countryTranslation): void
    {
        $this->collCountryTranslations[]= $countryTranslation;
        $countryTranslation->setCountry($this);
    }

    /**
     * @param ChildCountryTranslation $countryTranslation The ChildCountryTranslation object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeCountryTranslation(ChildCountryTranslation $countryTranslation)
    {
        if ($this->getCountryTranslations()->contains($countryTranslation)) {
            $pos = $this->collCountryTranslations->search($countryTranslation);
            $this->collCountryTranslations->remove($pos);
            if (null === $this->countryTranslationsScheduledForDeletion) {
                $this->countryTranslationsScheduledForDeletion = clone $this->collCountryTranslations;
                $this->countryTranslationsScheduledForDeletion->clear();
            }
            $this->countryTranslationsScheduledForDeletion[]= $countryTranslation;
            $countryTranslation->setCountry(null);
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
        $this->code = null;
        $this->capital = null;
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
            if ($this->collContests) {
                foreach ($this->collContests as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collCountryTranslations) {
                foreach ($this->collCountryTranslations as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collContests = null;
        $this->collCountryTranslations = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CountryTableMap::DEFAULT_STRING_FORMAT);
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
