<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Om;

use Propel\Generator\Model\ForeignKey;

/**
 * Trait containing referrer (RefFK) related methods for ObjectBuilder.
 *
 * This trait handles the generation of methods for objects that reference
 * the current table via foreign keys (referrers/back-references).
 */
trait ReferrerBuilderTrait
{
    /**
     * Constructs variable name for objects which referencing current table by specified foreign key.
     *
     * @param \Propel\Generator\Model\ForeignKey $fk
     *
     * @return string
     */
    public function getRefFKCollVarName(ForeignKey $fk): string
    {
        return 'coll' . $this->getRefFKPhpNameAffix($fk, true);
    }

    /**
     * Constructs variable name for single object which references current table by specified foreign key
     * which is ALSO a primary key (hence one-to-one relationship).
     *
     * @param \Propel\Generator\Model\ForeignKey $fk
     *
     * @return string
     */
    public function getPKRefFKVarName(ForeignKey $fk): string
    {
        return 'single' . $this->getRefFKPhpNameAffix($fk, false);
    }

    /**
     * Adds the method that fetches fkey-related (referencing) objects but also joins in data from another table.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKGetJoinMethods(string &$script, ForeignKey $refFK): void
    {
        $table = $this->getTable();
        $tblFK = $refFK->getTable();
        $joinBehavior = $this->getBuildProperty('generator.objectModel.useLeftJoinsInDoJoinMethods') ? 'Criteria::LEFT_JOIN' : 'Criteria::INNER_JOIN';

        $fkQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($refFK->getTable()));
        $relCol = $this->getRefFKPhpNameAffix($refFK, true);

        $className = $this->getClassNameFromTable($tblFK);

        foreach ($tblFK->getForeignKeys() as $fk2) {
            $tblFK2 = $fk2->getForeignTable();
            $doJoinGet = !$tblFK2->isForReferenceOnly();

            // it doesn't make sense to join in rows from the current table, since we are fetching
            // objects related to *this* table (i.e. the joined rows will all be the same row as current object)
            if ($this->getTable()->getPhpName() === $tblFK2->getPhpName()) {
                $doJoinGet = false;
            }

            $relCol2 = $this->getFKPhpNameAffix($fk2, false);

            if (
                $this->getRelatedBySuffix($refFK) !== '' &&
                ($this->getRelatedBySuffix($refFK) === $this->getRelatedBySuffix($fk2))
            ) {
                $doJoinGet = false;
            }

            if ($doJoinGet) {
                $script .= "

    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this " . $table->getPhpName() . " is new, it will return
     * an empty collection; or if this " . $table->getPhpName() . " has previously
     * been saved, it will retrieve related $relCol from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in " . $table->getPhpName() . ".
     *
     * @param Criteria \$criteria optional Criteria object to narrow the query
     * @param ConnectionInterface \$con optional connection object
     * @param string \$joinBehavior optional join type to use (defaults to $joinBehavior)
     * @return ObjectCollection|{$className}[] List of $className objects
     * @phpstan-return ObjectCollection&\Traversable<{$className}> List of $className objects
     */
    public function get" . $relCol . 'Join' . $relCol2 . "(?Criteria \$criteria = null, ?ConnectionInterface \$con = null, \$joinBehavior = $joinBehavior)
    {";
                $script .= "
        \$query = $fkQueryClassName::create(null, \$criteria);
        \$query->joinWith('" . $this->getFKPhpNameAffix($fk2, false) . "', \$joinBehavior);

        return \$this->get" . $relCol . "(\$query, \$con);
    }
";
            } /* end if ($doJoinGet) */
        } /* end foreach ($tblFK->getForeignKeys() as $fk2) { */
    }

    /**
     * Adds the attributes used to store objects that have referrer fkey relationships to this object.
     * <code>protected collVarName;</code>
     * <code>private lastVarNameCriteria = null;</code>
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKAttributes(string &$script, ForeignKey $refFK): void
    {
        $className = $this->getClassNameFromTable($refFK->getTable());

        if ($refFK->isLocalPrimaryKey()) {
            $script .= "
    /**
     * @var        $className|null one-to-one related $className object
     */
    protected ?$className $" . $this->getPKRefFKVarName($refFK) . " = null;
";
        } else {
            $script .= "
    /**
     * @var        ObjectCollection|{$className}[] Collection to store aggregation of $className objects.
     * @phpstan-var ObjectCollection&\Traversable<{$className}> Collection to store aggregation of $className objects.
     */
    protected $" . $this->getRefFKCollVarName($refFK) . ";
    protected bool $" . $this->getRefFKCollVarName($refFK) . "Partial = false;
";
        }
    }

    /**
     * Adds the methods for retrieving, initializing, adding objects that are related to this one by foreign keys.
     *
     * @param string $script The script will be modified in this method.
     *
     * @return void
     */
    protected function addRefFKMethods(string &$script): void
    {
        $referrers = $this->getTable()->getReferrers();
        if (!$referrers) {
            return;
        }

        $this->addInitRelations($script, $referrers);
        foreach ($referrers as $refFK) {
            $this->declareClassFromBuilder($this->getNewStubObjectBuilder($refFK->getTable()), 'Child');
            $this->declareClassFromBuilder($this->getNewStubQueryBuilder($refFK->getTable()));
            if ($refFK->isLocalPrimaryKey()) {
                $this->addPKRefFKGet($script, $refFK);
                $this->addPKRefFKSet($script, $refFK);
            } else {
                $this->addRefFKClear($script, $refFK);
                $this->addRefFKPartial($script, $refFK);
                $this->addRefFKInit($script, $refFK);
                $this->addRefFKGet($script, $refFK);
                $this->addRefFKSet($script, $refFK);
                $this->addRefFKCount($script, $refFK);
                $this->addRefFKAdd($script, $refFK);
                $this->addRefFKDoAdd($script, $refFK);
                $this->addRefFKRemove($script, $refFK);
                $this->addRefFKGetJoinMethods($script, $refFK);
            }
        }
    }

    /**
     * @param string $script
     * @param array<\Propel\Generator\Model\ForeignKey> $referrers
     *
     * @return void
     */
    protected function addInitRelations(string &$script, array $referrers): void
    {
        $script .= "

    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[\$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param string \$relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation(\$relationName): void
    {";
        foreach ($referrers as $refFK) {
            if (!$refFK->isLocalPrimaryKey()) {
                $relationName = $this->getRefFKPhpNameAffix($refFK);
                $relCol = $this->getRefFKPhpNameAffix($refFK, true);
                $script .= "
        if ('$relationName' === \$relationName) {
            \$this->init$relCol();
            return;
        }";
            }
        }
        $script .= "
    }
";
    }

    /**
     * Adds the method that clears the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKClear(string &$script, ForeignKey $refFK): void
    {
        $relCol = $this->getRefFKPhpNameAffix($refFK, true);
        $collName = $this->getRefFKCollVarName($refFK);

        $script .= "
    /**
     * Clears out the $collName collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return \$this
     * @see add$relCol()
     */
    public function clear$relCol()
    {
        \$this->$collName = null; // important to set this to NULL since that means it is uninitialized

        return \$this;
    }
";
    }

    /**
     * Adds the method that initializes the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKInit(string &$script, ForeignKey $refFK): void
    {
        $relCol = $this->getRefFKPhpNameAffix($refFK, true);
        $collName = $this->getRefFKCollVarName($refFK);
        $collectionClassNameExpr = $this->getClassNameFromBuilder($this->getNewTableMapBuilder($refFK->getTable())) . '::getTableMap()->getCollectionClassName()';
        $modelFqcn = $this->getClassNameFromBuilder($this->getNewStubObjectBuilder($refFK->getTable()), true);

        if ($this->getTable()->useLazyObjects()) {
            $script .= "\n" . (new LazyRelationBuilder())->emit(
                $relCol,
                $collName,
                $collectionClassNameExpr,
                $modelFqcn,
            ) . "\n";

            return;
        }

        $script .= "
    /**
     * Initializes the $collName collection.
     *
     * By default this just sets the $collName collection to an empty array (like clear$collName());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param bool \$overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function init$relCol(bool \$overrideExisting = true): void
    {
        if (null !== \$this->$collName && !\$overrideExisting) {
            return;
        }

        \$collectionClassName = " . $collectionClassNameExpr . ";

        \$this->{$collName} = new \$collectionClassName;
        \$this->{$collName}->setModel('" . $modelFqcn . "');
    }
";
    }

    /**
     * Adds the method that adds an object into the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKAdd(string &$script, ForeignKey $refFK): void
    {
        $className = $this->getClassNameFromTable($refFK->getTable());

        $collName = $this->getRefFKCollVarName($refFK);

        $scheduledForDeletion = lcfirst($this->getRefFKPhpNameAffix($refFK, true)) . 'ScheduledForDeletion';

        $script .= "
    /**
     * Method called to associate a $className object to this object
     * through the $className foreign key attribute.
     *
     * @param $className \$l $className
     * @return \$this The current object (for fluent API support)
     */
    public function add" . $this->getRefFKPhpNameAffix($refFK, false) . "($className \$l)
    {
        if (\$this->$collName === null) {
            \$this->init" . $this->getRefFKPhpNameAffix($refFK, true) . "();
            \$this->{$collName}Partial = true;
        }

        if (!\$this->{$collName}->contains(\$l)) {
            \$this->doAdd" . $this->getRefFKPhpNameAffix($refFK, false) . "(\$l);

            if (\$this->{$scheduledForDeletion} and \$this->{$scheduledForDeletion}->contains(\$l)) {
                \$this->{$scheduledForDeletion}->remove(\$this->{$scheduledForDeletion}->search(\$l));
            }
        }

        return \$this;
    }
";
    }

    /**
     * Adds the method that returns the size of the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKCount(string &$script, ForeignKey $refFK): void
    {
        $fkQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($refFK->getTable()));
        $relCol = $this->getRefFKPhpNameAffix($refFK, true);
        $collName = $this->getRefFKCollVarName($refFK);

        $joinedTableObjectBuilder = $this->getNewObjectBuilder($refFK->getTable());
        $className = $this->getClassNameFromBuilder($joinedTableObjectBuilder);

        $script .= "
    /**
     * Returns the number of related $className objects.
     *
     * @param Criteria \$criteria
     * @param bool \$distinct
     * @param ConnectionInterface \$con
     * @return int Count of related $className objects.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function count{$relCol}(?Criteria \$criteria = null, bool \$distinct = false, ?ConnectionInterface \$con = null): int
    {
        \$partial = \$this->{$collName}Partial && !\$this->isNew();
        if (null === \$this->$collName || null !== \$criteria || \$partial) {
            if (\$this->isNew() && null === \$this->$collName) {
                return 0;
            }

            if (\$partial && !\$criteria) {
                return count(\$this->get$relCol());
            }

            \$query = $fkQueryClassName::create(null, \$criteria);
            if (\$distinct) {
                \$query->distinct();
            }

            return \$query
                ->filterBy" . $this->getFKPhpNameAffix($refFK) . "(\$this)
                ->count(\$con);
        }

        return count(\$this->$collName);
    }
";
    }

    /**
     * Adds the method that returns the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKGet(string &$script, ForeignKey $refFK): void
    {
        $fkQueryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($refFK->getTable()));
        $relCol = $this->getRefFKPhpNameAffix($refFK, true);
        $collName = $this->getRefFKCollVarName($refFK);

        $className = $this->getClassNameFromTable($refFK->getTable());

        $script .= "
    /**
     * Gets an array of $className objects which contain a foreign key that references this object.
     *
     * If the \$criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without \$criteria, the cached collection is returned.
     * If this " . $this->getObjectClassName() . " is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria \$criteria optional Criteria object to narrow the query
     * @param ConnectionInterface \$con optional connection object
     * @return ObjectCollection|{$className}[] List of $className objects
     * @phpstan-return ObjectCollection&\Traversable<{$className}> List of $className objects
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function get$relCol(?Criteria \$criteria = null, ?ConnectionInterface \$con = null)
    {
        \$partial = \$this->{$collName}Partial && !\$this->isNew();
        if (null === \$this->$collName || null !== \$criteria || \$partial) {
            if (\$this->isNew()) {
                // return empty collection
                if (null === \$this->$collName) {
                    \$this->init" . $this->getRefFKPhpNameAffix($refFK, true) . "();
                } else {
                    \$collectionClassName = " . $this->getClassNameFromBuilder($this->getNewTableMapBuilder($refFK->getTable())) . "::getTableMap()->getCollectionClassName();

                    \$$collName = new \$collectionClassName;
                    \${$collName}->setModel('" . $this->getClassNameFromBuilder($this->getNewStubObjectBuilder($refFK->getTable()), true) . "');

                    return \$$collName;
                }
            } else {
                \$$collName = $fkQueryClassName::create(null, \$criteria)
                    ->filterBy" . $this->getFKPhpNameAffix($refFK) . "(\$this)
                    ->find(\$con);

                if (null !== \$criteria) {
                    if (false !== \$this->{$collName}Partial && count(\$$collName)) {
                        \$this->init" . $this->getRefFKPhpNameAffix($refFK, true) . "(false);

                        foreach (\$$collName as \$obj) {
                            if (false === \$this->{$collName}->contains(\$obj)) {
                                \$this->{$collName}->append(\$obj);
                            }
                        }

                        \$this->{$collName}Partial = true;
                    }

                    return \$$collName;
                }

                if (\$partial && \$this->$collName) {
                    foreach (\$this->$collName as \$obj) {
                        if (\$obj->isNew()) {
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

    /**
     * @param string $script
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKSet(string &$script, ForeignKey $refFK): void
    {
        $relatedName = $this->getRefFKPhpNameAffix($refFK, true);
        $relatedObjectClassName = $this->getRefFKPhpNameAffix($refFK, false);

        $className = $this->getClassNameFromTable($refFK->getTable());

        $inputCollection = lcfirst($relatedName);
        $inputCollectionEntry = lcfirst($this->getRefFKPhpNameAffix($refFK, false));

        $collName = $this->getRefFKCollVarName($refFK);
        $relCol = $this->getFKPhpNameAffix($refFK, false);

        $script .= "
    /**
     * Sets a collection of $className objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param Collection \${$inputCollection} A Propel collection.
     * @param ConnectionInterface \$con Optional connection object
     * @return \$this The current object (for fluent API support)
     */
    public function set{$relatedName}(Collection \${$inputCollection}, ?ConnectionInterface \$con = null)
    {
        /** @var {$className}[] \${$inputCollection}ToDelete */
        \${$inputCollection}ToDelete = \$this->get{$relatedName}(new Criteria(), \$con)->diff(\${$inputCollection});

        ";

        if ($refFK->isAtLeastOneLocalPrimaryKey()) {
            $script .= "
        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        \$this->{$inputCollection}ScheduledForDeletion = clone \${$inputCollection}ToDelete;
";
        } else {
            $script .= "
        \$this->{$inputCollection}ScheduledForDeletion = \${$inputCollection}ToDelete;
";
        }

        $script .= "
        foreach (\${$inputCollection}ToDelete as \${$inputCollectionEntry}Removed) {
            \${$inputCollectionEntry}Removed->set{$relCol}(null);
        }

        \$this->{$collName} = null;
        foreach (\${$inputCollection} as \${$inputCollectionEntry}) {
            \$this->add{$relatedObjectClassName}(\${$inputCollectionEntry});
        }

        \$this->{$collName} = \${$inputCollection};
        \$this->{$collName}Partial = false;

        return \$this;
    }
";
    }

    /**
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKDoAdd(string &$script, ForeignKey $refFK): void
    {
        $className = $this->getClassNameFromTable($refFK->getTable());

        $relatedObjectClassName = $this->getRefFKPhpNameAffix($refFK, false);
        $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);
        $collName = $this->getRefFKCollVarName($refFK);

        $script .= "
    /**
     * @param {$className} \${$lowerRelatedObjectClassName} The $className object to add.
     */
    protected function doAdd{$relatedObjectClassName}($className \${$lowerRelatedObjectClassName}): void
    {
        \$this->{$collName}[]= \${$lowerRelatedObjectClassName};
        \${$lowerRelatedObjectClassName}->set" . $this->getFKPhpNameAffix($refFK, false) . "(\$this);
    }
";
    }

    /**
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKRemove(string &$script, ForeignKey $refFK): void
    {
        $className = $this->getClassNameFromTable($refFK->getTable());

        $relatedName = $this->getRefFKPhpNameAffix($refFK, true);
        $relatedObjectClassName = $this->getRefFKPhpNameAffix($refFK, false);
        $inputCollection = lcfirst($relatedName . 'ScheduledForDeletion');
        $lowerRelatedObjectClassName = lcfirst($relatedObjectClassName);

        $collName = $this->getRefFKCollVarName($refFK);
        $relCol = $this->getFKPhpNameAffix($refFK, false);
        $localColumn = $refFK->getLocalColumn();

        $script .= "
    /**
     * @param {$className} \${$lowerRelatedObjectClassName} The $className object to remove.
     * @return \$this The current object (for fluent API support)
     */
    public function remove{$relatedObjectClassName}($className \${$lowerRelatedObjectClassName})
    {
        if (\$this->get{$relatedName}()->contains(\${$lowerRelatedObjectClassName})) {
            \$pos = \$this->{$collName}->search(\${$lowerRelatedObjectClassName});
            \$this->{$collName}->remove(\$pos);
            if (null === \$this->{$inputCollection}) {
                \$this->{$inputCollection} = clone \$this->{$collName};
                \$this->{$inputCollection}->clear();
            }";

        if (!$refFK->isComposite() && !$localColumn->isNotNull()) {
            $script .= "
            \$this->{$inputCollection}[]= \${$lowerRelatedObjectClassName};";
        } else {
            $script .= "
            \$this->{$inputCollection}[]= clone \${$lowerRelatedObjectClassName};";
        }

        $script .= "
            \${$lowerRelatedObjectClassName}->set{$relCol}(null);
        }

        return \$this;
    }
";
    }

    /**
     * Adds the method that gets a one-to-one related referrer fkey.
     * This is for one-to-one relationship special case.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addPKRefFKGet(string &$script, ForeignKey $refFK): void
    {
        $className = $this->getClassNameFromTable($refFK->getTable());

        $queryClassName = $this->getClassNameFromBuilder($this->getNewStubQueryBuilder($refFK->getTable()));

        $varName = $this->getPKRefFKVarName($refFK);

        $script .= "
    /**
     * Gets a single $className object, which is related to this object by a one-to-one relationship.
     *
     * @param ConnectionInterface \$con optional connection object
     * @return $className|null
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function get" . $this->getRefFKPhpNameAffix($refFK, false) . "(?ConnectionInterface \$con = null)
    {
";
        $script .= "
        if (\$this->$varName === null && !\$this->isNew()) {
            \$this->$varName = $queryClassName::create()->findPk(\$this->getPrimaryKey(), \$con);
        }

        return \$this->$varName;
    }
";
    }

    /**
     * Adds the method that sets a one-to-one related referrer fkey.
     * This is for one-to-one relationships special case.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK The referencing foreign key.
     *
     * @return void
     */
    protected function addPKRefFKSet(string &$script, ForeignKey $refFK): void
    {
        $className = $this->getClassNameFromTable($refFK->getTable());

        $varName = $this->getPKRefFKVarName($refFK);

        $script .= "
    /**
     * Sets a single $className object as related to this object by a one-to-one relationship.
     *
     * @param $className|null \$v $className
     * @return \$this The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function set" . $this->getRefFKPhpNameAffix($refFK, false) . "(?$className \$v = null)
    {
        \$this->$varName = \$v;

        // Make sure that that the passed-in $className isn't already associated with this object
        if (\$v !== null && \$v->get" . $this->getFKPhpNameAffix($refFK, false) . "(null, false) === null) {
            \$v->set" . $this->getFKPhpNameAffix($refFK, false) . "(\$this);
        }

        return \$this;
    }
";
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFkScheduledForDeletionAttribute(string &$script, ForeignKey $refFK): void
    {
        $className = $this->getClassNameFromTable($refFK->getTable());
        $fkName = lcfirst($this->getRefFKPhpNameAffix($refFK, true));

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
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFkScheduledForDeletion(string &$script, ForeignKey $refFK): void
    {
        $relatedName = $this->getRefFKPhpNameAffix($refFK, true);
        $lowerRelatedName = lcfirst($relatedName);
        $lowerSingleRelatedName = lcfirst($this->getRefFKPhpNameAffix($refFK, false));
        $queryClassName = $this->getNewStubQueryBuilder($refFK->getTable())->getClassname();

        $script .= "
            if (\$this->{$lowerRelatedName}ScheduledForDeletion !== null) {
                if (!\$this->{$lowerRelatedName}ScheduledForDeletion->isEmpty()) {";

        if ($refFK->isLocalColumnsRequired() || $refFK->getOnDelete() === ForeignKey::CASCADE) {
            $script .= "
                    $queryClassName::create()
                        ->filterByPrimaryKeys(\$this->{$lowerRelatedName}ScheduledForDeletion->getPrimaryKeys(false))
                        ->delete(\$con);";
        } else {
            $script .= "
                    foreach (\$this->{$lowerRelatedName}ScheduledForDeletion as \${$lowerSingleRelatedName}) {
                        // need to save related object because we set the relation to null
                        \${$lowerSingleRelatedName}->save(\$con);
                    }";
        }

        $script .= "
                    \$this->{$lowerRelatedName}ScheduledForDeletion = null;
                }
            }
";
    }

    /**
     * Adds the method that clears the referrer fkey collection.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\ForeignKey $refFK
     *
     * @return void
     */
    protected function addRefFKPartial(string &$script, ForeignKey $refFK): void
    {
        $relCol = $this->getRefFKPhpNameAffix($refFK, true);
        $collName = $this->getRefFKCollVarName($refFK);

        $script .= "
    /**
     * Reset is the $collName collection loaded partially.
     *
     * @return void
     */
    public function resetPartial{$relCol}(\$v = true): void
    {
        \$this->{$collName}Partial = \$v;
    }
";
    }
}
