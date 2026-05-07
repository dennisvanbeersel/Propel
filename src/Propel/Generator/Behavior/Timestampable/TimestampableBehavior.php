<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Behavior\Timestampable;

use DateTime;
use Propel\Generator\Builder\Om\AbstractOMBuilder;
use Propel\Generator\Builder\Om\ObjectBuilder;
use Propel\Generator\Builder\Util\CodeEmitter;
use Propel\Generator\Model\Behavior;

/**
 * Gives a model class the ability to track creation and last modification dates
 * Uses two additional columns storing the creation and update date
 *
 * @author François Zaninotto
 */
class TimestampableBehavior extends Behavior
{
    /**
     * @var array<string, mixed>
     */
    protected array $parameters = [
        'create_column' => 'created_at',
        'update_column' => 'updated_at',
        'disable_created_at' => 'false',
        'disable_updated_at' => 'false',
        // Phase D (umbrella §6.3): on MySQL/MariaDB the update column is declared with
        // ON UPDATE CURRENT_TIMESTAMP at the DDL level — single source of truth.
        // Set 'false' to opt out and keep the legacy PHP-side preUpdate hook on all platforms.
        'use_native_on_update' => 'true',
    ];

    /**
     * @return bool
     */
    protected function withUpdatedAt(): bool
    {
        return !$this->booleanValue($this->getParameter('disable_updated_at'));
    }

    /**
     * @return bool
     */
    protected function withCreatedAt(): bool
    {
        return !$this->booleanValue($this->getParameter('disable_created_at'));
    }

    /**
     * Phase D (umbrella §6.3): native ON UPDATE delegates the updated_at refresh
     * to the database engine — single source of truth, no PHP-side hook needed.
     * Only honored on MySQL/MariaDB; on PG / SQLite the legacy PHP-side preUpdate
     * hook is used (PG has no native ON UPDATE; SQLite is frozen per umbrella §1.2).
     *
     * @return bool
     */
    protected function useNativeOnUpdate(): bool
    {
        return $this->booleanValue($this->getParameter('use_native_on_update'));
    }

    /**
     * Returns true when the resolved platform supports native ON UPDATE
     * CURRENT_TIMESTAMP at the column DDL level. MySQL and MariaDB are detected
     * via the platform class name suffix; everything else is false.
     *
     * @return bool
     */
    protected function platformSupportsNativeOnUpdate(): bool
    {
        $table = $this->getTable();
        $database = $table->getDatabase();
        if ($database === null) {
            return false;
        }
        $platform = $database->getPlatform();
        if ($platform === null) {
            return false;
        }

        return $platform->getDatabaseType() === 'mysql';
    }

    /**
     * Native `ON UPDATE CURRENT_TIMESTAMP` only makes sense on temporal columns
     * (TIMESTAMP/DATETIME). Schemas that opt into the legacy integer-epoch
     * pattern (`type="INTEGER"` storing `time()`) must keep the PHP-side
     * preUpdate hook, since the database has no temporal value to refresh.
     *
     * @return bool
     */
    protected function updateColumnIsNativeCompatible(): bool
    {
        $table = $this->getTable();
        if (!$table->hasColumn($this->getParameter('update_column'))) {
            return false;
        }

        $type = strtoupper($table->getColumn($this->getParameter('update_column'))->getType());

        return $type === 'TIMESTAMP' || $type === 'DATETIME';
    }

    /**
     * Add the create_column and update_columns to the current table
     *
     * @return void
     */
    #[\Override]
    public function modifyTable(): void
    {
        $table = $this->getTable();

        if ($this->withCreatedAt() && !$table->hasColumn($this->getParameter('create_column'))) {
            $table->addColumn([
                'name' => $this->getParameter('create_column'),
                'type' => 'TIMESTAMP',
            ]);
        }
        if ($this->withUpdatedAt() && !$table->hasColumn($this->getParameter('update_column'))) {
            $table->addColumn([
                'name' => $this->getParameter('update_column'),
                'type' => 'TIMESTAMP',
            ]);
        }

        // Phase D: when native ON UPDATE is requested AND the platform supports it,
        // attach a vendor parameter so the platform's getColumnDDL emits
        // "ON UPDATE CURRENT_TIMESTAMP" inline. The PHP-side preUpdate hook then
        // skips its setUpdatedAt emission (see preUpdate() below).
        if (
            $this->withUpdatedAt()
            && $this->useNativeOnUpdate()
            && $this->platformSupportsNativeOnUpdate()
            && $this->updateColumnIsNativeCompatible()
        ) {
            $updateColumn = $table->getColumn($this->getParameter('update_column'));
            $vendor = $updateColumn->getVendorInfoForType('mysql');
            if (!$vendor->hasParameter('OnUpdate')) {
                $vendor->setParameter('OnUpdate', 'CURRENT_TIMESTAMP');
                // getVendorInfoForType returns a new VendorInfo when none exists
                // for the requested type — re-attach it to the column so it persists.
                $updateColumn->addVendorInfo($vendor);
            }
        }
    }

    /**
     * Phase D: when native ON UPDATE is in effect, the database refreshes the
     * timestamp itself — short-circuit so the same logic isn't duplicated PHP-side.
     *
     * @return bool
     */
    protected function nativeOnUpdateActive(): bool
    {
        return $this->withUpdatedAt()
            && $this->useNativeOnUpdate()
            && $this->platformSupportsNativeOnUpdate()
            && $this->updateColumnIsNativeCompatible();
    }

    /**
     * Get the setter of one of the columns of the behavior
     *
     * @param string $column One of the behavior columns, 'create_column' or 'update_column'
     *
     * @return string The related setter, 'setCreatedOn' or 'setUpdatedOn'
     */
    protected function getColumnSetter(string $column): string
    {
        return 'set' . $this->getColumnForParameter($column)->getPhpName();
    }

    /**
     * @param string $columnName
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string
     */
    protected function getColumnConstant(string $columnName, AbstractOMBuilder $builder): string
    {
        return $builder->getColumnConstant($this->getColumnForParameter($columnName));
    }

    /**
     * Add code in ObjectBuilder::preUpdate
     *
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string The code to put at the hook
     */
    public function preUpdate(AbstractOMBuilder $builder): string
    {
        // Phase D: when native ON UPDATE is active the database refreshes
        // updated_at on every UPDATE — duplicating it PHP-side is wasted work
        // and would break keepUpdateDateUnchanged() opt-out semantics.
        if ($this->nativeOnUpdateActive()) {
            return '';
        }

        if ($this->withUpdatedAt()) {
            $updateColumn = $this->getTable()->getColumn($this->getParameter('update_column'));

            $dateTimeClass = $builder instanceof ObjectBuilder
                ? $builder->getDateTimeClass($updateColumn)
                : DateTime::class;

            $valueSource = strtoupper($updateColumn->getType()) === 'INTEGER'
                ? 'time()'
                : "PropelDateTime::createHighPrecision(null, '$dateTimeClass')";

            return 'if ($this->isModified() && !$this->isColumnModified(' . $this->getColumnConstant('update_column', $builder) . ")) {
    \$this->" . $this->getColumnSetter('update_column') . "({$valueSource});
}";
        }

        return '';
    }

    /**
     * Add code in ObjectBuilder::preInsert
     *
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string The code to put at the hook
     */
    public function preInsert(AbstractOMBuilder $builder): string
    {
        $script = '$mtime = microtime(true);';

        if ($this->withCreatedAt()) {
            $createColumn = $this->getTable()->getColumn($this->getParameter('create_column'));

            $dateTimeClass = $builder instanceof ObjectBuilder
                ? $builder->getDateTimeClass($createColumn)
                : DateTime::class;

            $valueSource = strtoupper($createColumn->getType()) === 'INTEGER'
                ? '(int)$mtime'
                : "PropelDateTime::createHighPrecision(PropelDateTime::formatMicrotime(\$mtime), '$dateTimeClass')";

            $script .= "
if (!\$this->isColumnModified(" . $this->getColumnConstant('create_column', $builder) . ")) {
    \$this->" . $this->getColumnSetter('create_column') . "({$valueSource});
}";
        }

        if ($this->withUpdatedAt()) {
            $updateColumn = $this->getTable()->getColumn($this->getParameter('update_column'));

            $dateTimeClass = $builder instanceof ObjectBuilder
                ? $builder->getDateTimeClass($updateColumn)
                : DateTime::class;

            $valueSource = strtoupper($updateColumn->getType()) === 'INTEGER'
                ? '(int)$mtime'
                : "PropelDateTime::createHighPrecision(PropelDateTime::formatMicrotime(\$mtime), '$dateTimeClass')";

            $script .= "
if (!\$this->isColumnModified(" . $this->getColumnConstant('update_column', $builder) . ")) {
    \$this->" . $this->getColumnSetter('update_column') . "({$valueSource});
}";
        }

        return $script;
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string
     */
    public function objectMethods(AbstractOMBuilder $builder): string
    {
        if (!$this->withUpdatedAt()) {
            return '';
        }

        $updateConstant = $this->getColumnConstant('update_column', $builder);

        $emitter = new CodeEmitter();
        $emitter->blank();
        $emitter->docblock(
            "Mark the current object so that the update date doesn't get updated during next save\n"
            . "\n"
            . '@return $this The current object (for fluent API support)',
        );
        $emitter->line('public function keepUpdateDateUnchanged()');
        $emitter->line('{');
        $body = $emitter->block();
        $emitter->line('$this->modifiedColumns[' . $updateConstant . '] = true;');
        $emitter->blank();
        $emitter->line('return $this;');
        unset($body);
        $emitter->line('}');
        $emitter->blank();

        return $emitter->toString();
    }

    /**
     * @param \Propel\Generator\Builder\Om\AbstractOMBuilder $builder
     *
     * @return string
     */
    public function queryMethods(AbstractOMBuilder $builder): string
    {
        $script = '';

        if ($this->withUpdatedAt()) {
            $updateColumnConstant = $this->getColumnConstant('update_column', $builder);
            $script .= "
/**
 * Filter by the latest updated
 *
 * @param int \$nbDays Maximum age of the latest update in days
 *
 * @return \$this The current query, for fluid interface
 */
public function recentlyUpdated(\$nbDays = 7)
{
    \$this->addUsingAlias($updateColumnConstant, time() - \$nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);

    return \$this;
}

/**
 * Order by update date desc
 *
 * @return \$this The current query, for fluid interface
 */
public function lastUpdatedFirst()
{
    \$this->addDescendingOrderByColumn($updateColumnConstant);

    return \$this;
}

/**
 * Order by update date asc
 *
 * @return \$this The current query, for fluid interface
 */
public function firstUpdatedFirst()
{
    \$this->addAscendingOrderByColumn($updateColumnConstant);

    return \$this;
}
";
        }

        if ($this->withCreatedAt()) {
            $createColumnConstant = $this->getColumnConstant('create_column', $builder);
            $script .= "
/**
 * Order by create date desc
 *
 * @return \$this The current query, for fluid interface
 */
public function lastCreatedFirst()
{
    \$this->addDescendingOrderByColumn($createColumnConstant);

    return \$this;
}

/**
 * Filter by the latest created
 *
 * @param int \$nbDays Maximum age of in days
 *
 * @return \$this The current query, for fluid interface
 */
public function recentlyCreated(\$nbDays = 7)
{
    \$this->addUsingAlias($createColumnConstant, time() - \$nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);

    return \$this;
}

/**
 * Order by create date asc
 *
 * @return \$this The current query, for fluid interface
 */
public function firstCreatedFirst()
{
    \$this->addAscendingOrderByColumn($createColumnConstant);

    return \$this;
}
";
        }

        return $script;
    }
}
