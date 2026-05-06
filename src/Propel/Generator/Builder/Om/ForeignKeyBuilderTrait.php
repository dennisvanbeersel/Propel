<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Om;

use Propel\Generator\Model\Column;
use Propel\Generator\Model\CrossForeignKeys;
use Propel\Generator\Model\ForeignKey;

/**
 * Trait containing foreign key related methods for ObjectBuilder.
 *
 * This trait provides all FK-related code generation methods including:
 * - Foreign key variable naming (getFKVarName)
 * - FK method generation (addFKMethods, addFKAttributes, addFKMutator, addFKAccessor)
 * - Cross FK methods (addCrossFKMethods, addCrossFKAttributes, etc.)
 */
trait ForeignKeyBuilderTrait
{
    /**
     * Constructs variable name for fkey-related objects.
     *
     * @param \Propel\Generator\Model\ForeignKey $fk
     *
     * @return string
     */
    public function getFKVarName(ForeignKey $fk): string
    {
        return 'a' . $this->getFKPhpNameAffix($fk, false);
    }

    /**
     * Adds the methods that get & set objects related by foreign key to the current object.
     *
     * @param string $script The script will be modified in this method.
     *
     * @return void
     */
    protected function addFKMethods(string &$script): void
    {
        foreach ($this->getTable()->getForeignKeys() as $fk) {
            $this->declareClassFromBuilder($this->getNewStubObjectBuilder($fk->getForeignTable()), 'Child');
            $this->declareClassFromBuilder($this->getNewStubQueryBuilder($fk->getForeignTable()));
            $this->addFKMutator($script, $fk);
            $this->addFKAccessor($script, $fk);
        }
    }

    /**
     * Adds the class attributes that are needed to store fkey related objects.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $fk
     *
     * @return void
     */
    protected function addFKAttributes(string &$script, ForeignKey $fk): void
    {
        $className = $this->getClassNameFromTable($fk->getForeignTable());
        $varName = $this->getFKVarName($fk);

        $script .= "
    /**
     * @var        $className|null
     */
    protected ?$className $$varName = null;
";
    }

    /**
     * Adds the mutator (setter) method for setting an fkey related object.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $fk
     *
     * @return void
     */
    protected function addFKMutator(string &$script, ForeignKey $fk): void
    {
        $fkTable = $fk->getForeignTable();
        $interface = $fk->getInterface();

        if ($interface) {
            $className = $this->declareClass($interface);
        } else {
            $className = $this->getClassNameFromTable($fkTable);
        }

        $varName = $this->getFKVarName($fk);

        $orNull = $fk->getLocalColumn()->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Declares an association between this object and a $className object.
     *
     * @param {$className}{$orNull} \$v
     * @return \$this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function set" . $this->getFKPhpNameAffix($fk, false) . "(?$className \$v = null): self
    {";

        foreach ($fk->getMapping() as $map) {
            [$column, $rightValueOrColumn] = $map;

            if ($rightValueOrColumn instanceof Column) {
                $script .= "
        if (\$v === null) {
            \$this->set" . $column->getPhpName() . '(' . $this->getDefaultValueString($column) . ");
        } else {
            \$this->set" . $column->getPhpName() . '($v->get' . $rightValueOrColumn->getPhpName() . "());
        }
";
            } else {
                $val = var_export($rightValueOrColumn, true);
                $script .= "
        if (\$v === null) {
            \$this->set" . $column->getPhpName() . "(null);
        } else {
            \$this->set" . $column->getPhpName() . "($val);
        }
                ";
            }
        } /* foreach local col */

        $script .= "
        \$this->$varName = \$v;
";

        // Now add bi-directional relationship binding, taking into account whether this is
        // a one-to-one relationship.

        if ($fk->isLocalPrimaryKey()) {
            $script .= "
        // Add binding for other direction of this 1:1 relationship.
        if (\$v !== null) {
            \$v->set" . $this->getRefFKPhpNameAffix($fk, false) . "(\$this);
        }
";
        } else {
            $script .= "
        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the $className object, it will not be re-added.
        if (\$v !== null) {
            \$v->add" . $this->getRefFKPhpNameAffix($fk, false) . "(\$this);
        }
";
        }

        $script .= "

        return \$this;
    }
";
    }

    /**
     * Adds the accessor (getter) method for getting an fkey related object.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $fk
     *
     * @return void
     */
    protected function addFKAccessor(string &$script, ForeignKey $fk): void
    {
        $varName = $this->getFKVarName($fk);
        $fkQueryBuilder = $this->getNewStubQueryBuilder($fk->getForeignTable());
        $fkObjectBuilder = $this->getNewObjectBuilder($fk->getForeignTable())->getStubObjectBuilder();
        $returnDesc = '';
        $interface = $fk->getInterface();

        if ($interface) {
            $className = $this->declareClass($interface);
        } else {
            $className = $this->getClassNameFromBuilder($fkObjectBuilder); // get the ClassName that has maybe a prefix
            $returnDesc = "The associated $className object.";
        }

        $and = '';
        $conditional = '';
        $localColumns = []; // foreign key local attributes names

        // If the related columns are a primary key on the foreign table
        // then use findPk() instead of doSelect() to take advantage
        // of instance pooling
        $findPk = $fk->isForeignPrimaryKey();

        foreach ($fk->getMapping() as $mapping) {
            [$column, $rightValueOrColumn] = $mapping;

            $cptype = $column->getPhpType();
            $clo = $column->getLowercasedName();

            if ($rightValueOrColumn instanceof Column) {
                $localColumns[$rightValueOrColumn->getPosition()] = '$this->' . $clo;

                if ($cptype === 'int' || $cptype === 'float' || $cptype === 'double') {
                    $conditional .= $and . '$this->' . $clo . ' !== null';
                } elseif ($cptype === 'string') {
                    $conditional .= $and . '($this->' . $clo . ' !== "" && $this->' . $clo . ' !== null)';
                } else {
                    $conditional .= $and . '$this->' . $clo . ' !== null';
                }
            } else {
                $val = var_export($rightValueOrColumn, true);
                $conditional .= $and . '$this->' . $clo . ' === ' . $val;
            }

            $and = ' && ';
        }

        ksort($localColumns); // restoring the order of the foreign PK
        $localColumns = count($localColumns) > 1 ?
            ('array(' . implode(', ', $localColumns) . ')') : reset($localColumns);

        $orNull = $fk->getLocalColumn()->isNotNull() ? '' : '|null';

        $script .= "

    /**
     * Get the associated $className object
     *
     * @param ConnectionInterface \$con Optional Connection object.
     * @return {$className}{$orNull} $returnDesc
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function get" . $this->getFKPhpNameAffix($fk, false) . "(?ConnectionInterface \$con = null): ?$className
    {";
        $script .= "
        if (\$this->$varName === null && ($conditional)) {";
        if ($findPk) {
            $script .= "
            \$this->$varName = " . $this->getClassNameFromBuilder($fkQueryBuilder) . "::create()->findPk($localColumns, \$con);";
        } else {
            $script .= "
            \$this->$varName = " . $this->getClassNameFromBuilder($fkQueryBuilder) . "::create()
                ->filterBy" . $this->getRefFKPhpNameAffix($fk, false) . "(\$this) // here
                ->findOne(\$con);";
        }
        if ($fk->isLocalPrimaryKey()) {
            $script .= "
            // Because this foreign key represents a one-to-one relationship, we will create a bi-directional association.
            if (\$this->{$varName} !== null) {
                \$this->{$varName}->set" . $this->getRefFKPhpNameAffix($fk, false) . '($this);
            }';
        }
        // For 1:N relations the inverse `$this->fk->addLocal($this)` could
        // be invoked here to keep the related collection fully consistent,
        // but doing so silently overrides user-managed coupling and may
        // partially populate the referenced collection — we skip it.

        $script .= "
        }

        return \$this->$varName;
    }
";
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKAttributes(string &$script, CrossForeignKeys $crossFKs): void
    {
        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            [$names] = $this->getCrossFKInformation($crossFKs);
            $script .= "
    /**
     * @var ObjectCombinationCollection Cross CombinationCollection to store aggregation of $names combinations.
     */
    protected \$combination" . ucfirst($this->getCrossFKsVarName($crossFKs)) . ";

    /**
     * @var bool
     */
    protected \$combination" . ucfirst($this->getCrossFKsVarName($crossFKs)) . "Partial;
";
        }

        foreach ($crossFKs->getCrossForeignKeys() as $fk) {
            $className = $this->getClassNameFromTable($fk->getForeignTable());

            $script .= "
    /**
     * @var        ObjectCollection|{$className}[] Cross Collection to store aggregation of $className objects.
     * @phpstan-var ObjectCollection&\Traversable<{$className}> Cross Collection to store aggregation of $className objects.
     */
    protected \$coll" . $this->getFKPhpNameAffix($fk, true) . ";

    /**
     * @var bool
     */
    protected \$coll" . $this->getFKPhpNameAffix($fk, true) . "Partial;
";
        }
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossScheduledForDeletionAttribute(string &$script, CrossForeignKeys $crossFKs): void
    {
        $name = $this->getCrossScheduledForDeletionVarName($crossFKs);
        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            [$names] = $this->getCrossFKInformation($crossFKs);
            $script .= "
    /**
     * @var ObjectCombinationCollection Cross CombinationCollection to store aggregation of $names combinations.
     */
    protected \$$name = null;
";
        } else {
            $refFK = $crossFKs->getIncomingForeignKey();
            if (!$refFK->isLocalPrimaryKey()) {
                $foreignTable = $crossFKs->getCrossForeignKeys()[0]->getForeignTable();
                $className = $this->getClassNameFromTable($foreignTable);
                $script .= "
    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|{$className}[]
     * @phpstan-var ObjectCollection&\Traversable<{$className}>
     */
    protected \$$name = null;
";
            }
        }
    }

    /**
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return string
     */
    protected function getCrossScheduledForDeletionVarName(CrossForeignKeys $crossFKs): string
    {
        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            return 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs)) . 'ScheduledForDeletion';
        } else {
            $fkName = lcfirst($this->getFKPhpNameAffix($crossFKs->getCrossForeignKeys()[0], true));

            return "{$fkName}ScheduledForDeletion";
        }
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\ForeignKey $crossFK
     *
     * @return void
     */
    protected function addCrossFkScheduledForDeletionAttribute(string &$script, ForeignKey $crossFK): void
    {
        $className = $this->getClassNameFromTable($crossFK->getForeignTable());
        $fkName = lcfirst($this->getFKPhpNameAffix($crossFK, true));

        $script .= "
    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|{$className}[]
     * @phpstan-var ObjectCollection&\Traversable<{$className}>
     */
    protected \${$fkName}ScheduledForDeletion = null;
";
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFkScheduledForDeletion(string &$script, CrossForeignKeys $crossFKs): void
    {
        $multipleFks = 1 < count($crossFKs->getCrossForeignKeys()) || (bool)$crossFKs->getUnclassifiedPrimaryKeys();
        $scheduledForDeletionVarName = $this->getCrossScheduledForDeletionVarName($crossFKs);
        $queryClassName = $this->getNewStubQueryBuilder($crossFKs->getMiddleTable())->getClassname();

        $crossPks = $crossFKs->getMiddleTable()->getPrimaryKey();

        $script .= "
            if (\$this->$scheduledForDeletionVarName !== null) {
                if (!\$this->{$scheduledForDeletionVarName}->isEmpty()) {
                    \$pks = [];";
        if ($multipleFks) {
            $script .= "
                    foreach (\$this->{$scheduledForDeletionVarName} as \$combination) {
                        \$entryPk = [];
";
            foreach ($crossFKs->getIncomingForeignKey()->getColumnObjectsMapping() as $reference) {
                $local = $reference['local'];
                $foreign = $reference['foreign'];

                $idx = array_search($local, $crossPks, true);
                $script .= "
                        \$entryPk[$idx] = \$this->get{$foreign->getPhpName()}();";
            }

            $combinationIdx = 0;
            foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
                foreach ($crossFK->getColumnObjectsMapping() as $reference) {
                    $local = $reference['local'];
                    $foreign = $reference['foreign'];

                    $idx = array_search($local, $crossPks, true);
                    $script .= "
                        \$entryPk[$idx] = \$combination[$combinationIdx]->get{$foreign->getPhpName()}();";
                }
                $combinationIdx++;
            }

            foreach ($crossFKs->getUnclassifiedPrimaryKeys() as $pk) {
                $idx = array_search($pk, $crossPks, true);
                $script .= "
                        //\$combination[$combinationIdx] = {$pk->getPhpName()};
                        \$entryPk[$idx] = \$combination[$combinationIdx];";
                $combinationIdx++;
            }

            $script .= "

                        \$pks[] = \$entryPk;
                    }
";

            $script .= "
                    $queryClassName::create()
                        ->filterByPrimaryKeys(\$pks)
                        ->delete(\$con);
";
        } else {
            $script .= "
                    foreach (\$this->{$scheduledForDeletionVarName} as \$entry) {
                        \$entryPk = [];
";

            foreach ($crossFKs->getIncomingForeignKey()->getColumnObjectsMapping() as $reference) {
                $local = $reference['local'];
                $foreign = $reference['foreign'];

                $idx = array_search($local, $crossPks, true);
                $script .= "
                        \$entryPk[$idx] = \$this->get{$foreign->getPhpName()}();";
            }

            $crossFK = $crossFKs->getCrossForeignKeys()[0];
            foreach ($crossFK->getColumnObjectsMapping() as $reference) {
                $local = $reference['local'];
                $foreign = $reference['foreign'];

                $idx = array_search($local, $crossPks, true);
                $script .= "
                        \$entryPk[$idx] = \$entry->get{$foreign->getPhpName()}();";
            }

            $script .= "
                        \$pks[] = \$entryPk;
                    }

                    {$queryClassName}::create()
                        ->filterByPrimaryKeys(\$pks)
                        ->delete(\$con);
";
        }

        $script .= "
                    \$this->$scheduledForDeletionVarName = null;
                }
";

        $script .= "
            }
";

        if ($multipleFks) {
            $combineVarName = 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs));
            $script .= "
            if (null !== \$this->$combineVarName) {
                foreach (\$this->$combineVarName as \$combination) {
";

            $combinationIdx = 0;
            foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
                $script .= "
                    //\$combination[$combinationIdx] = {$crossFK->getForeignTable()->getPhpName()} ({$crossFK->getName()})
                    if (!\$combination[$combinationIdx]->isDeleted() && (\$combination[$combinationIdx]->isNew() || \$combination[$combinationIdx]->isModified())) {
                        \$combination[$combinationIdx]->save(\$con);
                    }
                ";

                $combinationIdx++;
            }

            foreach ($crossFKs->getUnclassifiedPrimaryKeys() as $pk) {
                $script .= "
                    //\$combination[$combinationIdx] = {$pk->getPhpName()}; Nothing to save.";
                $combinationIdx++;
            }

            $script .= "
                }
            }
";
        } else {
            foreach ($crossFKs->getCrossForeignKeys() as $fk) {
                $relatedName = $this->getFKPhpNameAffix($fk, true);
                $lowerSingleRelatedName = lcfirst($this->getFKPhpNameAffix($fk, false));

                $script .= "
            if (\$this->coll{$relatedName}) {
                foreach (\$this->coll{$relatedName} as \${$lowerSingleRelatedName}) {
                    if (!\${$lowerSingleRelatedName}->isDeleted() && (\${$lowerSingleRelatedName}->isNew() || \${$lowerSingleRelatedName}->isModified())) {
                        \${$lowerSingleRelatedName}->save(\$con);
                    }
                }
            }
";
            }
        }

        $script .= "
";
    }

    /**
     * @param string $script
     *
     * @return void
     */
    protected function addCrossFKMethods(string &$script): void
    {
        foreach ($this->getTable()->getCrossFks() as $crossFKs) {
            foreach ($crossFKs->getCrossForeignKeys() as $fk) {
                $this->declareClassFromBuilder($this->getNewStubObjectBuilder($fk->getForeignTable()), 'Child');
                $this->declareClassFromBuilder($this->getNewStubQueryBuilder($fk->getForeignTable()));
            }

            $this->addCrossFKClear($script, $crossFKs);
            $this->addCrossFKInit($script, $crossFKs);
            $this->addCrossFKisLoaded($script, $crossFKs);
            $this->addCrossFKCreateQuery($script, $crossFKs);
            $this->addCrossFKGet($script, $crossFKs);
            $this->addCrossFKSet($script, $crossFKs);
            $this->addCrossFKCount($script, $crossFKs);
            $this->addCrossFKAdd($script, $crossFKs);
            $this->addCrossFKDoAdd($script, $crossFKs);
            $this->addCrossFKRemove($script, $crossFKs);
        }
    }

    /**
     * Adds the method that clears the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKClear(string &$script, CrossForeignKeys $crossFKs): void
    {
        $relCol = $this->getCrossFKsPhpNameAffix($crossFKs);
        $collName = $this->getCrossFKsVarName($crossFKs);

        $script .= "
    /**
     * Clears out the {$collName} collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        add{$relCol}()
     */
    public function clear{$relCol}()
    {
        \$this->$collName = null; // important to set this to NULL since that means it is uninitialized
    }
";
    }

    /**
     * Adds the method that initializes the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKInit(string &$script, CrossForeignKeys $crossFKs): void
    {
        $inits = [];

        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            $inits[] = [
                'relCol' => $this->getCrossFKsPhpNameAffix($crossFKs, true),
                'collName' => 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs)),
                'collectionClass' => 'ObjectCombinationCollection',
                'relatedObjectClassName' => false,
                'foreignTableMapName' => false,
            ];
        } else {
            foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
                $relCol = $this->getFKPhpNameAffix($crossFK, true);
                $collName = $this->getCrossFKVarName($crossFK);
                $relatedObjectClassName = $this->getClassNameFromBuilder(
                    $this->getNewStubObjectBuilder($crossFK->getForeignTable()),
                    true,
                );

                $foreignTableMapName = $this->getClassNameFromBuilder($this->getNewTableMapBuilder($crossFK->getTable()));

                $inits[] = [
                    'relCol' => $relCol,
                    'collName' => $collName,
                    'collectionClass' => false,
                    'relatedObjectClassName' => $relatedObjectClassName,
                    'foreignTableMapName' => $foreignTableMapName,
                ];
            }
        }

        foreach ($inits as $init) {
            $relCol = $init['relCol'];
            $collName = $init['collName'];
            $collectionClass = $init['collectionClass'];
            $relatedObjectClassName = $init['relatedObjectClassName'];
            $foreignTableMapName = $init['foreignTableMapName'];

            $script .= "
    /**
     * Initializes the $collName crossRef collection.
     *
     * By default this just sets the $collName collection to an empty collection (like clear$relCol());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function init$relCol()
    {";
            if ($collectionClass) {
                $script .= "
        \$this->$collName = new $collectionClass;";
            } else {
                $script .= "
        \$collectionClassName = " . $foreignTableMapName . "::getTableMap()->getCollectionClassName();

        \$this->$collName = new \$collectionClassName;";
            }

            $script .= "
        \$this->{$collName}Partial = true;";
            if ($relatedObjectClassName) {
                $script .= "
        \$this->{$collName}->setModel('$relatedObjectClassName');";
            }
            $script .= "
    }
";
        }
    }

    /**
     * Adds the method that check if the referrer fkey collection is initialized.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKIsLoaded(string &$script, CrossForeignKeys $crossFKs): void
    {
        $inits = [];

        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            $inits[] = [
                'relCol' => $this->getCrossFKsPhpNameAffix($crossFKs, true),
                'collName' => 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs)),
            ];
        } else {
            foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
                $relCol = $this->getFKPhpNameAffix($crossFK, true);
                $collName = $this->getCrossFKVarName($crossFK);

                $inits[] = [
                    'relCol' => $relCol,
                    'collName' => $collName,
                ];
            }
        }

        foreach ($inits as $init) {
            $relCol = $init['relCol'];
            $collName = $init['collName'];

            $script .= "
    /**
     * Checks if the $collName collection is loaded.
     *
     * @return bool
     */
    public function is{$relCol}Loaded(): bool
    {
        return null !== \$this->$collName;
    }
";
        }
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKCreateQuery(string &$script, CrossForeignKeys $crossFKs): void
    {
        if (1 <= count($crossFKs->getCrossForeignKeys()) && !$crossFKs->getUnclassifiedPrimaryKeys()) {
            return;
        }

        $refFK = $crossFKs->getIncomingForeignKey();
        $selfRelationName = $this->getFKPhpNameAffix($refFK, false);
        $firstFK = $crossFKs->getCrossForeignKeys()[0];
        $firstFkName = $this->getFKPhpNameAffix($firstFK, true);

        $relatedQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($firstFK->getForeignTable()));
        $signature = $shortSignature = $normalizedShortSignature = $phpDoc = [];
        $this->extractCrossInformation($crossFKs, [$firstFK], $signature, $shortSignature, $normalizedShortSignature, $phpDoc);

        $signature = array_map(function ($item) {
            return $item . ' = null';
        }, $signature);
        $signature = implode(', ', $signature);
        $phpDoc = implode(', ', $phpDoc);

        $relatedUseQueryClassName = $this->getNewStubQueryBuilder($crossFKs->getMiddleTable())->getUnqualifiedClassName();
        $relatedUseQueryGetter = 'use' . ucfirst($relatedUseQueryClassName);
        $relatedUseQueryVariableName = lcfirst($relatedUseQueryClassName);

        $script .= "
    /**
     * Returns a new query object pre configured with filters from current object and given arguments to query the database.
     * $phpDoc
     * @param Criteria \$criteria
     *
     * @return $relatedQueryClassName
     */
    public function create{$firstFkName}Query($signature, ?Criteria \$criteria = null)
    {
        \$criteria = $relatedQueryClassName::create(\$criteria)
            ->filterBy{$selfRelationName}(\$this);

        \$$relatedUseQueryVariableName = \$criteria->{$relatedUseQueryGetter}();
";

        foreach ($crossFKs->getCrossForeignKeys() as $fk) {
            if ($crossFKs->getIncomingForeignKey() === $fk || $firstFK === $fk) {
                continue;
            }

            $filterName = $fk->getPhpName();
            $name = lcfirst($fk->getPhpName());

            $script .= "
        if (null !== \$$name) {
            \${$relatedUseQueryVariableName}->filterBy{$filterName}(\$$name);
        }
            ";
        }
        foreach ($crossFKs->getUnclassifiedPrimaryKeys() as $pk) {
            $filterName = $pk->getPhpName();
            $name = lcfirst($pk->getPhpName());

            $script .= "
        if (null !== \$$name) {
            \${$relatedUseQueryVariableName}->filterBy{$filterName}(\$$name);
        }
            ";
        }

        $script .= "
        \${$relatedUseQueryVariableName}->endUse();

        return \$criteria;
    }
";
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKGet(string &$script, CrossForeignKeys $crossFKs): void
    {
        $refFK = $crossFKs->getIncomingForeignKey();
        $selfRelationName = $this->getFKPhpNameAffix($refFK, false);
        $crossRefTableName = $crossFKs->getMiddleTable()->getName();

        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            $relatedName = $this->getCrossFKsPhpNameAffix($crossFKs, true);
            $collVarName = 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs));

            $classNames = [];
            foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
                $classNames[] = $this->getClassNameFromBuilder($this->getNewStubObjectBuilder($crossFK->getForeignTable()));
            }
            $classNames = implode(', ', $classNames);
            $relatedQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($crossFKs->getMiddleTable()));

            $script .= "
    /**
     * Gets a combined collection of $classNames objects related by a many-to-many relationship
     * to the current object by way of the $crossRefTableName cross-reference table.
     *
     * If the \$criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without \$criteria, the cached collection is returned.
     * If this " . $this->getObjectClassName() . " is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria \$criteria Optional query object to filter the query
     * @param ConnectionInterface \$con Optional connection object
     *
     * @return ObjectCombinationCollection Combination list of {$classNames} objects
     */
    public function get{$relatedName}(?Criteria \$criteria = null, ?ConnectionInterface \$con = null)
    {
        \$partial = \$this->{$collVarName}Partial && !\$this->isNew();
        if (null === \$this->$collVarName || null !== \$criteria || \$partial) {
            if (\$this->isNew()) {
                // return empty collection
                if (null === \$this->$collVarName) {
                    \$this->init{$relatedName}();
                }
            } else {

                \$query = $relatedQueryClassName::create(null, \$criteria)
                    ->filterBy{$selfRelationName}(\$this)";
            foreach ($crossFKs->getCrossForeignKeys() as $fk) {
                $varName = $this->getFKPhpNameAffix($fk, false);
                $script .= "
                    ->join{$varName}()";
            }

            $script .= "
                ;

                \$items = \$query->find(\$con);
                \$$collVarName = new ObjectCombinationCollection();
                foreach (\$items as \$item) {
                    \$combination = [];
";

            foreach ($crossFKs->getCrossForeignKeys() as $fk) {
                $varName = $this->getFKPhpNameAffix($fk, false);
                $script .= "
                    \$combination[] = \$item->get{$varName}();";
            }

            foreach ($crossFKs->getUnclassifiedPrimaryKeys() as $pk) {
                $varName = $pk->getPhpName();
                $script .= "
                    \$combination[] = \$item->get{$varName}();";
            }

            $script .= "
                    \${$collVarName}[] = \$combination;
                }

                if (null !== \$criteria) {
                    return \$$collVarName;
                }

                if (\$partial && \$this->{$collVarName}) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach (\$this->{$collVarName} as \$obj) {
                        if (!\${$collVarName}->contains(...\$obj)) {
                            \${$collVarName}[] = \$obj;
                        }
                    }
                }

                \$this->$collVarName = \$$collVarName;
                \$this->{$collVarName}Partial = false;
            }
        }

        return \$this->$collVarName;
    }
";

            $relatedName = $this->getCrossFKsPhpNameAffix($crossFKs, true);
            $firstFK = $crossFKs->getCrossForeignKeys()[0];
            $firstFkName = $this->getFKPhpNameAffix($firstFK, true);

            $relatedObjectClassName = $this->getClassNameFromBuilder($this->getNewStubObjectBuilder($firstFK->getForeignTable()));
            $signature = $shortSignature = $normalizedShortSignature = $phpDoc = [];
            $this->extractCrossInformation($crossFKs, [$firstFK], $signature, $shortSignature, $normalizedShortSignature, $phpDoc);

            $signature = array_map(function ($item) {
                return $item . ' = null';
            }, $signature);
            $signature = implode(', ', $signature);
            $phpDoc = implode(', ', $phpDoc);
            $shortSignature = implode(', ', $shortSignature);

            $script .= "
    /**
     * Returns a not cached ObjectCollection of $relatedObjectClassName objects. This will hit always the databases.
     * If you have attached new $relatedObjectClassName object to this object you need to call `save` first to get
     * the correct return value. Use get$relatedName() to get the current internal state.
     * $phpDoc
     * @param Criteria \$criteria
     * @param ConnectionInterface \$con
     *
     * @return {$relatedObjectClassName}[]|ObjectCollection
     * @phpstan-return ObjectCollection&\Traversable<{$relatedObjectClassName}>
     */
    public function get{$firstFkName}($signature, ?Criteria \$criteria = null, ?ConnectionInterface \$con = null)
    {
        return \$this->create{$firstFkName}Query($shortSignature, \$criteria)->find(\$con);
    }
";

            return;
        }

        foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
            $relatedName = $this->getFKPhpNameAffix($crossFK, true);
            $relatedObjectClassName = $this->getClassNameFromBuilder($this->getNewStubObjectBuilder($crossFK->getForeignTable()));
            $relatedQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($crossFK->getForeignTable()));

            $collName = $this->getCrossFKVarName($crossFK);

            $script .= "
    /**
     * Gets a collection of $relatedObjectClassName objects related by a many-to-many relationship
     * to the current object by way of the $crossRefTableName cross-reference table.
     *
     * If the \$criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without \$criteria, the cached collection is returned.
     * If this " . $this->getObjectClassName() . " is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria \$criteria Optional query object to filter the query
     * @param ConnectionInterface \$con Optional connection object
     *
     * @return ObjectCollection|{$relatedObjectClassName}[] List of {$relatedObjectClassName} objects
     * @phpstan-return ObjectCollection&\Traversable<{$relatedObjectClassName}> List of {$relatedObjectClassName} objects
     */
    public function get{$relatedName}(?Criteria \$criteria = null, ?ConnectionInterface \$con = null)
    {
        \$partial = \$this->{$collName}Partial && !\$this->isNew();
        if (null === \$this->$collName || null !== \$criteria || \$partial) {
            if (\$this->isNew()) {
                // return empty collection
                if (null === \$this->$collName) {
                    \$this->init{$relatedName}();
                }
            } else {

                \$query = $relatedQueryClassName::create(null, \$criteria)
                    ->filterBy{$selfRelationName}(\$this);
                \$$collName = \$query->find(\$con);
                if (null !== \$criteria) {
                    return \$$collName;
                }

                if (\$partial && \$this->{$collName}) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach (\$this->{$collName} as \$obj) {
                        if (!\${$collName}->contains(\$obj)) {
                            \${$collName}[] = \$obj;
                        }
                    }
                }

                \$this->$collName = \$$collName;
                \$this->{$collName}Partial = false;
            }
        }

        return \$this->$collName;
    }
";
        }
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKSet(string &$script, CrossForeignKeys $crossFKs): void
    {
        $scheduledForDeletionVarName = $this->getCrossScheduledForDeletionVarName($crossFKs);

        $multi = 1 < count($crossFKs->getCrossForeignKeys()) || (bool)$crossFKs->getUnclassifiedPrimaryKeys();

        $relatedNamePlural = $this->getCrossFKsPhpNameAffix($crossFKs, true);
        $relatedName = $this->getCrossFKsPhpNameAffix($crossFKs, false);
        $inputCollection = lcfirst($relatedNamePlural);
        $foreachItem = lcfirst($relatedName);
        $crossRefTableName = $crossFKs->getMiddleTable()->getName();

        if ($multi) {
            [$relatedObjectClassName] = $this->getCrossFKInformation($crossFKs);
            $collName = 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs));
        } else {
            $crossFK = $crossFKs->getCrossForeignKeys()[0];
            $relatedObjectClassName = $this->getNewStubObjectBuilder($crossFK->getForeignTable())->getUnqualifiedClassName();
            $collName = $this->getCrossFKVarName($crossFK);
        }

        $script .= "
    /**
     * Sets a collection of $relatedObjectClassName objects related by a many-to-many relationship
     * to the current object by way of the $crossRefTableName cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection \${$inputCollection} A Propel collection.
     * @param ConnectionInterface \$con Optional connection object
     * @return \$this The current object (for fluent API support)
     */
    public function set{$relatedNamePlural}(Collection \${$inputCollection}, ?ConnectionInterface \$con = null)
    {
        \$this->clear{$relatedNamePlural}();
        \$current{$relatedNamePlural} = \$this->get{$relatedNamePlural}();

        \${$scheduledForDeletionVarName} = \$current{$relatedNamePlural}->diff(\${$inputCollection});

        foreach (\${$scheduledForDeletionVarName} as \$toDelete) {";
        if ($multi) {
            $script .= "
            \$this->remove{$relatedName}(...\$toDelete);";
        } else {
            $script .= "
            \$this->remove{$relatedName}(\$toDelete);";
        }
        $script .= "
        }

        foreach (\${$inputCollection} as \${$foreachItem}) {";
        if ($multi) {
            $script .= "
            if (!\$current{$relatedNamePlural}->contains(...\${$foreachItem})) {
                \$this->doAdd{$relatedName}(...\${$foreachItem});
            }";
        } else {
            $script .= "
            if (!\$current{$relatedNamePlural}->contains(\${$foreachItem})) {
                \$this->doAdd{$relatedName}(\${$foreachItem});
            }";
        }
        $script .= "
        }

        \$this->{$collName}Partial = false;
        \$this->$collName = \${$inputCollection};

        return \$this;
    }
";
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKCount(string &$script, CrossForeignKeys $crossFKs): void
    {
        $refFK = $crossFKs->getIncomingForeignKey();
        $selfRelationName = $this->getFKPhpNameAffix($refFK, false);

        $multi = 1 < count($crossFKs->getCrossForeignKeys()) || (bool)$crossFKs->getUnclassifiedPrimaryKeys();

        $relatedName = $this->getCrossFKsPhpNameAffix($crossFKs, true);
        $crossRefTableName = $crossFKs->getMiddleTable()->getName();

        if ($multi) {
            [$relatedObjectClassName] = $this->getCrossFKInformation($crossFKs);
            $collName = 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs));
            $relatedQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($crossFKs->getMiddleTable()));
        } else {
            $crossFK = $crossFKs->getCrossForeignKeys()[0];
            $relatedObjectClassName = $this->getNewStubObjectBuilder($crossFK->getForeignTable())->getUnqualifiedClassName();
            $collName = $this->getCrossFKVarName($crossFK);
            $relatedQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($crossFK->getForeignTable()));
        }

        $script .= "
    /**
     * Gets the number of $relatedObjectClassName objects related by a many-to-many relationship
     * to the current object by way of the $crossRefTableName cross-reference table.
     *
     * @param Criteria \$criteria Optional query object to filter the query
     * @param bool \$distinct Set to true to force count distinct
     * @param ConnectionInterface \$con Optional connection object
     *
     * @return int The number of related $relatedObjectClassName objects
     */
    public function count{$relatedName}(?Criteria \$criteria = null, \$distinct = false, ?ConnectionInterface \$con = null): int
    {
        \$partial = \$this->{$collName}Partial && !\$this->isNew();
        if (null === \$this->$collName || null !== \$criteria || \$partial) {
            if (\$this->isNew() && null === \$this->$collName) {
                return 0;
            } else {

                if (\$partial && !\$criteria) {
                    return count(\$this->get$relatedName());
                }

                \$query = $relatedQueryClassName::create(null, \$criteria);
                if (\$distinct) {
                    \$query->distinct();
                }

                return \$query
                    ->filterBy{$selfRelationName}(\$this)
                    ->count(\$con);
            }
        } else {
            return count(\$this->$collName);
        }
    }
";

        if ($multi) {
            $relatedName = $this->getCrossFKsPhpNameAffix($crossFKs, true);
            $firstFK = $crossFKs->getCrossForeignKeys()[0];
            $firstFkName = $this->getFKPhpNameAffix($firstFK, true);

            $relatedObjectClassName = $this->getClassNameFromBuilder($this->getNewStubObjectBuilder($firstFK->getForeignTable()));
            $signature = $shortSignature = $normalizedShortSignature = $phpDoc = [];
            $this->extractCrossInformation($crossFKs, [$firstFK], $signature, $shortSignature, $normalizedShortSignature, $phpDoc);

            $signature = array_map(function ($item) {
                return $item . ' = null';
            }, $signature);
            $signature = implode(', ', $signature);
            $phpDoc = implode(', ', $phpDoc);
            $shortSignature = implode(', ', $shortSignature);

            $script .= "
    /**
     * Returns the not cached count of $relatedObjectClassName objects. This will hit always the databases.
     * If you have attached new $relatedObjectClassName object to this object you need to call `save` first to get
     * the correct return value. Use get$relatedName() to get the current internal state.
     * $phpDoc
     * @param Criteria \$criteria
     * @param ConnectionInterface \$con
     *
     * @return int
     */
    public function count{$firstFkName}($signature, ?Criteria \$criteria = null, ?ConnectionInterface \$con = null): int
    {
        return \$this->create{$firstFkName}Query($shortSignature, \$criteria)->count(\$con);
    }
";
        }
    }

    /**
     * Adds the method that adds an object into the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKAdd(string &$script, CrossForeignKeys $crossFKs): void
    {
        $refFK = $crossFKs->getIncomingForeignKey();

        foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
            $relSingleNamePlural = $this->getFKPhpNameAffix($crossFK, true);
            $relSingleName = $this->getFKPhpNameAffix($crossFK, false);
            $collSingleName = $this->getCrossFKVarName($crossFK);

            $relCombineNamePlural = $this->getCrossFKsPhpNameAffix($crossFKs, true);
            $relCombineName = $this->getCrossFKsPhpNameAffix($crossFKs, false);
            $collCombinationVarName = 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs));

            $collName = 1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys() ? $collCombinationVarName : $collSingleName;
            $relNamePlural = ucfirst(1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys() ? $relCombineNamePlural : $relSingleNamePlural);
            $relName = ucfirst(1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys() ? $relCombineName : $relSingleName);

            $tblFK = $refFK->getTable();
            $relatedObjectClassName = $this->getFKPhpNameAffix($crossFK, false);
            $crossObjectClassName = $this->getClassNameFromTable($crossFK->getForeignTable());
            [$signature, $shortSignature, $normalizedShortSignature, $phpDoc] = $this->getCrossFKAddMethodInformation($crossFKs, $crossFK);

            $script .= "
    /**
     * Associate a $crossObjectClassName to this object
     * through the " . $tblFK->getName() . " cross reference table.
     * $phpDoc
     * @return " . $this->getObjectClassname() . " The current object (for fluent API support)
     */
    public function add{$relatedObjectClassName}($signature)
    {
        if (\$this->" . $collName . " === null) {
            \$this->init" . $relNamePlural . "();
        }

        if (!\$this->get" . $relNamePlural . '()->contains(' . $normalizedShortSignature . ")) {
            // only add it if the **same** object is not already associated
            \$this->" . $collName . '->push(' . $normalizedShortSignature . ");
            \$this->doAdd{$relName}($normalizedShortSignature);
        }

        return \$this;
    }
";
        }
    }

    /**
     * Returns a function signature comma separated.
     *
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     * @param string $excludeSignatureItem Which variable to exclude.
     *
     * @return string
     */
    protected function getCrossFKGetterSignature(CrossForeignKeys $crossFKs, string $excludeSignatureItem): string
    {
        [, $getSignature] = $this->getCrossFKAddMethodInformation($crossFKs);
        $getSignature = explode(', ', $getSignature);

        $pos = array_search($excludeSignatureItem, $getSignature);
        if ($pos !== false) {
            unset($getSignature[$pos]);
        }

        return implode(', ', $getSignature);
    }

    /**
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKDoAdd(string &$script, CrossForeignKeys $crossFKs): void
    {
        $selfRelationNamePlural = $this->getFKPhpNameAffix($crossFKs->getIncomingForeignKey(), true);
        $relatedObjectClassName = $this->getCrossFKsPhpNameAffix($crossFKs, false);
        $className = $this->getClassNameFromTable($crossFKs->getIncomingForeignKey()->getTable());

        $refKObjectClassName = $this->getRefFKPhpNameAffix($crossFKs->getIncomingForeignKey(), false);
        $tblFK = $crossFKs->getIncomingForeignKey()->getTable();
        $foreignObjectName = '$' . $tblFK->getCamelCaseName();

        [$signature, $shortSignature, $normalizedShortSignature, $phpDoc] = $this->getCrossFKAddMethodInformation($crossFKs);

        $script .= "
    /**
     * {$phpDoc}
     */
    protected function doAdd{$relatedObjectClassName}($signature)
    {
        {$foreignObjectName} = new {$className}();
";
        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
                $relatedObjectClassName = $this->getFKPhpNameAffix($crossFK, false);
                $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);
                $script .= "
        {$foreignObjectName}->set{$relatedObjectClassName}(\${$lowerRelatedObjectClassName});";
            }

            foreach ($crossFKs->getUnclassifiedPrimaryKeys() as $primaryKey) {
                $paramName = lcfirst($primaryKey->getPhpName());
                $script .= "
        {$foreignObjectName}->set{$primaryKey->getPhpName()}(\$$paramName);
";
            }
        } else {
            $crossFK = $crossFKs->getCrossForeignKeys()[0];
            $relatedObjectClassName = $this->getFKPhpNameAffix($crossFK, false);
            $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);
            $script .= "
        {$foreignObjectName}->set{$relatedObjectClassName}(\${$lowerRelatedObjectClassName});";
        }

        $refFK = $crossFKs->getIncomingForeignKey();
        $script .= "

        {$foreignObjectName}->set" . $this->getFKPhpNameAffix($refFK, false) . "(\$this);

        \$this->add{$refKObjectClassName}({$foreignObjectName});\n";

        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
                $relatedObjectClassName = $this->getFKPhpNameAffix($crossFK, false);
                $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);

                $getterName = $this->getCrossRefFKGetterName($crossFKs, $crossFK);
                $getterRemoveObjectName = $this->getCrossRefFKRemoveObjectNames($crossFKs, $crossFK);

                $script .= "
        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (\${$lowerRelatedObjectClassName}->is{$getterName}Loaded()) {
            \${$lowerRelatedObjectClassName}->init{$getterName}();
            \${$lowerRelatedObjectClassName}->get{$getterName}()->push($getterRemoveObjectName);
        } elseif (!\${$lowerRelatedObjectClassName}->get{$getterName}()->contains($getterRemoveObjectName)) {
            \${$lowerRelatedObjectClassName}->get{$getterName}()->push($getterRemoveObjectName);
        }\n";
            }
        } else {
            $relatedObjectClassName = $this->getFKPhpNameAffix($crossFK, false);
            $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);
            $getterSignature = $this->getCrossFKGetterSignature($crossFKs, '$' . $lowerRelatedObjectClassName);
            $script .= "
        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!\${$lowerRelatedObjectClassName}->is{$selfRelationNamePlural}Loaded()) {
            \${$lowerRelatedObjectClassName}->init{$selfRelationNamePlural}();
            \${$lowerRelatedObjectClassName}->get{$selfRelationNamePlural}($getterSignature)->push(\$this);
        } elseif (!\${$lowerRelatedObjectClassName}->get{$selfRelationNamePlural}($getterSignature)->contains(\$this)) {
            \${$lowerRelatedObjectClassName}->get{$selfRelationNamePlural}($getterSignature)->push(\$this);
        }\n";
        }

        $script .= "
    }
";
    }

    /**
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     * @param \Propel\Generator\Model\ForeignKey $excludeFK
     *
     * @return string
     */
    protected function getCrossRefFKRemoveObjectNames(CrossForeignKeys $crossFKs, ForeignKey $excludeFK): string
    {
        $names = [];

        $fks = $crossFKs->getCrossForeignKeys();

        foreach ($crossFKs->getMiddleTable()->getForeignKeys() as $fk) {
            if ($fk !== $excludeFK && ($fk === $crossFKs->getIncomingForeignKey() || in_array($fk, $fks))) {
                if ($fk === $crossFKs->getIncomingForeignKey()) {
                    $names[] = '$this';
                } else {
                    $names[] = '$' . lcfirst($this->getFKPhpNameAffix($fk, false));
                }
            }
        }

        foreach ($crossFKs->getUnclassifiedPrimaryKeys() as $pk) {
            $names[] = '$' . lcfirst($pk->getPhpName());
        }

        return implode(', ', $names);
    }

    /**
     * Adds the method that remove an object from the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\CrossForeignKeys $crossFKs
     *
     * @return void
     */
    protected function addCrossFKRemove(string &$script, CrossForeignKeys $crossFKs): void
    {
        $relCol = $this->getCrossFKsPhpNameAffix($crossFKs, true);
        if (1 < count($crossFKs->getCrossForeignKeys()) || $crossFKs->getUnclassifiedPrimaryKeys()) {
            $collName = 'combination' . ucfirst($this->getCrossFKsVarName($crossFKs));
        } else {
            $collName = $this->getCrossFKsVarName($crossFKs);
        }

        $tblFK = $crossFKs->getIncomingForeignKey()->getTable();

        $M2MScheduledForDeletion = $this->getCrossScheduledForDeletionVarName($crossFKs);
        $relatedObjectClassName = $this->getCrossFKsPhpNameAffix($crossFKs, false);

        [$signature, $shortSignature, $normalizedShortSignature, $phpDoc] = $this->getCrossFKAddMethodInformation($crossFKs);
        $names = str_replace('$', '', $normalizedShortSignature);

        $className = $this->getClassNameFromTable($crossFKs->getIncomingForeignKey()->getTable());
        $refKObjectClassName = $this->getRefFKPhpNameAffix($crossFKs->getIncomingForeignKey(), false);
        $foreignObjectName = '$' . $tblFK->getCamelCaseName();

        $script .= "
    /**
     * Remove $names of this object
     * through the {$tblFK->getName()} cross reference table.
     * $phpDoc
     * @return " . $this->getObjectClassname() . " The current object (for fluent API support)
     */
    public function remove{$relatedObjectClassName}($signature)
    {
        if (\$this->get{$relCol}()->contains({$shortSignature})) {
            {$foreignObjectName} = new {$className}();";
        foreach ($crossFKs->getCrossForeignKeys() as $crossFK) {
            $relatedObjectClassName = $this->getFKPhpNameAffix($crossFK, false);
            $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);

            $relatedObjectClassName = $this->getFKPhpNameAffix($crossFK, false);
            $script .= "
            {$foreignObjectName}->set{$relatedObjectClassName}(\${$lowerRelatedObjectClassName});";

            $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);

            $getterName = $this->getCrossRefFKGetterName($crossFKs, $crossFK);
            $getterRemoveObjectName = $this->getCrossRefFKRemoveObjectNames($crossFKs, $crossFK);

            $script .= "
            if (\${$lowerRelatedObjectClassName}->is{$getterName}Loaded()) {
                //remove the back reference if available
                \${$lowerRelatedObjectClassName}->get$getterName()->removeObject($getterRemoveObjectName);
            }\n";
        }

        foreach ($crossFKs->getUnclassifiedPrimaryKeys() as $primaryKey) {
            $paramName = lcfirst($primaryKey->getPhpName());
            $script .= "
            {$foreignObjectName}->set{$primaryKey->getPhpName()}(\$$paramName);";
        }
        $script .= "
            {$foreignObjectName}->set{$this->getFKPhpNameAffix($crossFKs->getIncomingForeignKey())}(\$this);";

        $script .= "
            \$this->remove{$refKObjectClassName}(clone {$foreignObjectName});
            {$foreignObjectName}->clear();

            \$this->{$collName}->remove(\$this->{$collName}->search({$shortSignature}));
            ";
        $script .= "
            if (null === \$this->{$M2MScheduledForDeletion}) {
                \$this->{$M2MScheduledForDeletion} = clone \$this->{$collName};
                \$this->{$M2MScheduledForDeletion}->clear();
            }

            \$this->{$M2MScheduledForDeletion}->push({$shortSignature});
        }
";

        $script .= "

        return \$this;
    }
";
    }
}
