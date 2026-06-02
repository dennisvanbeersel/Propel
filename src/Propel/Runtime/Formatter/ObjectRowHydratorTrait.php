<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Formatter;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;

/**
 * Phase G.6 (Propel 4.0): row→entity hydration extracted from
 * {@see ObjectFormatter::getAllObjectsFromRow} so the streaming sibling
 * {@see StreamingObjectFormatter} can reuse it without inheriting
 * `ObjectFormatter::format()`'s `Collection|array` return contract.
 *
 * Behavior is byte-equivalent to the original method. The dedup against
 * `$this->objects` short-circuits when the property is empty (the
 * streaming formatter never populates it), so the trait stays drop-in
 * compatible with the eager formatter's instance-pooling-disabled flow.
 *
 * Consumers must declare a `protected array $objects = [];` property and
 * provide `getTableMap(): TableMap`, `getDataFetcher()`, `getWith()`,
 * `getAsColumns()` (all inherited from {@see AbstractFormatter}).
 *
 * @internal
 */
trait ObjectRowHydratorTrait
{
    /**
     * Hydrates a series of objects from a result row.
     *
     * The first object hydrated is the model of the Criteria; subsequent
     * objects (the ones added via `ModelCriteria::with()`) are linked to
     * the first one.
     *
     * @param array $row associative array indexed by column number,
     *                   as returned by DataFetcher::fetch()
     *
     * @return \Propel\Runtime\ActiveRecord\ActiveRecordInterface
     */
    public function getAllObjectsFromRow(array $row): ActiveRecordInterface
    {
        $indexType = $this->getDataFetcher()->getIndexType();

        // main object
        [$obj, $col] = $this->getTableMap()->populateObject($row, 0, $indexType);

        $pk = $obj->getPrimaryKey();
        $serializedPk = serialize($pk);

        if (isset($this->objects[$serializedPk])) {
            //if instance pooling is disabled, we need to make sure we're working on the correct (already fetched) object
            //so one-to-many relations are correctly loaded.
            $obj = $this->objects[$serializedPk];
        }

        //TODO: is this var even useable? populateObject() also seems dead.
        /** @var array<string, object> $hydrationChain */
        $hydrationChain = [];

        // related objects added using with()
        foreach ($this->getWith() as $modelWith) {
            [$endObject, $col] = $modelWith->getTableMap()->populateObject($row, $col, $indexType);

            if ($modelWith->getLeftPhpName() !== null && !isset($hydrationChain[$modelWith->getLeftPhpName()])) {
                continue;
            }

            if ($modelWith->isPrimary()) {
                $startObject = $obj;
            } elseif ($hydrationChain && !empty($hydrationChain[$modelWith->getLeftPhpName()])) {
                $startObject = $hydrationChain[$modelWith->getLeftPhpName()];
            } else {
                continue;
            }

            // as we may be in a left join, the endObject may be empty
            // in which case it should not be related to the previous object
            if ($endObject === null || $endObject->isPrimaryKeyNull()) {
                if ($modelWith->isAdd()) {
                    $initMethod = $modelWith->getInitMethod();
                    $startObject->$initMethod(false);
                }

                continue;
            }

            $hydrationChain[$modelWith->getRightPhpName()] = $endObject;

            $relationMethod = $modelWith->getRelationMethod();
            $startObject->$relationMethod($endObject);

            if ($modelWith->isAdd()) {
                $resetPartialMethod = $modelWith->getResetPartialMethod();
                $startObject->$resetPartialMethod(false);
            }
        }

        // columns added using withColumn()
        foreach ($this->getAsColumns() as $alias => $clause) {
            $obj->setVirtualColumn($alias, $row[$col]);
            $col++;
        }

        return $obj;
    }
}
