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
use Propel\Tests\Bookstore\AcctAccessRole as ChildAcctAccessRole;
use Propel\Tests\Bookstore\AcctAccessRoleQuery as ChildAcctAccessRoleQuery;
use Propel\Tests\Bookstore\AcctAuditLog as ChildAcctAuditLog;
use Propel\Tests\Bookstore\AcctAuditLogQuery as ChildAcctAuditLogQuery;
use Propel\Tests\Bookstore\BookstoreEmployee as ChildBookstoreEmployee;
use Propel\Tests\Bookstore\BookstoreEmployeeAccount as ChildBookstoreEmployeeAccount;
use Propel\Tests\Bookstore\BookstoreEmployeeAccountQuery as ChildBookstoreEmployeeAccountQuery;
use Propel\Tests\Bookstore\BookstoreEmployeeQuery as ChildBookstoreEmployeeQuery;
use Propel\Tests\Bookstore\Map\AcctAuditLogTableMap;
use Propel\Tests\Bookstore\Map\BookstoreEmployeeAccountTableMap;

/**
 * Base class that represents a row from the 'bookstore_employee_account' table.
 *
 * Bookstore employees login credentials.
 *
 * @package    propel.generator.Propel.Tests.Bookstore.Base
 */
abstract class BookstoreEmployeeAccount implements ActiveRecordInterface
{
    /**
     * TableMap class name
     *
     * @var string
     */
    public const TABLE_MAP = '\\Propel\\Tests\\Bookstore\\Map\\BookstoreEmployeeAccountTableMap';


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
     * The value for the employee_id field.
     * Primary key for the account ...
     * @var        int
     */
    protected ?int $employee_id = null;

    /**
     * The value for the login field.
     *
     * @var        string|null
     */
    protected ?string $login = null;

    /**
     * The value for the password field.
     *
     * Note: this column has a database default value of: '\'@\'\'34"'
     * @var        string|null
     */
    protected ?string $password = null;

    /**
     * The value for the enabled field.
     *
     * Note: this column has a database default value of: true
     * @var        bool|null
     */
    protected ?bool $enabled = null;

    /**
     * The value for the not_enabled field.
     *
     * Note: this column has a database default value of: false
     * @var        bool|null
     */
    protected ?bool $not_enabled = null;

    /**
     * The value for the created field.
     *
     * Note: this column has a database default value of: (expression) CURRENT_TIMESTAMP
     * @var        DateTime|null
     */
    protected $created;

    /**
     * The value for the updated field.
     *
     * Note: this column has a database default value of: (expression) CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
     * @var        DateTime|null
     */
    protected $updated;

    /**
     * The value for the role_id field.
     *
     * @var        int|null
     */
    protected ?int $role_id = null;

    /**
     * The value for the authenticator field.
     *
     * Note: this column has a database default value of: (expression) 'Password'
     * @var        string|null
     */
    protected ?string $authenticator = null;

    /**
     * @var        ChildBookstoreEmployee|null
     */
    protected ?ChildBookstoreEmployee $aBookstoreEmployee = null;

    /**
     * @var        ChildAcctAccessRole|null
     */
    protected ?ChildAcctAccessRole $aAcctAccessRole = null;

    /**
     * @var        ObjectCollection|ChildAcctAuditLog[] Collection to store aggregation of ChildAcctAuditLog objects.
     * @phpstan-var ObjectCollection&\Traversable<ChildAcctAuditLog> Collection to store aggregation of ChildAcctAuditLog objects.
     */
    protected $collAcctAuditLogs;
    protected bool $collAcctAuditLogsPartial = false;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var bool
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildAcctAuditLog[]
     * @phpstan-var ObjectCollection&\Traversable<ChildAcctAuditLog>
     */
    protected $acctAuditLogsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues(): void
    {
        $this->password = '\'@\'\'34"';
        $this->enabled = true;
        $this->not_enabled = false;
    }

    /**
     * Initializes internal state of Propel\Tests\Bookstore\Base\BookstoreEmployeeAccount object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
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
     * Compares this with another <code>BookstoreEmployeeAccount</code> instance.  If
     * <code>obj</code> is an instance of <code>BookstoreEmployeeAccount</code>, delegates to
     * <code>equals(BookstoreEmployeeAccount)</code>.  Otherwise, returns <code>false</code>.
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
     * Get the [employee_id] column value.
     * Primary key for the account ...
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->employee_id;
    }

    /**
     * Get the [login] column value.
     *
     * @return string|null
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Get the [password] column value.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the [enabled] column value.
     *
     * @return bool|null
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Get the [enabled] column value.
     *
     * @return bool|null
     */
    public function isEnabled()
    {
        return $this->getEnabled();
    }

    /**
     * Get the [not_enabled] column value.
     *
     * @return bool|null
     */
    public function getNotEnabled()
    {
        return $this->not_enabled;
    }

    /**
     * Get the [not_enabled] column value.
     *
     * @return bool|null
     */
    public function isNotEnabled()
    {
        return $this->getNotEnabled();
    }

    /**
     * Get the [optionally formatted] temporal [created] column value.
     *
     *
     * @param string|null $format The date/time format string (date()-style).
     *   If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime|null Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00.
     *
     * @throws \Propel\Runtime\Exception\PropelException - if unable to parse/validate the date/time value.
     *
     * @psalm-return ($format is null ? DateTime|null : string|null)
     */
    public function getCreated(?string $format = null)
    {
        if ($format === null) {
            return $this->created;
        } else {
            return $this->created instanceof \DateTimeInterface ? $this->created->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated] column value.
     *
     *
     * @param string|null $format The date/time format string (date()-style).
     *   If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime|null Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00.
     *
     * @throws \Propel\Runtime\Exception\PropelException - if unable to parse/validate the date/time value.
     *
     * @psalm-return ($format is null ? DateTime|null : string|null)
     */
    public function getUpdated(?string $format = null)
    {
        if ($format === null) {
            return $this->updated;
        } else {
            return $this->updated instanceof \DateTimeInterface ? $this->updated->format($format) : null;
        }
    }

    /**
     * Get the [role_id] column value.
     *
     * @return int|null
     */
    public function getRoleId()
    {
        return $this->role_id;
    }

    /**
     * Get the [authenticator] column value.
     *
     * @return string|null
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * Set the value of [employee_id] column.
     * Primary key for the account ...
     * @param int $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setEmployeeId($v): self
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->employee_id !== $v) {
            $this->employee_id = $v;
            $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID] = true;
        }

        if ($this->aBookstoreEmployee !== null && $this->aBookstoreEmployee->getId() !== $v) {
            $this->aBookstoreEmployee = null;
        }

        return $this;
    }

    /**
     * Set the value of [login] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setLogin($v): self
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->login !== $v) {
            $this->login = $v;
            $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_LOGIN] = true;
        }

        return $this;
    }

    /**
     * Set the value of [password] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setPassword($v): self
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->password !== $v) {
            $this->password = $v;
            $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_PASSWORD] = true;
        }

        return $this;
    }

    /**
     * Sets the value of the [enabled] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param bool|integer|string|null $v The new value
     * @return $this The current object (for fluent API support)
     */
    public function setEnabled($v): self
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), ['false', 'off', '-', 'no', 'n', '0', '']) ? false : true;
            } else {
                $v = (bool) $v;
            }
        }

        if ($this->enabled !== $v) {
            $this->enabled = $v;
            $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_ENABLED] = true;
        }

        return $this;
    }

    /**
     * Sets the value of the [not_enabled] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param bool|integer|string|null $v The new value
     * @return $this The current object (for fluent API support)
     */
    public function setNotEnabled($v): self
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), ['false', 'off', '-', 'no', 'n', '0', '']) ? false : true;
            } else {
                $v = (bool) $v;
            }
        }

        if ($this->not_enabled !== $v) {
            $this->not_enabled = $v;
            $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED] = true;
        }

        return $this;
    }

    /**
     * Sets the value of [created] column to a normalized version of the date/time value specified.
     *
     * @param string|integer|\DateTimeInterface|null $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this The current object (for fluent API support)
     */
    public function setCreated($v): self
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created !== null || $dt !== null) {
            if ($this->created === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->created->format("Y-m-d H:i:s.u")) {
                $this->created = $dt === null ? null : clone $dt;
                $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_CREATED] = true;
            }
        } // if either are not null

        return $this;
    }

    /**
     * Sets the value of [updated] column to a normalized version of the date/time value specified.
     *
     * @param string|integer|\DateTimeInterface|null $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this The current object (for fluent API support)
     */
    public function setUpdated($v): self
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->updated !== null || $dt !== null) {
            if ($this->updated === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->updated->format("Y-m-d H:i:s.u")) {
                $this->updated = $dt === null ? null : clone $dt;
                $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_UPDATED] = true;
            }
        } // if either are not null

        return $this;
    }

    /**
     * Set the value of [role_id] column.
     *
     * @param int|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setRoleId($v): self
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->role_id !== $v) {
            $this->role_id = $v;
            $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_ROLE_ID] = true;
        }

        if ($this->aAcctAccessRole !== null && $this->aAcctAccessRole->getId() !== $v) {
            $this->aAcctAccessRole = null;
        }

        return $this;
    }

    /**
     * Set the value of [authenticator] column.
     *
     * @param string|null $v New value
     * @return $this The current object (for fluent API support)
     */
    public function setAuthenticator($v): self
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->authenticator !== $v) {
            $this->authenticator = $v;
            $this->modifiedColumns[BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR] = true;
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
            if ($this->password !== '\'@\'\'34"') {
                return false;
            }

            if ($this->enabled !== true) {
                return false;
            }

            if ($this->not_enabled !== false) {
                return false;
            }

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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('EmployeeId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->employee_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('Login', TableMap::TYPE_PHPNAME, $indexType)];
            $this->login = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('Password', TableMap::TYPE_PHPNAME, $indexType)];
            $this->password = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('Enabled', TableMap::TYPE_PHPNAME, $indexType)];
            $this->enabled = (null !== $col) ? (bool) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('NotEnabled', TableMap::TYPE_PHPNAME, $indexType)];
            $this->not_enabled = (null !== $col) ? (bool) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('Created', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('Updated', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('RoleId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->role_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : BookstoreEmployeeAccountTableMap::translateFieldName('Authenticator', TableMap::TYPE_PHPNAME, $indexType)];
            $this->authenticator = (null !== $col) ? (string) $col : null;

            $this->resetModified();
            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 9; // 9 = BookstoreEmployeeAccountTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Propel\\Tests\\Bookstore\\BookstoreEmployeeAccount'), 0, $e);
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
        if ($this->aBookstoreEmployee !== null && $this->employee_id !== $this->aBookstoreEmployee->getId()) {
            $this->aBookstoreEmployee = null;
        }
        if ($this->aAcctAccessRole !== null && $this->role_id !== $this->aAcctAccessRole->getId()) {
            $this->aAcctAccessRole = null;
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
            $con = Propel::getServiceContainer()->getReadConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildBookstoreEmployeeAccountQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aBookstoreEmployee = null;
            $this->aAcctAccessRole = null;
            $this->collAcctAuditLogs = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param ConnectionInterface $con
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     * @see BookstoreEmployeeAccount::setDeleted()
     * @see BookstoreEmployeeAccount::isDeleted()
     */
    public function delete(?ConnectionInterface $con = null): void
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildBookstoreEmployeeAccountQuery::create()
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
     * Since this table was configured to reload rows on update, the object will
     * be reloaded from the database if an UPDATE operation is performed (unless
     * the $skipReload parameter is TRUE).
     *
     * Since this table was configured to reload rows on insert, the object will
     * be reloaded from the database if an INSERT operation is performed (unless
     * the $skipReload parameter is TRUE).
     *
     * @param ConnectionInterface $con
     * @param boolean $skipReload Whether to skip the reload for this object from database.
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see doSave()
     */
    public function save(?ConnectionInterface $con = null, bool $skipReload = false): int
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookstoreEmployeeAccountTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con, $skipReload) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con, $skipReload);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                BookstoreEmployeeAccountTableMap::addInstanceToPool($this);
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
     * @param bool $skipReload Whether to skip the reload for this object from database.
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws \Propel\Runtime\Exception\PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con, $skipReload = false): int
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            $reloadObject = false;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aBookstoreEmployee !== null) {
                if ($this->aBookstoreEmployee->isModified() || $this->aBookstoreEmployee->isNew()) {
                    $affectedRows += $this->aBookstoreEmployee->save($con);
                }
                $this->setBookstoreEmployee($this->aBookstoreEmployee);
            }

            if ($this->aAcctAccessRole !== null) {
                if ($this->aAcctAccessRole->isModified() || $this->aAcctAccessRole->isNew()) {
                    $affectedRows += $this->aAcctAccessRole->save($con);
                }
                $this->setAcctAccessRole($this->aAcctAccessRole);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                    if (!$skipReload) {
                        $reloadObject = true;
                    }
                } else {
                    $affectedRows += $this->doUpdate($con);
                    if (!$skipReload) {
                        $reloadObject = true;
                    }
                }
                $this->resetModified();
            }

            if ($this->acctAuditLogsScheduledForDeletion !== null) {
                if (!$this->acctAuditLogsScheduledForDeletion->isEmpty()) {
                    \Propel\Tests\Bookstore\AcctAuditLogQuery::create()
                        ->filterByPrimaryKeys($this->acctAuditLogsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->acctAuditLogsScheduledForDeletion = null;
                }
            }

            if ($this->collAcctAuditLogs !== null) {
                foreach ($this->collAcctAuditLogs as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

            if ($reloadObject) {
                $this->reload((bool)$con);
            }

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


         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID)) {
            $modifiedColumns[':p' . $index++]  = 'employee_id';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_LOGIN)) {
            $modifiedColumns[':p' . $index++]  = 'login';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_PASSWORD)) {
            $modifiedColumns[':p' . $index++]  = 'password';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_ENABLED)) {
            $modifiedColumns[':p' . $index++]  = 'enabled';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED)) {
            $modifiedColumns[':p' . $index++]  = 'not_enabled';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_CREATED)) {
            $modifiedColumns[':p' . $index++]  = 'created';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_UPDATED)) {
            $modifiedColumns[':p' . $index++]  = 'updated';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_ROLE_ID)) {
            $modifiedColumns[':p' . $index++]  = 'role_id';
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR)) {
            $modifiedColumns[':p' . $index++]  = 'authenticator';
        }

        $sql = sprintf(
            'INSERT INTO bookstore_employee_account (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'employee_id':
                        $stmt->bindValue($identifier, $this->employee_id, PDO::PARAM_INT);

                        break;
                    case 'login':
                        $stmt->bindValue($identifier, $this->login, PDO::PARAM_STR);

                        break;
                    case 'password':
                        $stmt->bindValue($identifier, $this->password, PDO::PARAM_STR);

                        break;
                    case 'enabled':
                        $stmt->bindValue($identifier, $this->enabled, PDO::PARAM_BOOL);

                        break;
                    case 'not_enabled':
                        $stmt->bindValue($identifier, $this->not_enabled, PDO::PARAM_BOOL);

                        break;
                    case 'created':
                        $stmt->bindValue($identifier, $this->created ? $this->created->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);

                        break;
                    case 'updated':
                        $stmt->bindValue($identifier, $this->updated ? $this->updated->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);

                        break;
                    case 'role_id':
                        $stmt->bindValue($identifier, $this->role_id, PDO::PARAM_INT);

                        break;
                    case 'authenticator':
                        $stmt->bindValue($identifier, $this->authenticator, PDO::PARAM_STR);

                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

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
        $pos = (int)BookstoreEmployeeAccountTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getEmployeeId();

            case 1:
                return $this->getLogin();

            case 2:
                return $this->getPassword();

            case 3:
                return $this->getEnabled();

            case 4:
                return $this->getNotEnabled();

            case 5:
                return $this->getCreated();

            case 6:
                return $this->getUpdated();

            case 7:
                return $this->getRoleId();

            case 8:
                return $this->getAuthenticator();

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
        if (isset($alreadyDumpedObjects['BookstoreEmployeeAccount'][$this->hashCode()])) {
            return ['*RECURSION*'];
        }
        $alreadyDumpedObjects['BookstoreEmployeeAccount'][$this->hashCode()] = true;
        $keys = BookstoreEmployeeAccountTableMap::getFieldNames($keyType);
        $result = [
            $keys[0] => $this->getEmployeeId(),
            $keys[1] => $this->getLogin(),
            $keys[2] => $this->getPassword(),
            $keys[3] => $this->getEnabled(),
            $keys[4] => $this->getNotEnabled(),
            $keys[5] => $this->getCreated(),
            $keys[6] => $this->getUpdated(),
            $keys[7] => $this->getRoleId(),
            $keys[8] => $this->getAuthenticator(),
        ];
        if ($result[$keys[5]] instanceof \DateTimeInterface) {
            $result[$keys[5]] = $result[$keys[5]]->format('Y-m-d H:i:s.u');
        }

        if ($result[$keys[6]] instanceof \DateTimeInterface) {
            $result[$keys[6]] = $result[$keys[6]]->format('Y-m-d H:i:s.u');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aBookstoreEmployee) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'bookstoreEmployee';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'bookstore_employee';
                        break;
                    default:
                        $key = 'BookstoreEmployee';
                }

                $result[$key] = $this->aBookstoreEmployee->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAcctAccessRole) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'acctAccessRole';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'acct_access_role';
                        break;
                    default:
                        $key = 'AcctAccessRole';
                }

                $result[$key] = $this->aAcctAccessRole->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collAcctAuditLogs) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'acctAuditLogs';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'acct_audit_logs';
                        break;
                    default:
                        $key = 'AcctAuditLogs';
                }

                $result[$key] = $this->collAcctAuditLogs->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = (int)BookstoreEmployeeAccountTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

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
                $this->setEmployeeId($value);
                break;
            case 1:
                $this->setLogin($value);
                break;
            case 2:
                $this->setPassword($value);
                break;
            case 3:
                $this->setEnabled($value);
                break;
            case 4:
                $this->setNotEnabled($value);
                break;
            case 5:
                $this->setCreated($value);
                break;
            case 6:
                $this->setUpdated($value);
                break;
            case 7:
                $this->setRoleId($value);
                break;
            case 8:
                $this->setAuthenticator($value);
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
        $keys = BookstoreEmployeeAccountTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setEmployeeId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setLogin($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setPassword($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setEnabled($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setNotEnabled($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setCreated($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setUpdated($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setRoleId($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setAuthenticator($arr[$keys[8]]);
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
        $criteria = new Criteria(BookstoreEmployeeAccountTableMap::DATABASE_NAME);

        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $this->employee_id);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_LOGIN)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_LOGIN, $this->login);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_PASSWORD)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_PASSWORD, $this->password);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_ENABLED)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_ENABLED, $this->enabled);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_NOT_ENABLED, $this->not_enabled);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_CREATED)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_CREATED, $this->created);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_UPDATED)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_UPDATED, $this->updated);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_ROLE_ID)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_ROLE_ID, $this->role_id);
        }
        if ($this->isColumnModified(BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR)) {
            $criteria->add(BookstoreEmployeeAccountTableMap::COL_AUTHENTICATOR, $this->authenticator);
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
        $criteria = ChildBookstoreEmployeeAccountQuery::create();
        $criteria->add(BookstoreEmployeeAccountTableMap::COL_EMPLOYEE_ID, $this->employee_id);

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
        $validPk = null !== $this->getEmployeeId();

        $validPrimaryKeyFKs = 1;
        $primaryKeyFKs = [];

        //relation bookstore_employee_account_fk_0ae967 to table bookstore_employee
        if ($this->aBookstoreEmployee && $hash = spl_object_hash($this->aBookstoreEmployee)) {
            $primaryKeyFKs[] = $hash;
        } else {
            $validPrimaryKeyFKs = false;
        }

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
        return $this->getEmployeeId();
    }

    /**
     * Generic method to set the primary key (employee_id column).
     *
     * @param int|null $key Primary key.
     * @return void
     */
    public function setPrimaryKey(?int $key = null): void
    {
        $this->setEmployeeId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     *
     * @return bool
     */
    public function isPrimaryKeyNull(): bool
    {
        return null === $this->getEmployeeId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of \Propel\Tests\Bookstore\BookstoreEmployeeAccount (or compatible) type.
     * @param bool $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param bool $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws \Propel\Runtime\Exception\PropelException
     * @return void
     */
    public function copyInto(object $copyObj, bool $deepCopy = false, bool $makeNew = true): void
    {
        $copyObj->setEmployeeId($this->getEmployeeId());
        $copyObj->setLogin($this->getLogin());
        $copyObj->setPassword($this->getPassword());
        $copyObj->setEnabled($this->getEnabled());
        $copyObj->setNotEnabled($this->getNotEnabled());
        $copyObj->setCreated($this->getCreated());
        $copyObj->setUpdated($this->getUpdated());
        $copyObj->setRoleId($this->getRoleId());
        $copyObj->setAuthenticator($this->getAuthenticator());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getAcctAuditLogs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAcctAuditLog($relObj->copy($deepCopy));
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
     * @return \Propel\Tests\Bookstore\BookstoreEmployeeAccount Clone of current object.
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
     * Declares an association between this object and a ChildBookstoreEmployee object.
     *
     * @param ChildBookstoreEmployee $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setBookstoreEmployee(?ChildBookstoreEmployee $v = null): self
    {
        if ($v === null) {
            $this->setEmployeeId(NULL);
        } else {
            $this->setEmployeeId($v->getId());
        }

        $this->aBookstoreEmployee = $v;

        // Add binding for other direction of this 1:1 relationship.
        if ($v !== null) {
            $v->setBookstoreEmployeeAccount($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildBookstoreEmployee object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildBookstoreEmployee The associated ChildBookstoreEmployee object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getBookstoreEmployee(?ConnectionInterface $con = null): ?ChildBookstoreEmployee
    {
        if ($this->aBookstoreEmployee === null && ($this->employee_id !== null)) {
            $this->aBookstoreEmployee = ChildBookstoreEmployeeQuery::create()->findPk($this->employee_id, $con);
            // Because this foreign key represents a one-to-one relationship, we will create a bi-directional association.
            if ($this->aBookstoreEmployee !== null) {
                $this->aBookstoreEmployee->setBookstoreEmployeeAccount($this);
            }
        }

        return $this->aBookstoreEmployee;
    }

    /**
     * Declares an association between this object and a ChildAcctAccessRole object.
     *
     * @param ChildAcctAccessRole|null $v
     * @return $this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setAcctAccessRole(?ChildAcctAccessRole $v = null): self
    {
        if ($v === null) {
            $this->setRoleId(NULL);
        } else {
            $this->setRoleId($v->getId());
        }

        $this->aAcctAccessRole = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildAcctAccessRole object, it will not be re-added.
        if ($v !== null) {
            $v->addBookstoreEmployeeAccount($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildAcctAccessRole object
     *
     * @param ConnectionInterface $con Optional Connection object.
     * @return ChildAcctAccessRole|null The associated ChildAcctAccessRole object.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getAcctAccessRole(?ConnectionInterface $con = null): ?ChildAcctAccessRole
    {
        if ($this->aAcctAccessRole === null && ($this->role_id !== null)) {
            $this->aAcctAccessRole = ChildAcctAccessRoleQuery::create()->findPk($this->role_id, $con);
        }

        return $this->aAcctAccessRole;
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
        if ('AcctAuditLog' === $relationName) {
            $this->initAcctAuditLogs();
            return;
        }
    }

    /**
     * Clears out the collAcctAuditLogs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return $this
     * @see addAcctAuditLogs()
     */
    public function clearAcctAuditLogs()
    {
        $this->collAcctAuditLogs = null; // important to set this to NULL since that means it is uninitialized

        return $this;
    }

    /**
     * Reset is the collAcctAuditLogs collection loaded partially.
     *
     * @return void
     */
    public function resetPartialAcctAuditLogs($v = true): void
    {
        $this->collAcctAuditLogsPartial = $v;
    }

    /**
     * Initializes the collAcctAuditLogs collection.
     *
     * By default this just sets the collAcctAuditLogs collection to an empty array (like clearcollAcctAuditLogs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAcctAuditLogs(bool $overrideExisting = true): void
    {
        if (null !== $this->collAcctAuditLogs && !$overrideExisting) {
            return;
        }

        $collectionClassName = AcctAuditLogTableMap::getTableMap()->getCollectionClassName();

        $this->collAcctAuditLogs = new $collectionClassName;
        $this->collAcctAuditLogs->setModel('\Propel\Tests\Bookstore\AcctAuditLog');
    }

    /**
     * Gets an array of ChildAcctAuditLog objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBookstoreEmployeeAccount is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildAcctAuditLog[] List of ChildAcctAuditLog objects
     * @phpstan-return ObjectCollection&\Traversable<ChildAcctAuditLog> List of ChildAcctAuditLog objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getAcctAuditLogs(?Criteria $criteria = null, ?ConnectionInterface $con = null)
    {
        $partial = $this->collAcctAuditLogsPartial && !$this->isNew();
        if (null === $this->collAcctAuditLogs || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collAcctAuditLogs) {
                    $this->initAcctAuditLogs();
                } else {
                    $collectionClassName = AcctAuditLogTableMap::getTableMap()->getCollectionClassName();

                    $collAcctAuditLogs = new $collectionClassName;
                    $collAcctAuditLogs->setModel('\Propel\Tests\Bookstore\AcctAuditLog');

                    return $collAcctAuditLogs;
                }
            } else {
                $collAcctAuditLogs = ChildAcctAuditLogQuery::create(null, $criteria)
                    ->filterByBookstoreEmployeeAccount($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collAcctAuditLogsPartial && count($collAcctAuditLogs)) {
                        $this->initAcctAuditLogs(false);

                        foreach ($collAcctAuditLogs as $obj) {
                            if (false === $this->collAcctAuditLogs->contains($obj)) {
                                $this->collAcctAuditLogs->append($obj);
                            }
                        }

                        $this->collAcctAuditLogsPartial = true;
                    }

                    return $collAcctAuditLogs;
                }

                if ($partial && $this->collAcctAuditLogs) {
                    foreach ($this->collAcctAuditLogs as $obj) {
                        if ($obj->isNew()) {
                            $collAcctAuditLogs[] = $obj;
                        }
                    }
                }

                $this->collAcctAuditLogs = $collAcctAuditLogs;
                $this->collAcctAuditLogsPartial = false;
            }
        }

        return $this->collAcctAuditLogs;
    }

    /**
     * Sets a collection of ChildAcctAuditLog objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection $acctAuditLogs A Propel collection.
     * @param ConnectionInterface $con Optional connection object
     * @return $this The current object (for fluent API support)
     */
    public function setAcctAuditLogs(Collection $acctAuditLogs, ?ConnectionInterface $con = null)
    {
        /** @var ChildAcctAuditLog[] $acctAuditLogsToDelete */
        $acctAuditLogsToDelete = $this->getAcctAuditLogs(new Criteria(), $con)->diff($acctAuditLogs);


        $this->acctAuditLogsScheduledForDeletion = $acctAuditLogsToDelete;

        foreach ($acctAuditLogsToDelete as $acctAuditLogRemoved) {
            $acctAuditLogRemoved->setBookstoreEmployeeAccount(null);
        }

        $this->collAcctAuditLogs = null;
        foreach ($acctAuditLogs as $acctAuditLog) {
            $this->addAcctAuditLog($acctAuditLog);
        }

        $this->collAcctAuditLogs = $acctAuditLogs;
        $this->collAcctAuditLogsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related AcctAuditLog objects.
     *
     * @param Criteria $criteria
     * @param bool $distinct
     * @param ConnectionInterface $con
     * @return int Count of related AcctAuditLog objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function countAcctAuditLogs(?Criteria $criteria = null, bool $distinct = false, ?ConnectionInterface $con = null): int
    {
        $partial = $this->collAcctAuditLogsPartial && !$this->isNew();
        if (null === $this->collAcctAuditLogs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAcctAuditLogs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAcctAuditLogs());
            }

            $query = ChildAcctAuditLogQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBookstoreEmployeeAccount($this)
                ->count($con);
        }

        return count($this->collAcctAuditLogs);
    }

    /**
     * Method called to associate a ChildAcctAuditLog object to this object
     * through the ChildAcctAuditLog foreign key attribute.
     *
     * @param ChildAcctAuditLog $l ChildAcctAuditLog
     * @return $this The current object (for fluent API support)
     */
    public function addAcctAuditLog(ChildAcctAuditLog $l)
    {
        if ($this->collAcctAuditLogs === null) {
            $this->initAcctAuditLogs();
            $this->collAcctAuditLogsPartial = true;
        }

        if (!$this->collAcctAuditLogs->contains($l)) {
            $this->doAddAcctAuditLog($l);

            if ($this->acctAuditLogsScheduledForDeletion and $this->acctAuditLogsScheduledForDeletion->contains($l)) {
                $this->acctAuditLogsScheduledForDeletion->remove($this->acctAuditLogsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildAcctAuditLog $acctAuditLog The ChildAcctAuditLog object to add.
     */
    protected function doAddAcctAuditLog(ChildAcctAuditLog $acctAuditLog): void
    {
        $this->collAcctAuditLogs[]= $acctAuditLog;
        $acctAuditLog->setBookstoreEmployeeAccount($this);
    }

    /**
     * @param ChildAcctAuditLog $acctAuditLog The ChildAcctAuditLog object to remove.
     * @return $this The current object (for fluent API support)
     */
    public function removeAcctAuditLog(ChildAcctAuditLog $acctAuditLog)
    {
        if ($this->getAcctAuditLogs()->contains($acctAuditLog)) {
            $pos = $this->collAcctAuditLogs->search($acctAuditLog);
            $this->collAcctAuditLogs->remove($pos);
            if (null === $this->acctAuditLogsScheduledForDeletion) {
                $this->acctAuditLogsScheduledForDeletion = clone $this->collAcctAuditLogs;
                $this->acctAuditLogsScheduledForDeletion->clear();
            }
            $this->acctAuditLogsScheduledForDeletion[]= clone $acctAuditLog;
            $acctAuditLog->setBookstoreEmployeeAccount(null);
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
        if (null !== $this->aBookstoreEmployee) {
            $this->aBookstoreEmployee->removeBookstoreEmployeeAccount($this);
        }
        if (null !== $this->aAcctAccessRole) {
            $this->aAcctAccessRole->removeBookstoreEmployeeAccount($this);
        }
        $this->employee_id = null;
        $this->login = null;
        $this->password = null;
        $this->enabled = null;
        $this->not_enabled = null;
        $this->created = null;
        $this->updated = null;
        $this->role_id = null;
        $this->authenticator = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
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
            if ($this->collAcctAuditLogs) {
                foreach ($this->collAcctAuditLogs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collAcctAuditLogs = null;
        $this->aBookstoreEmployee = null;
        $this->aAcctAccessRole = null;
        return $this;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(BookstoreEmployeeAccountTableMap::DEFAULT_STRING_FORMAT);
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
