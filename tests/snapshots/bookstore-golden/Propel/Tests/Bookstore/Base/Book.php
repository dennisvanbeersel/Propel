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
use Propel\Tests\Bookstore\Author as ChildAuthor;
use Propel\Tests\Bookstore\AuthorQuery as ChildAuthorQuery;
use Propel\Tests\Bookstore\Book as ChildBook;
use Propel\Tests\Bookstore\BookClubList as ChildBookClubList;
use Propel\Tests\Bookstore\BookClubListQuery as ChildBookClubListQuery;
use Propel\Tests\Bookstore\BookListFavorite as ChildBookListFavorite;
use Propel\Tests\Bookstore\BookListFavoriteQuery as ChildBookListFavoriteQuery;
use Propel\Tests\Bookstore\BookListRel as ChildBookListRel;
use Propel\Tests\Bookstore\BookListRelQuery as ChildBookListRelQuery;
use Propel\Tests\Bookstore\BookOpinion as ChildBookOpinion;
use Propel\Tests\Bookstore\BookOpinionQuery as ChildBookOpinionQuery;
use Propel\Tests\Bookstore\BookQuery as ChildBookQuery;
use Propel\Tests\Bookstore\BookSummary as ChildBookSummary;
use Propel\Tests\Bookstore\BookSummaryQuery as ChildBookSummaryQuery;
use Propel\Tests\Bookstore\BookstoreContest as ChildBookstoreContest;
use Propel\Tests\Bookstore\BookstoreContestQuery as ChildBookstoreContestQuery;
use Propel\Tests\Bookstore\Media as ChildMedia;
use Propel\Tests\Bookstore\MediaQuery as ChildMediaQuery;
use Propel\Tests\Bookstore\PolymorphicRelationLog as ChildPolymorphicRelationLog;
use Propel\Tests\Bookstore\PolymorphicRelationLogQuery as ChildPolymorphicRelationLogQuery;
use Propel\Tests\Bookstore\Publisher as ChildPublisher;
use Propel\Tests\Bookstore\PublisherQuery as ChildPublisherQuery;
use Propel\Tests\Bookstore\ReaderFavorite as ChildReaderFavorite;
use Propel\Tests\Bookstore\ReaderFavoriteQuery as ChildReaderFavoriteQuery;
use Propel\Tests\Bookstore\Review as ChildReview;
use Propel\Tests\Bookstore\ReviewQuery as ChildReviewQuery;
use Propel\Tests\Bookstore\Map\BookListFavoriteTableMap;
use Propel\Tests\Bookstore\Map\BookListRelTableMap;
use Propel\Tests\Bookstore\Map\BookOpinionTableMap;
use Propel\Tests\Bookstore\Map\BookSummaryTableMap;
use Propel\Tests\Bookstore\Map\BookTableMap;
use Propel\Tests\Bookstore\Map\BookstoreContestTableMap;
use Propel\Tests\Bookstore\Map\MediaTableMap;
use Propel\Tests\Bookstore\Map\PolymorphicRelationLogTableMap;
use Propel\Tests\Bookstore\Map\ReaderFavoriteTableMap;
use Propel\Tests\Bookstore\Map\ReviewTableMap;

/**
 * Base class that represents a row from the 'book' table.
 *
 * Book Table
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Base
 */
abstract class Book implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Map\\BookTableMap';


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
     * Book Id
     * @var        int
     */
    protected ?int $id = null;

    /**
     * The value for the title field.
     * Book Title
     * @var        string
     */
    protected ?string $title = null;

    /**
     * The value for the isbn field.
     * ISBN Number
     * @var        string
     */
    protected ?string $isbn = null;

    /**
     * The value for the price field.
     * Price of the book.
     * @var        float|null
     */
    protected ?float $price = null;

    /**
     * The value for the publisher_id field.
     * Foreign Key Publisher
     * @var        int|null
     */
    protected ?int $publisher_id = null;

    /**
     * The value for the author_id field.
     * Foreign Key Author
     * @var        int|null
     */
    protected ?int $author_id = null;

    /**
     * @var        ChildPublisher|null
     */
    protected ?ChildPublisher $aPublisher = null;

    /**
     * @var        ChildAuthor|null
     */
    protected ?ChildAuthor $aAuthor = null;

    /**
     * @var        ObjectCollection|ChildBookSummary[] Collection to store aggregation of ChildBookSummary objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookSummary> Collection to store aggregation of ChildBookSummary objects.
     */
    protected $collBookSummaries;
    protected bool $collBookSummariesPartial = false;

    /**
     * @var        ObjectCollection|ChildReview[] Collection to store aggregation of ChildReview objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildReview> Collection to store aggregation of ChildReview objects.
     */
    protected $collReviews;
    protected bool $collReviewsPartial = false;

    /**
     * @var        ObjectCollection|ChildMedia[] Collection to store aggregation of ChildMedia objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildMedia> Collection to store aggregation of ChildMedia objects.
     */
    protected $collMedias;
    protected bool $collMediasPartial = false;

    /**
     * @var        ObjectCollection|ChildBookListRel[] Collection to store aggregation of ChildBookListRel objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookListRel> Collection to store aggregation of ChildBookListRel objects.
     */
    protected $collBookListRels;
    protected bool $collBookListRelsPartial = false;

    /**
     * @var        ObjectCollection|ChildBookListFavorite[] Collection to store aggregation of ChildBookListFavorite objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookListFavorite> Collection to store aggregation of ChildBookListFavorite objects.
     */
    protected $collBookListFavorites;
    protected bool $collBookListFavoritesPartial = false;

    /**
     * @var        ObjectCollection|ChildBookOpinion[] Collection to store aggregation of ChildBookOpinion objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookOpinion> Collection to store aggregation of ChildBookOpinion objects.
     */
    protected $collBookOpinions;
    protected bool $collBookOpinionsPartial = false;

    /**
     * @var        ObjectCollection|ChildReaderFavorite[] Collection to store aggregation of ChildReaderFavorite objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildReaderFavorite> Collection to store aggregation of ChildReaderFavorite objects.
     */
    protected $collReaderFavorites;
    protected bool $collReaderFavoritesPartial = false;

    /**
     * @var        ObjectCollection|ChildBookstoreContest[] Collection to store aggregation of ChildBookstoreContest objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreContest> Collection to store aggregation of ChildBookstoreContest objects.
     */
    protected $collBookstoreContests;
    protected bool $collBookstoreContestsPartial = false;

    /**
     * @var        ObjectCollection|ChildPolymorphicRelationLog[] Collection to store aggregation of ChildPolymorphicRelationLog objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildPolymorphicRelationLog> Collection to store aggregation of ChildPolymorphicRelationLog objects.
     */
    protected $collPolymorphicRelationLogs;
    protected bool $collPolymorphicRelationLogsPartial = false;

    /**
     * @var        ObjectCollection|ChildBookClubList[] Cross Collection to store aggregation of ChildBookClubList objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookClubList> Cross Collection to store aggregation of ChildBookClubList objects.
     */
    protected $collBookClubLists;

    /**
     * @var bool
     */
    protected $collBookClubListsPartial;

    /**
     * @var        ObjectCollection|ChildBookClubList[] Cross Collection to store aggregation of ChildBookClubList objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildBookClubList> Cross Collection to store aggregation of ChildBookClubList objects.
     */
    protected $collFavoriteBookClubLists;

    /**
     * @var bool
     */
    protected $collFavoriteBookClubListsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookClubList[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookClubList>
     */
    protected $bookClubListsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookClubList[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookClubList>
     */
    protected $favoriteBookClubListsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookSummary[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookSummary>
     */
    protected $bookSummariesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildReview[]
     * @phpstan-var ObjectCollection&\Traversable<ChildReview>
     */
    protected $reviewsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildMedia[]
     * @phpstan-var ObjectCollection&\Traversable<ChildMedia>
     */
    protected $mediasScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookListRel[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookListRel>
     */
    protected $bookListRelsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookListFavorite[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookListFavorite>
     */
    protected $bookListFavoritesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookOpinion[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookOpinion>
     */
    protected $bookOpinionsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildReaderFavorite[]
     * @phpstan-var ObjectCollection&\Traversable<ChildReaderFavorite>
     */
    protected $readerFavoritesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildBookstoreContest[]
     * @phpstan-var ObjectCollection&\Traversable<ChildBookstoreContest>
     */
    protected $bookstoreContestsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildPolymorphicRelationLog[]
     * @phpstan-var ObjectCollection&\Traversable<ChildPolymorphicRelationLog>
     */
    protected $polymorphicRelationLogsScheduledForDeletion = null;

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Base\Book object.
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
     * Compares this with another <code>Book</code> instance.  If
     * <code>obj</code> is an instance of <code>Book</code>, delegates to
     * <code>equals(Book)</code>.  Otherwise, returns <code>false</code>.
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
     * Book Id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [title] column value.
     * Book Title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the [isbn] column value.
     * ISBN Number
     * @return string
     */
    public function getISBN()
    {
        return $this->isbn;
    }

    /**
     * Get the [price] column value.
     * Price of the book.
     * @return float|null
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
     * Set the value of [id] column.
     * Book Id
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
            $this->modifiedColumns[BookTableMap::COL_ID] = true;
        }

        return $this;
    }

    /**
     * Set the value of [title] column.
     * Book Title
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setTitle($v): self
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->title !== $v) {
            $this->title = $v;
            $this->modifiedColumns[BookTableMap::COL_TITLE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [isbn] column.
     * ISBN Number
     * @param string $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setISBN($v): self
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->isbn !== $v) {
            $this->isbn = $v;
            $this->modifiedColumns[BookTableMap::COL_ISBN] = true;
        }

        return $this;
    }

    /**
     * Set the value of [price] column.
     * Price of the book.
     * @param float|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPrice($v): self
    {
        if ($v !== null) {
            $v = (float) $v;
        }

        if ($this->price !== $v) {
            $this->price = $v;
            $this->modifiedColumns[BookTableMap::COL_PRICE] = true;
        }

        return $this;
    }

    /**
     * Set the value of [publisher_id] column.
     * Foreign Key Publisher
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPublisherId($v): self
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->publisher_id !== $v) {
            $this->publisher_id = $v;
            $this->modifiedColumns[BookTableMap::COL_PUBLISHER_ID] = true;
        }

        if ($this->aPublisher !== null && $this->aPublisher->getId() !== $v) {
            $this->aPublisher = null;
        }

        return $this;
    }

    /**
     * Set the value of [author_id] column.
     * Foreign Key Author
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setAuthorId($v): self
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->author_id !== $v) {
            $this->author_id = $v;
            $this->modifiedColumns[BookTableMap::COL_AUTHOR_ID] = true;
        }

        if ($this->aAuthor !== null && $this->aAuthor->getId() !== $v) {
            $this->aAuthor = null;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : BookTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : BookTableMap::translateFieldName('Title', TableMap::TYPE_PHPNAME, $indexType)];
            $this->title = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : BookTableMap::translateFieldName('ISBN', TableMap::TYPE_PHPNAME, $indexType)];
            $this->isbn = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : BookTableMap::translateFieldName('Price', TableMap::TYPE_PHPNAME, $indexType)];
            $this->price = (null !== $col) ? (float) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : BookTableMap::translateFieldName('PublisherId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->publisher_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : BookTableMap::translateFieldName('AuthorId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->author_id = (null !== $col) ? (int) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 6; // 6 = BookTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\Book'), 0, $e);
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
        if ($this->aPublisher !== null && $this->publisher_id !== $this->aPublisher->getId()) {
            $this->aPublisher = null;
        }
        if ($this->aAuthor !== null && $this->author_id !== $this->aAuthor->getId()) {
            $this->aAuthor = null;
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
            $con = Propel::getServiceContainer()->getReadConnection(BookTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildBookQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aPublisher = null;
            $this->aAuthor = null;
            $this->collBookSummaries = null;

            $this->collReviews = null;

            $this->collMedias = null;

            $this->collBookListRels = null;

            $this->collBookListFavorites = null;

            $this->collBookOpinions = null;

            $this->collReaderFavorites = null;

            $this->collBookstoreContests = null;

            $this->collPolymorphicRelationLogs = null;

            $this->collBookClubLists = null;
            $this->collFavoriteBookClubLists = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see Book::setDeleted()
     * @see Book::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildBookQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
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
                BookTableMap::addInstanceToPool($this);
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

            if ($this->aPublisher !== null) {
                if ($this->aPublisher->isModified() || $this->aPublisher->isNew()) {
                    $affectedRows += $this->aPublisher->save($con);
                }
                $this->setPublisher($this->aPublisher);
            }

            if ($this->aAuthor !== null) {
                if ($this->aAuthor->isModified() || $this->aAuthor->isNew()) {
                    $affectedRows += $this->aAuthor->save($con);
                }
                $this->setAuthor($this->aAuthor);
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

            if ($this->bookClubListsScheduledForDeletion !== null) {
                if (!$this->bookClubListsScheduledForDeletion->isEmpty()) {
                    $pks = [];
                    foreach ($this->bookClubListsScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[0] = $this->getId();
                        $entryPk[1] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \Propel\Tests\Bookstore\BookListRelQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->bookClubListsScheduledForDeletion = null;
                }

            }

            if ($this->collBookClubLists) {
                foreach ($this->collBookClubLists as $bookClubList) {
                    if (!$bookClubList->isDeleted() && ($bookClubList->isNew() || $bookClubList->isModified())) {
                        $bookClubList->save($con);
                    }
                }
            }


            if ($this->favoriteBookClubListsScheduledForDeletion !== null) {
                if (!$this->favoriteBookClubListsScheduledForDeletion->isEmpty()) {
                    $pks = [];
                    foreach ($this->favoriteBookClubListsScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[0] = $this->getId();
                        $entryPk[1] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \Propel\Tests\Bookstore\BookListFavoriteQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->favoriteBookClubListsScheduledForDeletion = null;
                }

            }

            if ($this->collFavoriteBookClubLists) {
                foreach ($this->collFavoriteBookClubLists as $favoriteBookClubList) {
                    if (!$favoriteBookClubList->isDeleted() && ($favoriteBookClubList->isNew() || $favoriteBookClubList->isModified())) {
                        $favoriteBookClubList->save($con);
                    }
                }
            }


            if ($this->bookSummariesScheduledForDeletion !== null) {
                if (!$this->bookSummariesScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\BookSummaryQuery::create()
                        ->filterByPrimaryKeys($this->bookSummariesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookSummariesScheduledForDeletion = null;
                }
            }

            if ($this->collBookSummaries !== null) {
                foreach ($this->collBookSummaries as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->reviewsScheduledForDeletion !== null) {
                if (!$this->reviewsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\ReviewQuery::create()
                        ->filterByPrimaryKeys($this->reviewsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->reviewsScheduledForDeletion = null;
                }
            }

            if ($this->collReviews !== null) {
                foreach ($this->collReviews as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->mediasScheduledForDeletion !== null) {
                if (!$this->mediasScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\MediaQuery::create()
                        ->filterByPrimaryKeys($this->mediasScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->mediasScheduledForDeletion = null;
                }
            }

            if ($this->collMedias !== null) {
                foreach ($this->collMedias as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->bookListRelsScheduledForDeletion !== null) {
                if (!$this->bookListRelsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\BookListRelQuery::create()
                        ->filterByPrimaryKeys($this->bookListRelsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookListRelsScheduledForDeletion = null;
                }
            }

            if ($this->collBookListRels !== null) {
                foreach ($this->collBookListRels as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->bookListFavoritesScheduledForDeletion !== null) {
                if (!$this->bookListFavoritesScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\BookListFavoriteQuery::create()
                        ->filterByPrimaryKeys($this->bookListFavoritesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookListFavoritesScheduledForDeletion = null;
                }
            }

            if ($this->collBookListFavorites !== null) {
                foreach ($this->collBookListFavorites as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->bookOpinionsScheduledForDeletion !== null) {
                if (!$this->bookOpinionsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\BookOpinionQuery::create()
                        ->filterByPrimaryKeys($this->bookOpinionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->bookOpinionsScheduledForDeletion = null;
                }
            }

            if ($this->collBookOpinions !== null) {
                foreach ($this->collBookOpinions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->readerFavoritesScheduledForDeletion !== null) {
                if (!$this->readerFavoritesScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\ReaderFavoriteQuery::create()
                        ->filterByPrimaryKeys($this->readerFavoritesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->readerFavoritesScheduledForDeletion = null;
                }
            }

            if ($this->collReaderFavorites !== null) {
                foreach ($this->collReaderFavorites as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->bookstoreContestsScheduledForDeletion !== null) {
                if (!$this->bookstoreContestsScheduledForDeletion->isEmpty()) {
                    foreach ($this->bookstoreContestsScheduledForDeletion as $bookstoreContest) {
                        // need to save related object because we set the relation to null
                        $bookstoreContest->save($con);
                    }
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

        $this->modifiedColumns[BookTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . BookTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(BookTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(BookTableMap::COL_TITLE)) {
            $modifiedColumns[':p' . $index++]  = 'title';
        }
        if ($this->isColumnModified(BookTableMap::COL_ISBN)) {
            $modifiedColumns[':p' . $index++]  = 'isbn';
        }
        if ($this->isColumnModified(BookTableMap::COL_PRICE)) {
            $modifiedColumns[':p' . $index++]  = 'price';
        }
        if ($this->isColumnModified(BookTableMap::COL_PUBLISHER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'publisher_id';
        }
        if ($this->isColumnModified(BookTableMap::COL_AUTHOR_ID)) {
            $modifiedColumns[':p' . $index++]  = 'author_id';
        }

        $sql = sprintf(
            'INSERT INTO book (%s) VALUES (%s)',
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
        $pos = (int)BookTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getISBN();

            case 3:
                return $this->getPrice();

            case 4:
                return $this->getPublisherId();

            case 5:
                return $this->getAuthorId();

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
        if (isset($alreadyDumpedObjects['Book'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['Book'][$this->hashCode()] = true;
        $keys = BookTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getId(),
            $keys[1] => $this->getTitle(),
            $keys[2] => $this->getISBN(),
            $keys[3] => $this->getPrice(),
            $keys[4] => $this->getPublisherId(),
            $keys[5] => $this->getAuthorId(),
        ];
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aPublisher) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'publisher';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'publisher';
                        break;
                    default:
                        $key = 'Publisher';
                }

                $result[$key] = $this->aPublisher->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthor) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'author';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'author';
                        break;
                    default:
                        $key = 'Author';
                }

                $result[$key] = $this->aAuthor->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collBookSummaries) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookSummaries';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'book_summaries';
                        break;
                    default:
                        $key = 'BookSummaries';
                }

                $result[$key] = $this->collBookSummaries->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collReviews) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'reviews';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'reviews';
                        break;
                    default:
                        $key = 'Reviews';
                }

                $result[$key] = $this->collReviews->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMedias) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'medias';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'medias';
                        break;
                    default:
                        $key = 'Medias';
                }

                $result[$key] = $this->collMedias->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBookListRels) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookListRels';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'book_x_lists';
                        break;
                    default:
                        $key = 'BookListRels';
                }

                $result[$key] = $this->collBookListRels->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBookListFavorites) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookListFavorites';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'book_club_list_favorite_bookss';
                        break;
                    default:
                        $key = 'BookListFavorites';
                }

                $result[$key] = $this->collBookListFavorites->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBookOpinions) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookOpinions';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'book_opinions';
                        break;
                    default:
                        $key = 'BookOpinions';
                }

                $result[$key] = $this->collBookOpinions->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collReaderFavorites) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'readerFavorites';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'reader_favorites';
                        break;
                    default:
                        $key = 'ReaderFavorites';
                }

                $result[$key] = $this->collReaderFavorites->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = (int)BookTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setISBN($value);
                break;
            case 3:
                $this->setPrice($value);
                break;
            case 4:
                $this->setPublisherId($value);
                break;
            case 5:
                $this->setAuthorId($value);
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
        $keys = BookTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setTitle($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setISBN($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setPrice($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setPublisherId($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setAuthorId($arr[$keys[5]]);
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
        $criteria = new Criteria(BookTableMap::DATABASE_NAME);

        if ($this->isColumnModified(BookTableMap::COL_ID)) {
            $criteria->add(BookTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(BookTableMap::COL_TITLE)) {
            $criteria->add(BookTableMap::COL_TITLE, $this->title);
        }
        if ($this->isColumnModified(BookTableMap::COL_ISBN)) {
            $criteria->add(BookTableMap::COL_ISBN, $this->isbn);
        }
        if ($this->isColumnModified(BookTableMap::COL_PRICE)) {
            $criteria->add(BookTableMap::COL_PRICE, $this->price);
        }
        if ($this->isColumnModified(BookTableMap::COL_PUBLISHER_ID)) {
            $criteria->add(BookTableMap::COL_PUBLISHER_ID, $this->publisher_id);
        }
        if ($this->isColumnModified(BookTableMap::COL_AUTHOR_ID)) {
            $criteria->add(BookTableMap::COL_AUTHOR_ID, $this->author_id);
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
        $criteria = ChildBookQuery::create();
        $criteria->add(BookTableMap::COL_ID, $this->id);

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
     * @param object $copyObj An object of \Propel\Tests\Bookstore\Book (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setTitle($this->getTitle());
        $copyObj->setISBN($this->getISBN());
        $copyObj->setPrice($this->getPrice());
        $copyObj->setPublisherId($this->getPublisherId());
        $copyObj->setAuthorId($this->getAuthorId());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getBookSummaries() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookSummary($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getReviews() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addReview($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMedias() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMedia($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBookListRels() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookListRel($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBookListFavorites() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookListFavorite($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBookOpinions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookOpinion($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getReaderFavorites() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addReaderFavorite($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBookstoreContests() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBookstoreContest($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\Book Clone of current object.
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
     * Declares an association between this object and a ChildPublisher object.
     *
     * @param ChildPublisher|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setPublisher(?ChildPublisher $v = null): self
    {
        if ($v === null) {
            $this->setPublisherId(NULL);
        } else {
            $this->setPublisherId($v->getId());
        }

        $this->aPublisher = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildPublisher object, it will not be re-added.
        if ($v !== null) {
            $v->addBook($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildPublisher object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildPublisher|null The associated ChildPublisher object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getPublisher(?ConnectionInterface $con = null): ?ChildPublisher
    {
        if ($this->aPublisher === null && ($this->publisher_id !== null)) {
            $this->aPublisher = ChildPublisherQuery::create()->findPk($this->publisher_id, $con);
        }

        return $this->aPublisher;
    }

    /**
     * Declares an association between this object and a ChildAuthor object.
     *
     * @param ChildAuthor|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setAuthor(?ChildAuthor $v = null): self
    {
        if ($v === null) {
            $this->setAuthorId(NULL);
        } else {
            $this->setAuthorId($v->getId());
        }

        $this->aAuthor = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildAuthor object, it will not be re-added.
        if ($v !== null) {
            $v->addBook($this);
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
    public function getAuthor(?ConnectionInterface $con = null): ?ChildAuthor
    {
        if ($this->aAuthor === null && ($this->author_id !== null)) {
            $this->aAuthor = ChildAuthorQuery::create()->findPk($this->author_id, $con);
        }

        return $this->aAuthor;
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
        if ('BookSummary' === $relationName) {
            $this->initBookSummaries();
            return;
        }
        if ('Review' === $relationName) {
            $this->initReviews();
            return;
        }
        if ('Media' === $relationName) {
            $this->initMedias();
            return;
        }
        if ('BookListRel' === $relationName) {
            $this->initBookListRels();
            return;
        }
        if ('BookListFavorite' === $relationName) {
            $this->initBookListFavorites();
            return;
        }
        if ('BookOpinion' === $relationName) {
            $this->initBookOpinions();
            return;
        }
        if ('ReaderFavorite' === $relationName) {
            $this->initReaderFavorites();
            return;
        }
        if ('BookstoreContest' === $relationName) {
            $this->initBookstoreContests();
            return;
        }
        if ('PolymorphicRelationLog' === $relationName) {
            $this->initPolymorphicRelationLogs();
            return;
        }
    }

    /**
     * Clears out the collBookSummaries collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBookSummaries()
     */
    public function clearBookSummaries()
    {
        $this->collBookSummaries = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBookSummaries collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBookSummaries($v = true): void
    {
        $this->collBookSummariesPartial = $v;
    }

    /**
     * Initializes the collBookSummaries collection.
     *
     * By default this just sets the collBookSummaries collection to an empty array (like clearcollBookSummaries());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookSummaries(bool $overrideExisting = true): void
    {
        if (null !== $this->collBookSummaries && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookSummaryTableMap::getTableMap()->getCollectionClassName();

        $this->collBookSummaries = new $collectionClassName;
        $this->collBookSummaries->setModel('\Propel\Tests\Bookstore\BookSummary');
    }

    /**
     * Gets an array of ChildBookSummary objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBookSummary[] List of ChildBookSummary objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookSummary> List of ChildBookSummary objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookSummaries(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookSummariesPartial && !$this->isNew();
        if (null === $this->collBookSummaries || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookSummaries) {
                    $this->initBookSummaries();
                } else {
                    $collectionClassName = BookSummaryTableMap::getTableMap()->getCollectionClassName();

                    $collBookSummaries = new $collectionClassName;
                    $collBookSummaries->setModel('\Propel\Tests\Bookstore\BookSummary');

                    return $collBookSummaries;
                }
            } else {
                $collBookSummaries = ChildBookSummaryQuery::create(null, $criteria)
                    ->filterBySummarizedBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBookSummariesPartial && count($collBookSummaries)) {
                        $this->initBookSummaries(false);

                        foreach ($collBookSummaries as $obj) {
                            if (false === $this->collBookSummaries->contains($obj)) {
                                $this->collBookSummaries->append($obj);
                            }
                        }

                        $this->collBookSummariesPartial = true;
                    }

                    return $collBookSummaries;
                }

                if ($partial && $this->collBookSummaries) {
                    foreach ($this->collBookSummaries as $obj) {
                        if ($obj->isNew()) {
                            $collBookSummaries[] = $obj;
                        }
                    }
                }

                $this->collBookSummaries = $collBookSummaries;
                $this->collBookSummariesPartial = false;
            }
        }

        return $this->collBookSummaries;
    }

    /**
     * Sets a collection of ChildBookSummary objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookSummaries A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookSummaries(Collection $bookSummaries, ?ConnectionInterface $con = null)
    {
        /** @var ChildBookSummary[] $bookSummariesToDelete */
        $bookSummariesToDelete = $this->getBookSummaries(new Criteria(), $con)->diff($bookSummaries);


        $this->bookSummariesScheduledForDeletion = $bookSummariesToDelete;

        foreach ($bookSummariesToDelete as $bookSummaryRemoved) {
            $bookSummaryRemoved->setSummarizedBook(null);
        }

        $this->collBookSummaries = null;
        foreach ($bookSummaries as $bookSummary) {
            $this->addBookSummary($bookSummary);
        }

        $this->collBookSummaries = $bookSummaries;
        $this->collBookSummariesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookSummary objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related BookSummary objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBookSummaries(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookSummariesPartial && !$this->isNew();
        if (null === $this->collBookSummaries || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookSummaries) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBookSummaries());
            }

            $query = ChildBookSummaryQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterBySummarizedBook($this)
                ->count($con);
        }

        return count($this->collBookSummaries);
    }

    /**
     * Method called to associate a ChildBookSummary object to this object
     * through the ChildBookSummary foreign key attribute.
     *
     * @param ChildBookSummary $l ChildBookSummary
     * @return $this The current object (for fluent API support)
     */
    public function addBookSummary(ChildBookSummary $l)
    {
        if ($this->collBookSummaries === null) {
            $this->initBookSummaries();
            $this->collBookSummariesPartial = true;
        }

        if (!$this->collBookSummaries->contains($l)) {
            $this->doAddBookSummary($l);

            if ($this->bookSummariesScheduledForDeletion and $this->bookSummariesScheduledForDeletion->contains($l)) {
                $this->bookSummariesScheduledForDeletion->remove($this->bookSummariesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBookSummary $bookSummary The ChildBookSummary object to add.
     */
    protected function doAddBookSummary(ChildBookSummary $bookSummary): void
    {
        $this->collBookSummaries[]= $bookSummary;
        $bookSummary->setSummarizedBook($this);
    }

    /**
     * @param ChildBookSummary $bookSummary The ChildBookSummary object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBookSummary(ChildBookSummary $bookSummary)
    {
        if ($this->getBookSummaries()->contains($bookSummary)) {
            $pos = $this->collBookSummaries->search($bookSummary);
            $this->collBookSummaries->remove($pos);
            if (null === $this->bookSummariesScheduledForDeletion) {
                $this->bookSummariesScheduledForDeletion = clone $this->collBookSummaries;
                $this->bookSummariesScheduledForDeletion->clear();
            }
            $this->bookSummariesScheduledForDeletion[]= clone $bookSummary;
            $bookSummary->setSummarizedBook(null);
        }

        return $this;
    }

    /**
     * Clears out the collReviews collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addReviews()
     */
    public function clearReviews()
    {
        $this->collReviews = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collReviews collection loaded partially.
     *
     * @return void
     */
    public function resetPartialReviews($v = true): void
    {
        $this->collReviewsPartial = $v;
    }

    /**
     * Initializes the collReviews collection.
     *
     * By default this just sets the collReviews collection to an empty array (like clearcollReviews());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initReviews(bool $overrideExisting = true): void
    {
        if (null !== $this->collReviews && !$overrideExisting) {
            return;
        }

        $collectionClassName = ReviewTableMap::getTableMap()->getCollectionClassName();

        $this->collReviews = new $collectionClassName;
        $this->collReviews->setModel('\Propel\Tests\Bookstore\Review');
    }

    /**
     * Gets an array of ChildReview objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildReview[] List of ChildReview objects
     * @phpstan-return ObjectCollection&\Traversable<ChildReview> List of ChildReview objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getReviews(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collReviewsPartial && !$this->isNew();
        if (null === $this->collReviews || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collReviews) {
                    $this->initReviews();
                } else {
                    $collectionClassName = ReviewTableMap::getTableMap()->getCollectionClassName();

                    $collReviews = new $collectionClassName;
                    $collReviews->setModel('\Propel\Tests\Bookstore\Review');

                    return $collReviews;
                }
            } else {
                $collReviews = ChildReviewQuery::create(null, $criteria)
                    ->filterByBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collReviewsPartial && count($collReviews)) {
                        $this->initReviews(false);

                        foreach ($collReviews as $obj) {
                            if (false === $this->collReviews->contains($obj)) {
                                $this->collReviews->append($obj);
                            }
                        }

                        $this->collReviewsPartial = true;
                    }

                    return $collReviews;
                }

                if ($partial && $this->collReviews) {
                    foreach ($this->collReviews as $obj) {
                        if ($obj->isNew()) {
                            $collReviews[] = $obj;
                        }
                    }
                }

                $this->collReviews = $collReviews;
                $this->collReviewsPartial = false;
            }
        }

        return $this->collReviews;
    }

    /**
     * Sets a collection of ChildReview objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $reviews A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setReviews(Collection $reviews, ?ConnectionInterface $con = null)
    {
        /** @var ChildReview[] $reviewsToDelete */
        $reviewsToDelete = $this->getReviews(new Criteria(), $con)->diff($reviews);


        $this->reviewsScheduledForDeletion = $reviewsToDelete;

        foreach ($reviewsToDelete as $reviewRemoved) {
            $reviewRemoved->setBook(null);
        }

        $this->collReviews = null;
        foreach ($reviews as $review) {
            $this->addReview($review);
        }

        $this->collReviews = $reviews;
        $this->collReviewsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Review objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related Review objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countReviews(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collReviewsPartial && !$this->isNew();
        if (null === $this->collReviews || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collReviews) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getReviews());
            }

            $query = ChildReviewQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBook($this)
                ->count($con);
        }

        return count($this->collReviews);
    }

    /**
     * Method called to associate a ChildReview object to this object
     * through the ChildReview foreign key attribute.
     *
     * @param ChildReview $l ChildReview
     * @return $this The current object (for fluent API support)
     */
    public function addReview(ChildReview $l)
    {
        if ($this->collReviews === null) {
            $this->initReviews();
            $this->collReviewsPartial = true;
        }

        if (!$this->collReviews->contains($l)) {
            $this->doAddReview($l);

            if ($this->reviewsScheduledForDeletion and $this->reviewsScheduledForDeletion->contains($l)) {
                $this->reviewsScheduledForDeletion->remove($this->reviewsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildReview $review The ChildReview object to add.
     */
    protected function doAddReview(ChildReview $review): void
    {
        $this->collReviews[]= $review;
        $review->setBook($this);
    }

    /**
     * @param ChildReview $review The ChildReview object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeReview(ChildReview $review)
    {
        if ($this->getReviews()->contains($review)) {
            $pos = $this->collReviews->search($review);
            $this->collReviews->remove($pos);
            if (null === $this->reviewsScheduledForDeletion) {
                $this->reviewsScheduledForDeletion = clone $this->collReviews;
                $this->reviewsScheduledForDeletion->clear();
            }
            $this->reviewsScheduledForDeletion[]= $review;
            $review->setBook(null);
        }

        return $this;
    }

    /**
     * Clears out the collMedias collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addMedias()
     */
    public function clearMedias()
    {
        $this->collMedias = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collMedias collection loaded partially.
     *
     * @return void
     */
    public function resetPartialMedias($v = true): void
    {
        $this->collMediasPartial = $v;
    }

    /**
     * Initializes the collMedias collection.
     *
     * By default this just sets the collMedias collection to an empty array (like clearcollMedias());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMedias(bool $overrideExisting = true): void
    {
        if (null !== $this->collMedias && !$overrideExisting) {
            return;
        }

        $collectionClassName = MediaTableMap::getTableMap()->getCollectionClassName();

        $this->collMedias = new $collectionClassName;
        $this->collMedias->setModel('\Propel\Tests\Bookstore\Media');
    }

    /**
     * Gets an array of ChildMedia objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildMedia[] List of ChildMedia objects
     * @phpstan-return ObjectCollection&\Traversable<ChildMedia> List of ChildMedia objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getMedias(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collMediasPartial && !$this->isNew();
        if (null === $this->collMedias || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collMedias) {
                    $this->initMedias();
                } else {
                    $collectionClassName = MediaTableMap::getTableMap()->getCollectionClassName();

                    $collMedias = new $collectionClassName;
                    $collMedias->setModel('\Propel\Tests\Bookstore\Media');

                    return $collMedias;
                }
            } else {
                $collMedias = ChildMediaQuery::create(null, $criteria)
                    ->filterByBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collMediasPartial && count($collMedias)) {
                        $this->initMedias(false);

                        foreach ($collMedias as $obj) {
                            if (false === $this->collMedias->contains($obj)) {
                                $this->collMedias->append($obj);
                            }
                        }

                        $this->collMediasPartial = true;
                    }

                    return $collMedias;
                }

                if ($partial && $this->collMedias) {
                    foreach ($this->collMedias as $obj) {
                        if ($obj->isNew()) {
                            $collMedias[] = $obj;
                        }
                    }
                }

                $this->collMedias = $collMedias;
                $this->collMediasPartial = false;
            }
        }

        return $this->collMedias;
    }

    /**
     * Sets a collection of ChildMedia objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $medias A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setMedias(Collection $medias, ?ConnectionInterface $con = null)
    {
        /** @var ChildMedia[] $mediasToDelete */
        $mediasToDelete = $this->getMedias(new Criteria(), $con)->diff($medias);


        $this->mediasScheduledForDeletion = $mediasToDelete;

        foreach ($mediasToDelete as $mediaRemoved) {
            $mediaRemoved->setBook(null);
        }

        $this->collMedias = null;
        foreach ($medias as $media) {
            $this->addMedia($media);
        }

        $this->collMedias = $medias;
        $this->collMediasPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Media objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related Media objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countMedias(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collMediasPartial && !$this->isNew();
        if (null === $this->collMedias || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMedias) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMedias());
            }

            $query = ChildMediaQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBook($this)
                ->count($con);
        }

        return count($this->collMedias);
    }

    /**
     * Method called to associate a ChildMedia object to this object
     * through the ChildMedia foreign key attribute.
     *
     * @param ChildMedia $l ChildMedia
     * @return $this The current object (for fluent API support)
     */
    public function addMedia(ChildMedia $l)
    {
        if ($this->collMedias === null) {
            $this->initMedias();
            $this->collMediasPartial = true;
        }

        if (!$this->collMedias->contains($l)) {
            $this->doAddMedia($l);

            if ($this->mediasScheduledForDeletion and $this->mediasScheduledForDeletion->contains($l)) {
                $this->mediasScheduledForDeletion->remove($this->mediasScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildMedia $media The ChildMedia object to add.
     */
    protected function doAddMedia(ChildMedia $media): void
    {
        $this->collMedias[]= $media;
        $media->setBook($this);
    }

    /**
     * @param ChildMedia $media The ChildMedia object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeMedia(ChildMedia $media)
    {
        if ($this->getMedias()->contains($media)) {
            $pos = $this->collMedias->search($media);
            $this->collMedias->remove($pos);
            if (null === $this->mediasScheduledForDeletion) {
                $this->mediasScheduledForDeletion = clone $this->collMedias;
                $this->mediasScheduledForDeletion->clear();
            }
            $this->mediasScheduledForDeletion[]= clone $media;
            $media->setBook(null);
        }

        return $this;
    }

    /**
     * Clears out the collBookListRels collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBookListRels()
     */
    public function clearBookListRels()
    {
        $this->collBookListRels = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBookListRels collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBookListRels($v = true): void
    {
        $this->collBookListRelsPartial = $v;
    }

    /**
     * Initializes the collBookListRels collection.
     *
     * By default this just sets the collBookListRels collection to an empty array (like clearcollBookListRels());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookListRels(bool $overrideExisting = true): void
    {
        if (null !== $this->collBookListRels && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookListRelTableMap::getTableMap()->getCollectionClassName();

        $this->collBookListRels = new $collectionClassName;
        $this->collBookListRels->setModel('\Propel\Tests\Bookstore\BookListRel');
    }

    /**
     * Gets an array of ChildBookListRel objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBookListRel[] List of ChildBookListRel objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookListRel> List of ChildBookListRel objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookListRels(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookListRelsPartial && !$this->isNew();
        if (null === $this->collBookListRels || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookListRels) {
                    $this->initBookListRels();
                } else {
                    $collectionClassName = BookListRelTableMap::getTableMap()->getCollectionClassName();

                    $collBookListRels = new $collectionClassName;
                    $collBookListRels->setModel('\Propel\Tests\Bookstore\BookListRel');

                    return $collBookListRels;
                }
            } else {
                $collBookListRels = ChildBookListRelQuery::create(null, $criteria)
                    ->filterByBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBookListRelsPartial && count($collBookListRels)) {
                        $this->initBookListRels(false);

                        foreach ($collBookListRels as $obj) {
                            if (false === $this->collBookListRels->contains($obj)) {
                                $this->collBookListRels->append($obj);
                            }
                        }

                        $this->collBookListRelsPartial = true;
                    }

                    return $collBookListRels;
                }

                if ($partial && $this->collBookListRels) {
                    foreach ($this->collBookListRels as $obj) {
                        if ($obj->isNew()) {
                            $collBookListRels[] = $obj;
                        }
                    }
                }

                $this->collBookListRels = $collBookListRels;
                $this->collBookListRelsPartial = false;
            }
        }

        return $this->collBookListRels;
    }

    /**
     * Sets a collection of ChildBookListRel objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookListRels A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookListRels(Collection $bookListRels, ?ConnectionInterface $con = null)
    {
        /** @var ChildBookListRel[] $bookListRelsToDelete */
        $bookListRelsToDelete = $this->getBookListRels(new Criteria(), $con)->diff($bookListRels);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->bookListRelsScheduledForDeletion = clone $bookListRelsToDelete;

        foreach ($bookListRelsToDelete as $bookListRelRemoved) {
            $bookListRelRemoved->setBook(null);
        }

        $this->collBookListRels = null;
        foreach ($bookListRels as $bookListRel) {
            $this->addBookListRel($bookListRel);
        }

        $this->collBookListRels = $bookListRels;
        $this->collBookListRelsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookListRel objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related BookListRel objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBookListRels(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookListRelsPartial && !$this->isNew();
        if (null === $this->collBookListRels || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookListRels) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBookListRels());
            }

            $query = ChildBookListRelQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBook($this)
                ->count($con);
        }

        return count($this->collBookListRels);
    }

    /**
     * Method called to associate a ChildBookListRel object to this object
     * through the ChildBookListRel foreign key attribute.
     *
     * @param ChildBookListRel $l ChildBookListRel
     * @return $this The current object (for fluent API support)
     */
    public function addBookListRel(ChildBookListRel $l)
    {
        if ($this->collBookListRels === null) {
            $this->initBookListRels();
            $this->collBookListRelsPartial = true;
        }

        if (!$this->collBookListRels->contains($l)) {
            $this->doAddBookListRel($l);

            if ($this->bookListRelsScheduledForDeletion and $this->bookListRelsScheduledForDeletion->contains($l)) {
                $this->bookListRelsScheduledForDeletion->remove($this->bookListRelsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBookListRel $bookListRel The ChildBookListRel object to add.
     */
    protected function doAddBookListRel(ChildBookListRel $bookListRel): void
    {
        $this->collBookListRels[]= $bookListRel;
        $bookListRel->setBook($this);
    }

    /**
     * @param ChildBookListRel $bookListRel The ChildBookListRel object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBookListRel(ChildBookListRel $bookListRel)
    {
        if ($this->getBookListRels()->contains($bookListRel)) {
            $pos = $this->collBookListRels->search($bookListRel);
            $this->collBookListRels->remove($pos);
            if (null === $this->bookListRelsScheduledForDeletion) {
                $this->bookListRelsScheduledForDeletion = clone $this->collBookListRels;
                $this->bookListRelsScheduledForDeletion->clear();
            }
            $this->bookListRelsScheduledForDeletion[]= clone $bookListRel;
            $bookListRel->setBook(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related BookListRels from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookListRel[] List of ChildBookListRel objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookListRel> List of ChildBookListRel objects
     */
    public function getBookListRelsJoinBookClubList(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookListRelQuery::create(null, $criteria);
        $query->joinWith('BookClubList', $joinBehavior);

        return $this->getBookListRels($query, $con);
    }

    /**
     * Clears out the collBookListFavorites collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBookListFavorites()
     */
    public function clearBookListFavorites()
    {
        $this->collBookListFavorites = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBookListFavorites collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBookListFavorites($v = true): void
    {
        $this->collBookListFavoritesPartial = $v;
    }

    /**
     * Initializes the collBookListFavorites collection.
     *
     * By default this just sets the collBookListFavorites collection to an empty array (like clearcollBookListFavorites());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookListFavorites(bool $overrideExisting = true): void
    {
        if (null !== $this->collBookListFavorites && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookListFavoriteTableMap::getTableMap()->getCollectionClassName();

        $this->collBookListFavorites = new $collectionClassName;
        $this->collBookListFavorites->setModel('\Propel\Tests\Bookstore\BookListFavorite');
    }

    /**
     * Gets an array of ChildBookListFavorite objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBookListFavorite[] List of ChildBookListFavorite objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookListFavorite> List of ChildBookListFavorite objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookListFavorites(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookListFavoritesPartial && !$this->isNew();
        if (null === $this->collBookListFavorites || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookListFavorites) {
                    $this->initBookListFavorites();
                } else {
                    $collectionClassName = BookListFavoriteTableMap::getTableMap()->getCollectionClassName();

                    $collBookListFavorites = new $collectionClassName;
                    $collBookListFavorites->setModel('\Propel\Tests\Bookstore\BookListFavorite');

                    return $collBookListFavorites;
                }
            } else {
                $collBookListFavorites = ChildBookListFavoriteQuery::create(null, $criteria)
                    ->filterByFavoriteBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBookListFavoritesPartial && count($collBookListFavorites)) {
                        $this->initBookListFavorites(false);

                        foreach ($collBookListFavorites as $obj) {
                            if (false === $this->collBookListFavorites->contains($obj)) {
                                $this->collBookListFavorites->append($obj);
                            }
                        }

                        $this->collBookListFavoritesPartial = true;
                    }

                    return $collBookListFavorites;
                }

                if ($partial && $this->collBookListFavorites) {
                    foreach ($this->collBookListFavorites as $obj) {
                        if ($obj->isNew()) {
                            $collBookListFavorites[] = $obj;
                        }
                    }
                }

                $this->collBookListFavorites = $collBookListFavorites;
                $this->collBookListFavoritesPartial = false;
            }
        }

        return $this->collBookListFavorites;
    }

    /**
     * Sets a collection of ChildBookListFavorite objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookListFavorites A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookListFavorites(Collection $bookListFavorites, ?ConnectionInterface $con = null)
    {
        /** @var ChildBookListFavorite[] $bookListFavoritesToDelete */
        $bookListFavoritesToDelete = $this->getBookListFavorites(new Criteria(), $con)->diff($bookListFavorites);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->bookListFavoritesScheduledForDeletion = clone $bookListFavoritesToDelete;

        foreach ($bookListFavoritesToDelete as $bookListFavoriteRemoved) {
            $bookListFavoriteRemoved->setFavoriteBook(null);
        }

        $this->collBookListFavorites = null;
        foreach ($bookListFavorites as $bookListFavorite) {
            $this->addBookListFavorite($bookListFavorite);
        }

        $this->collBookListFavorites = $bookListFavorites;
        $this->collBookListFavoritesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookListFavorite objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related BookListFavorite objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBookListFavorites(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookListFavoritesPartial && !$this->isNew();
        if (null === $this->collBookListFavorites || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookListFavorites) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBookListFavorites());
            }

            $query = ChildBookListFavoriteQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByFavoriteBook($this)
                ->count($con);
        }

        return count($this->collBookListFavorites);
    }

    /**
     * Method called to associate a ChildBookListFavorite object to this object
     * through the ChildBookListFavorite foreign key attribute.
     *
     * @param ChildBookListFavorite $l ChildBookListFavorite
     * @return $this The current object (for fluent API support)
     */
    public function addBookListFavorite(ChildBookListFavorite $l)
    {
        if ($this->collBookListFavorites === null) {
            $this->initBookListFavorites();
            $this->collBookListFavoritesPartial = true;
        }

        if (!$this->collBookListFavorites->contains($l)) {
            $this->doAddBookListFavorite($l);

            if ($this->bookListFavoritesScheduledForDeletion and $this->bookListFavoritesScheduledForDeletion->contains($l)) {
                $this->bookListFavoritesScheduledForDeletion->remove($this->bookListFavoritesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBookListFavorite $bookListFavorite The ChildBookListFavorite object to add.
     */
    protected function doAddBookListFavorite(ChildBookListFavorite $bookListFavorite): void
    {
        $this->collBookListFavorites[]= $bookListFavorite;
        $bookListFavorite->setFavoriteBook($this);
    }

    /**
     * @param ChildBookListFavorite $bookListFavorite The ChildBookListFavorite object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBookListFavorite(ChildBookListFavorite $bookListFavorite)
    {
        if ($this->getBookListFavorites()->contains($bookListFavorite)) {
            $pos = $this->collBookListFavorites->search($bookListFavorite);
            $this->collBookListFavorites->remove($pos);
            if (null === $this->bookListFavoritesScheduledForDeletion) {
                $this->bookListFavoritesScheduledForDeletion = clone $this->collBookListFavorites;
                $this->bookListFavoritesScheduledForDeletion->clear();
            }
            $this->bookListFavoritesScheduledForDeletion[]= clone $bookListFavorite;
            $bookListFavorite->setFavoriteBook(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related BookListFavorites from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookListFavorite[] List of ChildBookListFavorite objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookListFavorite> List of ChildBookListFavorite objects
     */
    public function getBookListFavoritesJoinFavoriteBookClubList(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookListFavoriteQuery::create(null, $criteria);
        $query->joinWith('FavoriteBookClubList', $joinBehavior);

        return $this->getBookListFavorites($query, $con);
    }

    /**
     * Clears out the collBookOpinions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addBookOpinions()
     */
    public function clearBookOpinions()
    {
        $this->collBookOpinions = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collBookOpinions collection loaded partially.
     *
     * @return void
     */
    public function resetPartialBookOpinions($v = true): void
    {
        $this->collBookOpinionsPartial = $v;
    }

    /**
     * Initializes the collBookOpinions collection.
     *
     * By default this just sets the collBookOpinions collection to an empty array (like clearcollBookOpinions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBookOpinions(bool $overrideExisting = true): void
    {
        if (null !== $this->collBookOpinions && !$overrideExisting) {
            return;
        }

        $collectionClassName = BookOpinionTableMap::getTableMap()->getCollectionClassName();

        $this->collBookOpinions = new $collectionClassName;
        $this->collBookOpinions->setModel('\Propel\Tests\Bookstore\BookOpinion');
    }

    /**
     * Gets an array of ChildBookOpinion objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildBookOpinion[] List of ChildBookOpinion objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookOpinion> List of ChildBookOpinion objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookOpinions(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookOpinionsPartial && !$this->isNew();
        if (null === $this->collBookOpinions || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookOpinions) {
                    $this->initBookOpinions();
                } else {
                    $collectionClassName = BookOpinionTableMap::getTableMap()->getCollectionClassName();

                    $collBookOpinions = new $collectionClassName;
                    $collBookOpinions->setModel('\Propel\Tests\Bookstore\BookOpinion');

                    return $collBookOpinions;
                }
            } else {
                $collBookOpinions = ChildBookOpinionQuery::create(null, $criteria)
                    ->filterByBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collBookOpinionsPartial && count($collBookOpinions)) {
                        $this->initBookOpinions(false);

                        foreach ($collBookOpinions as $obj) {
                            if (false === $this->collBookOpinions->contains($obj)) {
                                $this->collBookOpinions->append($obj);
                            }
                        }

                        $this->collBookOpinionsPartial = true;
                    }

                    return $collBookOpinions;
                }

                if ($partial && $this->collBookOpinions) {
                    foreach ($this->collBookOpinions as $obj) {
                        if ($obj->isNew()) {
                            $collBookOpinions[] = $obj;
                        }
                    }
                }

                $this->collBookOpinions = $collBookOpinions;
                $this->collBookOpinionsPartial = false;
            }
        }

        return $this->collBookOpinions;
    }

    /**
     * Sets a collection of ChildBookOpinion objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookOpinions A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookOpinions(Collection $bookOpinions, ?ConnectionInterface $con = null)
    {
        /** @var ChildBookOpinion[] $bookOpinionsToDelete */
        $bookOpinionsToDelete = $this->getBookOpinions(new Criteria(), $con)->diff($bookOpinions);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->bookOpinionsScheduledForDeletion = clone $bookOpinionsToDelete;

        foreach ($bookOpinionsToDelete as $bookOpinionRemoved) {
            $bookOpinionRemoved->setBook(null);
        }

        $this->collBookOpinions = null;
        foreach ($bookOpinions as $bookOpinion) {
            $this->addBookOpinion($bookOpinion);
        }

        $this->collBookOpinions = $bookOpinions;
        $this->collBookOpinionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BookOpinion objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related BookOpinion objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countBookOpinions(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookOpinionsPartial && !$this->isNew();
        if (null === $this->collBookOpinions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookOpinions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBookOpinions());
            }

            $query = ChildBookOpinionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBook($this)
                ->count($con);
        }

        return count($this->collBookOpinions);
    }

    /**
     * Method called to associate a ChildBookOpinion object to this object
     * through the ChildBookOpinion foreign key attribute.
     *
     * @param ChildBookOpinion $l ChildBookOpinion
     * @return $this The current object (for fluent API support)
     */
    public function addBookOpinion(ChildBookOpinion $l)
    {
        if ($this->collBookOpinions === null) {
            $this->initBookOpinions();
            $this->collBookOpinionsPartial = true;
        }

        if (!$this->collBookOpinions->contains($l)) {
            $this->doAddBookOpinion($l);

            if ($this->bookOpinionsScheduledForDeletion and $this->bookOpinionsScheduledForDeletion->contains($l)) {
                $this->bookOpinionsScheduledForDeletion->remove($this->bookOpinionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildBookOpinion $bookOpinion The ChildBookOpinion object to add.
     */
    protected function doAddBookOpinion(ChildBookOpinion $bookOpinion): void
    {
        $this->collBookOpinions[]= $bookOpinion;
        $bookOpinion->setBook($this);
    }

    /**
     * @param ChildBookOpinion $bookOpinion The ChildBookOpinion object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeBookOpinion(ChildBookOpinion $bookOpinion)
    {
        if ($this->getBookOpinions()->contains($bookOpinion)) {
            $pos = $this->collBookOpinions->search($bookOpinion);
            $this->collBookOpinions->remove($pos);
            if (null === $this->bookOpinionsScheduledForDeletion) {
                $this->bookOpinionsScheduledForDeletion = clone $this->collBookOpinions;
                $this->bookOpinionsScheduledForDeletion->clear();
            }
            $this->bookOpinionsScheduledForDeletion[]= clone $bookOpinion;
            $bookOpinion->setBook(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related BookOpinions from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookOpinion[] List of ChildBookOpinion objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookOpinion> List of ChildBookOpinion objects
     */
    public function getBookOpinionsJoinBookReader(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookOpinionQuery::create(null, $criteria);
        $query->joinWith('BookReader', $joinBehavior);

        return $this->getBookOpinions($query, $con);
    }

    /**
     * Clears out the collReaderFavorites collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addReaderFavorites()
     */
    public function clearReaderFavorites()
    {
        $this->collReaderFavorites = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collReaderFavorites collection loaded partially.
     *
     * @return void
     */
    public function resetPartialReaderFavorites($v = true): void
    {
        $this->collReaderFavoritesPartial = $v;
    }

    /**
     * Initializes the collReaderFavorites collection.
     *
     * By default this just sets the collReaderFavorites collection to an empty array (like clearcollReaderFavorites());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initReaderFavorites(bool $overrideExisting = true): void
    {
        if (null !== $this->collReaderFavorites && !$overrideExisting) {
            return;
        }

        $collectionClassName = ReaderFavoriteTableMap::getTableMap()->getCollectionClassName();

        $this->collReaderFavorites = new $collectionClassName;
        $this->collReaderFavorites->setModel('\Propel\Tests\Bookstore\ReaderFavorite');
    }

    /**
     * Gets an array of ChildReaderFavorite objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildReaderFavorite[] List of ChildReaderFavorite objects
     * @phpstan-return ObjectCollection&\Traversable<ChildReaderFavorite> List of ChildReaderFavorite objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getReaderFavorites(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collReaderFavoritesPartial && !$this->isNew();
        if (null === $this->collReaderFavorites || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collReaderFavorites) {
                    $this->initReaderFavorites();
                } else {
                    $collectionClassName = ReaderFavoriteTableMap::getTableMap()->getCollectionClassName();

                    $collReaderFavorites = new $collectionClassName;
                    $collReaderFavorites->setModel('\Propel\Tests\Bookstore\ReaderFavorite');

                    return $collReaderFavorites;
                }
            } else {
                $collReaderFavorites = ChildReaderFavoriteQuery::create(null, $criteria)
                    ->filterByBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collReaderFavoritesPartial && count($collReaderFavorites)) {
                        $this->initReaderFavorites(false);

                        foreach ($collReaderFavorites as $obj) {
                            if (false === $this->collReaderFavorites->contains($obj)) {
                                $this->collReaderFavorites->append($obj);
                            }
                        }

                        $this->collReaderFavoritesPartial = true;
                    }

                    return $collReaderFavorites;
                }

                if ($partial && $this->collReaderFavorites) {
                    foreach ($this->collReaderFavorites as $obj) {
                        if ($obj->isNew()) {
                            $collReaderFavorites[] = $obj;
                        }
                    }
                }

                $this->collReaderFavorites = $collReaderFavorites;
                $this->collReaderFavoritesPartial = false;
            }
        }

        return $this->collReaderFavorites;
    }

    /**
     * Sets a collection of ChildReaderFavorite objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $readerFavorites A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setReaderFavorites(Collection $readerFavorites, ?ConnectionInterface $con = null)
    {
        /** @var ChildReaderFavorite[] $readerFavoritesToDelete */
        $readerFavoritesToDelete = $this->getReaderFavorites(new Criteria(), $con)->diff($readerFavorites);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->readerFavoritesScheduledForDeletion = clone $readerFavoritesToDelete;

        foreach ($readerFavoritesToDelete as $readerFavoriteRemoved) {
            $readerFavoriteRemoved->setBook(null);
        }

        $this->collReaderFavorites = null;
        foreach ($readerFavorites as $readerFavorite) {
            $this->addReaderFavorite($readerFavorite);
        }

        $this->collReaderFavorites = $readerFavorites;
        $this->collReaderFavoritesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ReaderFavorite objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related ReaderFavorite objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countReaderFavorites(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collReaderFavoritesPartial && !$this->isNew();
        if (null === $this->collReaderFavorites || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collReaderFavorites) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getReaderFavorites());
            }

            $query = ChildReaderFavoriteQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBook($this)
                ->count($con);
        }

        return count($this->collReaderFavorites);
    }

    /**
     * Method called to associate a ChildReaderFavorite object to this object
     * through the ChildReaderFavorite foreign key attribute.
     *
     * @param ChildReaderFavorite $l ChildReaderFavorite
     * @return $this The current object (for fluent API support)
     */
    public function addReaderFavorite(ChildReaderFavorite $l)
    {
        if ($this->collReaderFavorites === null) {
            $this->initReaderFavorites();
            $this->collReaderFavoritesPartial = true;
        }

        if (!$this->collReaderFavorites->contains($l)) {
            $this->doAddReaderFavorite($l);

            if ($this->readerFavoritesScheduledForDeletion and $this->readerFavoritesScheduledForDeletion->contains($l)) {
                $this->readerFavoritesScheduledForDeletion->remove($this->readerFavoritesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildReaderFavorite $readerFavorite The ChildReaderFavorite object to add.
     */
    protected function doAddReaderFavorite(ChildReaderFavorite $readerFavorite): void
    {
        $this->collReaderFavorites[]= $readerFavorite;
        $readerFavorite->setBook($this);
    }

    /**
     * @param ChildReaderFavorite $readerFavorite The ChildReaderFavorite object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeReaderFavorite(ChildReaderFavorite $readerFavorite)
    {
        if ($this->getReaderFavorites()->contains($readerFavorite)) {
            $pos = $this->collReaderFavorites->search($readerFavorite);
            $this->collReaderFavorites->remove($pos);
            if (null === $this->readerFavoritesScheduledForDeletion) {
                $this->readerFavoritesScheduledForDeletion = clone $this->collReaderFavorites;
                $this->readerFavoritesScheduledForDeletion->clear();
            }
            $this->readerFavoritesScheduledForDeletion[]= clone $readerFavorite;
            $readerFavorite->setBook(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related ReaderFavorites from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildReaderFavorite[] List of ChildReaderFavorite objects
     * @phpstan-return ObjectCollection&\Traversable<ChildReaderFavorite> List of ChildReaderFavorite objects
     */
    public function getReaderFavoritesJoinBookReader(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildReaderFavoriteQuery::create(null, $criteria);
        $query->joinWith('BookReader', $joinBehavior);

        return $this->getReaderFavorites($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related ReaderFavorites from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildReaderFavorite[] List of ChildReaderFavorite objects
     * @phpstan-return ObjectCollection&\Traversable<ChildReaderFavorite> List of ChildReaderFavorite objects
     */
    public function getReaderFavoritesJoinBookOpinion(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildReaderFavoriteQuery::create(null, $criteria);
        $query->joinWith('BookOpinion', $joinBehavior);

        return $this->getReaderFavorites($query, $con);
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
     * If this ChildBook is new, it will return
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
                    ->filterByWork($this)
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


        $this->bookstoreContestsScheduledForDeletion = $bookstoreContestsToDelete;

        foreach ($bookstoreContestsToDelete as $bookstoreContestRemoved) {
            $bookstoreContestRemoved->setWork(null);
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
                ->filterByWork($this)
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
        $bookstoreContest->setWork($this);
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
            $this->bookstoreContestsScheduledForDeletion[]= $bookstoreContest;
            $bookstoreContest->setWork(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related BookstoreContests from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildBookstoreContest[] List of ChildBookstoreContest objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookstoreContest> List of ChildBookstoreContest objects
     */
    public function getBookstoreContestsJoinBookstore(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildBookstoreContestQuery::create(null, $criteria);
        $query->joinWith('Bookstore', $joinBehavior);

        return $this->getBookstoreContests($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related BookstoreContests from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
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
     * If this ChildBook is new, it will return
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
                    ->filterByBook($this)
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
            $polymorphicRelationLogRemoved->setBook(null);
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
                ->filterByBook($this)
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
        $polymorphicRelationLog->setBook($this);
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
            $polymorphicRelationLog->setBook(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Book is new, it will return
     * an empty collection; or if this Book has previously
     * been saved, it will retrieve related PolymorphicRelationLogs from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Book.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @param string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildPolymorphicRelationLog[] List of ChildPolymorphicRelationLog objects
     * @phpstan-return ObjectCollection&\Traversable<ChildPolymorphicRelationLog> List of ChildPolymorphicRelationLog objects
     */
    public function getPolymorphicRelationLogsJoinAuthor(?Criteria $criteria = null, ?ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildPolymorphicRelationLogQuery::create(null, $criteria);
        $query->joinWith('Author', $joinBehavior);

        return $this->getPolymorphicRelationLogs($query, $con);
    }

    /**
     * Clears out the collBookClubLists collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addBookClubLists()
     */
    public function clearBookClubLists()
    {
        $this->collBookClubLists = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collBookClubLists crossRef collection.
     *
     * By default this just sets the collBookClubLists collection to an empty collection (like clearBookClubLists());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initBookClubLists()
    {
        $collectionClassName = BookListRelTableMap::getTableMap()->getCollectionClassName();

        $this->collBookClubLists = new $collectionClassName;
        $this->collBookClubListsPartial = true;
        $this->collBookClubLists->setModel('\Propel\Tests\Bookstore\BookClubList');
    }

    /**
     * Checks if the collBookClubLists collection is loaded.
     *
     * @return bool
     */
    public function isBookClubListsLoaded(): bool
    {
        return null !== $this->collBookClubLists;
    }

    /**
     * Gets a collection of ChildBookClubList objects related by a many-to-many relationship
     * to the current object by way of the book_x_list cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria Optional query object to filter the query
     * @param ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildBookClubList[] List of ChildBookClubList objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookClubList> List of ChildBookClubList objects
     */
    public function getBookClubLists(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collBookClubListsPartial && !$this->isNew();
        if (null === $this->collBookClubLists || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collBookClubLists) {
                    $this->initBookClubLists();
                }
            } else {

                $query = ChildBookClubListQuery::create(null, $criteria)
                    ->filterByBook($this);
                $collBookClubLists = $query->find($con);
                if (null !== $criteria) {
                    return $collBookClubLists;
                }

                if ($partial && $this->collBookClubLists) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collBookClubLists as $obj) {
                        if (!$collBookClubLists->contains($obj)) {
                            $collBookClubLists[] = $obj;
                        }
                    }
                }

                $this->collBookClubLists = $collBookClubLists;
                $this->collBookClubListsPartial = false;
            }
        }

        return $this->collBookClubLists;
    }

    /**
     * Sets a collection of BookClubList objects related by a many-to-many relationship
     * to the current object by way of the book_x_list cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $bookClubLists A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setBookClubLists(Collection $bookClubLists, ?ConnectionInterface $con = null)
    {
        $this->clearBookClubLists();
        $currentBookClubLists = $this->getBookClubLists();

        $bookClubListsScheduledForDeletion = $currentBookClubLists->diff($bookClubLists);

        foreach ($bookClubListsScheduledForDeletion as $toDelete) {
            $this->removeBookClubList($toDelete);
        }

        foreach ($bookClubLists as $bookClubList) {
            if (!$currentBookClubLists->contains($bookClubList)) {
                $this->doAddBookClubList($bookClubList);
            }
        }

        $this->collBookClubListsPartial = false;
        $this->collBookClubLists = $bookClubLists;

        return $this;
    }

    /**
     * Gets the number of BookClubList objects related by a many-to-many relationship
     * to the current object by way of the book_x_list cross-reference table.
     *
     * @param Criteria $criteria Optional query object to filter the query
     * @param bool $distinct Set to true to force count distinct
     * @param ConnectionInterface $con Optional connection object
     *
     * @return int The number of related BookClubList objects
     */
    public function countBookClubLists(?Criteria $criteria = null, $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collBookClubListsPartial && !$this->isNew();
        if (null === $this->collBookClubLists || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBookClubLists) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getBookClubLists());
                }

                $query = ChildBookClubListQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByBook($this)
                    ->count($con);
            }
        } else {
            return count($this->collBookClubLists);
        }
    }

    /**
     * Associate a ChildBookClubList to this object
     * through the book_x_list cross reference table.
     *
     * @param ChildBookClubList $bookClubList
     * @return ChildBook The current object (for fluent API support)
     */
    public function addBookClubList(ChildBookClubList $bookClubList)
    {
        if ($this->collBookClubLists === null) {
            $this->initBookClubLists();
        }

        if (!$this->getBookClubLists()->contains($bookClubList)) {
            // only add it if the **same** object is not already associated
            $this->collBookClubLists->push($bookClubList);
            $this->doAddBookClubList($bookClubList);
        }

        return $this;
    }

    /**
     *
     * @param ChildBookClubList $bookClubList
     */
    protected function doAddBookClubList(ChildBookClubList $bookClubList)
    {
        $bookListRel = new ChildBookListRel();

        $bookListRel->setBookClubList($bookClubList);

        $bookListRel->setBook($this);

        $this->addBookListRel($bookListRel);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$bookClubList->isBooksLoaded()) {
            $bookClubList->initBooks();
            $bookClubList->getBooks()->push($this);
        } elseif (!$bookClubList->getBooks()->contains($this)) {
            $bookClubList->getBooks()->push($this);
        }

    }

    /**
     * Remove bookClubList of this object
     * through the book_x_list cross reference table.
     *
     * @param ChildBookClubList $bookClubList
     * @return ChildBook The current object (for fluent API support)
     */
    public function removeBookClubList(ChildBookClubList $bookClubList)
    {
        if ($this->getBookClubLists()->contains($bookClubList)) {
            $bookListRel = new ChildBookListRel();
            $bookListRel->setBookClubList($bookClubList);
            if ($bookClubList->isBooksLoaded()) {
                //remove the back reference if available
                $bookClubList->getBooks()->removeObject($this);
            }

            $bookListRel->setBook($this);
            $this->removeBookListRel(clone $bookListRel);
            $bookListRel->clear();

            $this->collBookClubLists->remove($this->collBookClubLists->search($bookClubList));

            if (null === $this->bookClubListsScheduledForDeletion) {
                $this->bookClubListsScheduledForDeletion = clone $this->collBookClubLists;
                $this->bookClubListsScheduledForDeletion->clear();
            }

            $this->bookClubListsScheduledForDeletion->push($bookClubList);
        }


        return $this;
    }

    /**
     * Clears out the collFavoriteBookClubLists collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addFavoriteBookClubLists()
     */
    public function clearFavoriteBookClubLists()
    {
        $this->collFavoriteBookClubLists = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collFavoriteBookClubLists crossRef collection.
     *
     * By default this just sets the collFavoriteBookClubLists collection to an empty collection (like clearFavoriteBookClubLists());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initFavoriteBookClubLists()
    {
        $collectionClassName = BookListFavoriteTableMap::getTableMap()->getCollectionClassName();

        $this->collFavoriteBookClubLists = new $collectionClassName;
        $this->collFavoriteBookClubListsPartial = true;
        $this->collFavoriteBookClubLists->setModel('\Propel\Tests\Bookstore\BookClubList');
    }

    /**
     * Checks if the collFavoriteBookClubLists collection is loaded.
     *
     * @return bool
     */
    public function isFavoriteBookClubListsLoaded(): bool
    {
        return null !== $this->collFavoriteBookClubLists;
    }

    /**
     * Gets a collection of ChildBookClubList objects related by a many-to-many relationship
     * to the current object by way of the book_club_list_favorite_books cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria Optional query object to filter the query
     * @param ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildBookClubList[] List of ChildBookClubList objects
     * @phpstan-return ObjectCollection&\Traversable<ChildBookClubList> List of ChildBookClubList objects
     */
    public function getFavoriteBookClubLists(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collFavoriteBookClubListsPartial && !$this->isNew();
        if (null === $this->collFavoriteBookClubLists || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collFavoriteBookClubLists) {
                    $this->initFavoriteBookClubLists();
                }
            } else {

                $query = ChildBookClubListQuery::create(null, $criteria)
                    ->filterByFavoriteBook($this);
                $collFavoriteBookClubLists = $query->find($con);
                if (null !== $criteria) {
                    return $collFavoriteBookClubLists;
                }

                if ($partial && $this->collFavoriteBookClubLists) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collFavoriteBookClubLists as $obj) {
                        if (!$collFavoriteBookClubLists->contains($obj)) {
                            $collFavoriteBookClubLists[] = $obj;
                        }
                    }
                }

                $this->collFavoriteBookClubLists = $collFavoriteBookClubLists;
                $this->collFavoriteBookClubListsPartial = false;
            }
        }

        return $this->collFavoriteBookClubLists;
    }

    /**
     * Sets a collection of BookClubList objects related by a many-to-many relationship
     * to the current object by way of the book_club_list_favorite_books cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $favoriteBookClubLists A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setFavoriteBookClubLists(Collection $favoriteBookClubLists, ?ConnectionInterface $con = null)
    {
        $this->clearFavoriteBookClubLists();
        $currentFavoriteBookClubLists = $this->getFavoriteBookClubLists();

        $favoriteBookClubListsScheduledForDeletion = $currentFavoriteBookClubLists->diff($favoriteBookClubLists);

        foreach ($favoriteBookClubListsScheduledForDeletion as $toDelete) {
            $this->removeFavoriteBookClubList($toDelete);
        }

        foreach ($favoriteBookClubLists as $favoriteBookClubList) {
            if (!$currentFavoriteBookClubLists->contains($favoriteBookClubList)) {
                $this->doAddFavoriteBookClubList($favoriteBookClubList);
            }
        }

        $this->collFavoriteBookClubListsPartial = false;
        $this->collFavoriteBookClubLists = $favoriteBookClubLists;

        return $this;
    }

    /**
     * Gets the number of BookClubList objects related by a many-to-many relationship
     * to the current object by way of the book_club_list_favorite_books cross-reference table.
     *
     * @param Criteria $criteria Optional query object to filter the query
     * @param bool $distinct Set to true to force count distinct
     * @param ConnectionInterface $con Optional connection object
     *
     * @return int The number of related BookClubList objects
     */
    public function countFavoriteBookClubLists(?Criteria $criteria = null, $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collFavoriteBookClubListsPartial && !$this->isNew();
        if (null === $this->collFavoriteBookClubLists || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collFavoriteBookClubLists) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getFavoriteBookClubLists());
                }

                $query = ChildBookClubListQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByFavoriteBook($this)
                    ->count($con);
            }
        } else {
            return count($this->collFavoriteBookClubLists);
        }
    }

    /**
     * Associate a ChildBookClubList to this object
     * through the book_club_list_favorite_books cross reference table.
     *
     * @param ChildBookClubList $favoriteBookClubList
     * @return ChildBook The current object (for fluent API support)
     */
    public function addFavoriteBookClubList(ChildBookClubList $favoriteBookClubList)
    {
        if ($this->collFavoriteBookClubLists === null) {
            $this->initFavoriteBookClubLists();
        }

        if (!$this->getFavoriteBookClubLists()->contains($favoriteBookClubList)) {
            // only add it if the **same** object is not already associated
            $this->collFavoriteBookClubLists->push($favoriteBookClubList);
            $this->doAddFavoriteBookClubList($favoriteBookClubList);
        }

        return $this;
    }

    /**
     *
     * @param ChildBookClubList $favoriteBookClubList
     */
    protected function doAddFavoriteBookClubList(ChildBookClubList $favoriteBookClubList)
    {
        $bookListFavorite = new ChildBookListFavorite();

        $bookListFavorite->setFavoriteBookClubList($favoriteBookClubList);

        $bookListFavorite->setFavoriteBook($this);

        $this->addBookListFavorite($bookListFavorite);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$favoriteBookClubList->isFavoriteBooksLoaded()) {
            $favoriteBookClubList->initFavoriteBooks();
            $favoriteBookClubList->getFavoriteBooks()->push($this);
        } elseif (!$favoriteBookClubList->getFavoriteBooks()->contains($this)) {
            $favoriteBookClubList->getFavoriteBooks()->push($this);
        }

    }

    /**
     * Remove favoriteBookClubList of this object
     * through the book_club_list_favorite_books cross reference table.
     *
     * @param ChildBookClubList $favoriteBookClubList
     * @return ChildBook The current object (for fluent API support)
     */
    public function removeFavoriteBookClubList(ChildBookClubList $favoriteBookClubList)
    {
        if ($this->getFavoriteBookClubLists()->contains($favoriteBookClubList)) {
            $bookListFavorite = new ChildBookListFavorite();
            $bookListFavorite->setFavoriteBookClubList($favoriteBookClubList);
            if ($favoriteBookClubList->isFavoriteBooksLoaded()) {
                //remove the back reference if available
                $favoriteBookClubList->getFavoriteBooks()->removeObject($this);
            }

            $bookListFavorite->setFavoriteBook($this);
            $this->removeBookListFavorite(clone $bookListFavorite);
            $bookListFavorite->clear();

            $this->collFavoriteBookClubLists->remove($this->collFavoriteBookClubLists->search($favoriteBookClubList));

            if (null === $this->favoriteBookClubListsScheduledForDeletion) {
                $this->favoriteBookClubListsScheduledForDeletion = clone $this->collFavoriteBookClubLists;
                $this->favoriteBookClubListsScheduledForDeletion->clear();
            }

            $this->favoriteBookClubListsScheduledForDeletion->push($favoriteBookClubList);
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
        if (null !== $this->aPublisher) {
            $this->aPublisher->removeBook($this);
        }
        if (null !== $this->aAuthor) {
            $this->aAuthor->removeBook($this);
        }
        $this->id = null;
        $this->title = null;
        $this->isbn = null;
        $this->price = null;
        $this->publisher_id = null;
        $this->author_id = null;
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
            if ($this->collBookSummaries) {
                foreach ($this->collBookSummaries as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collReviews) {
                foreach ($this->collReviews as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMedias) {
                foreach ($this->collMedias as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBookListRels) {
                foreach ($this->collBookListRels as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBookListFavorites) {
                foreach ($this->collBookListFavorites as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBookOpinions) {
                foreach ($this->collBookOpinions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collReaderFavorites) {
                foreach ($this->collReaderFavorites as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBookstoreContests) {
                foreach ($this->collBookstoreContests as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPolymorphicRelationLogs) {
                foreach ($this->collPolymorphicRelationLogs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBookClubLists) {
                foreach ($this->collBookClubLists as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collFavoriteBookClubLists) {
                foreach ($this->collFavoriteBookClubLists as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collBookSummaries = null;
        $this->collReviews = null;
        $this->collMedias = null;
        $this->collBookListRels = null;
        $this->collBookListFavorites = null;
        $this->collBookOpinions = null;
        $this->collReaderFavorites = null;
        $this->collBookstoreContests = null;
        $this->collPolymorphicRelationLogs = null;
        $this->collBookClubLists = null;
        $this->collFavoriteBookClubLists = null;
        $this->aPublisher = null;
        $this->aAuthor = null;
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
