<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Om;

use Propel\Generator\Model\Column;
use Propel\Generator\Model\PropelTypes;

/**
 * Trait containing column mutator (setter) generation methods for ObjectBuilder.
 *
 * This trait provides methods for generating setter methods for various column types
 * including LOB, temporal, enum, set, array, JSON, boolean, and default mutators.
 */
trait ColumnMutatorBuilderTrait
{
    /**
     * Adds the open of the mutator (setter) method for a column.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addMutatorOpen(string &$script, Column $column): void
    {
        $this->addMutatorComment($script, $column);
        $this->addMutatorOpenOpen($script, $column);
        $this->addMutatorOpenBody($script, $column);
    }

    /**
     * Adds the open of the mutator (setter) method for a JSON column.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addJsonMutatorOpen(string &$script, Column $column): void
    {
        $this->addJsonMutatorComment($script, $column);
        $this->addMutatorOpenOpen($script, $column);
        $this->addMutatorOpenBody($script, $column);
    }

    /**
     * Adds the comment for a mutator.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addJsonMutatorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $orNull = $column->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Set the value of [$clo] column.
     * " . $column->getDescription() . "
     * @param string|array|object{$orNull} \$v new value
     * @return \$this The current object (for fluent API support)
     */";
    }

    /**
     * Adds the comment for a mutator.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addMutatorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $type = $column->getPhpType();
        if ($type && !$column->isNotNull()) {
            $type .= '|null';
        }

        $script .= "
    /**
     * Set the value of [$clo] column.
     * " . $column->getDescription() . "
     * @param " . $type . " \$v New value
     * @return \$this The current object (for fluent API support)
     */";
    }

    /**
     * Adds the mutator function declaration.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addMutatorOpenOpen(string &$script, Column $column): void
    {
        $cfc = $column->getPhpName();
        $visibility = $this->getTable()->isReadOnly() ? 'protected' : $column->getMutatorVisibility();

        $typeHint = '';
        $null = '';

        if ($column->getTypeHint()) {
            $typeHint = $column->getTypeHint();
            // Don't call declareClass for primitive types or array
            if (!in_array($typeHint, ['array', 'int', 'string', 'bool', 'float', 'mixed'], true)) {
                $typeHint = $this->declareClass($typeHint);
            }

            $typeHint .= ' ';

            if (!$column->isNotNull()) {
                $typeHint = '?' . $typeHint;
                $null = ' = null';
            }
        }

        $script .= "
    " . $visibility . " function set$cfc($typeHint\$v$null)
    {";
    }

    /**
     * Adds the mutator open body part.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addMutatorOpenBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $cfc = $column->getPhpName();
        if ($column->isLazyLoad()) {
            $script .= "
        // explicitly set the is-loaded flag to true for this lazy load col;
        // it doesn't matter if the value is actually set or not (logic below) as
        // any attempt to set the value means that no db lookup should be performed
        // when the get$cfc() method is called.
        \$this->" . $clo . "_isLoaded = true;
";
        }
    }

    /**
     * Adds the close of the mutator (setter) method for a column.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addMutatorClose(string &$script, Column $column): void
    {
        $this->addMutatorCloseBody($script, $column);
        $this->addMutatorCloseClose($script, $column);
    }

    /**
     * Adds the body of the close part of a mutator.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addMutatorCloseBody(string &$script, Column $column): void
    {
        $table = $this->getTable();

        if ($column->isForeignKey()) {
            foreach ($column->getForeignKeys() as $fk) {
                $tblFK = $table->getDatabase()->getTable($fk->getForeignTableName());
                $colFK = $tblFK->getColumn($fk->getMappedForeignColumn($column->getName()));

                if (!$colFK) {
                    continue;
                }

                $varName = $this->getFKVarName($fk);

                $script .= "
        if (\$this->$varName !== null && \$this->" . $varName . '->get' . $colFK->getPhpName() . "() !== \$v) {
            \$this->$varName = null;
        }
";
            }
        }

        foreach ($column->getReferrers() as $refFK) {
            $tblFK = $this->getDatabase()->getTable($refFK->getForeignTableName());

            if ($tblFK->getName() !== $table->getName()) {
                foreach ($column->getForeignKeys() as $fk) {
                    $tblFK = $table->getDatabase()->getTable($fk->getForeignTableName());
                    $colFK = $tblFK->getColumn($fk->getMappedForeignColumn($column->getName()));

                    if ($refFK->isLocalPrimaryKey()) {
                        $varName = $this->getPKRefFKVarName($refFK);
                        $script .= "
        // update associated " . $tblFK->getPhpName() . "
        if (\$this->$varName !== null) {
            \$this->{$varName}->set" . $colFK->getPhpName() . "(\$v);
        }
";
                    } else {
                        $collName = $this->getRefFKCollVarName($refFK);
                        $script .= "

        // update associated " . $tblFK->getPhpName() . "
        if (\$this->$collName !== null) {
            foreach (\$this->$collName as \$referrerObject) {
                \$referrerObject->set" . $colFK->getPhpName() . "(\$v);
            }
        }
";
                    }
                }
            }
        }
    }

    /**
     * Adds the close for the mutator close
     *
     * @see addMutatorClose()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addMutatorCloseClose(string &$script, Column $col): void
    {
        $script .= "
        return \$this;
    }
";
    }

    /**
     * Adds a setter for BLOB columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addLobMutator(string &$script, Column $col): void
    {
        $this->addMutatorOpen($script, $col);
        $clo = $col->getLowercasedName();
        $script .= "
        // Because BLOB columns are streams in PDO we have to assume that they are
        // always modified when a new value is passed in.  For example, the contents
        // of the stream itself may have changed externally.
        if (!is_resource(\$v) && \$v !== null) {
            \$this->$clo = fopen('php://memory', 'r+');
            fwrite(\$this->$clo, \$v);
            rewind(\$this->$clo);
        } else { // it's already a stream
            \$this->$clo = \$v;
        }
        \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * Adds a setter method for date/time/timestamp columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addTemporalMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $dateTimeClass = $this->getDateTimeClass($col);

        $this->declareClasses($dateTimeClass, '\Propel\Runtime\Util\PropelDateTime');

        $this->addTemporalMutatorComment($script, $col);
        $this->addMutatorOpenOpen($script, $col);
        $this->addMutatorOpenBody($script, $col);

        $fmt = var_export($this->getTemporalFormatter($col), true);

        $script .= "
        \$dt = PropelDateTime::newInstance(\$v, null, '$dateTimeClass');
        if (\$this->$clo !== null || \$dt !== null) {";

        $def = $col->getDefaultValue();
        if ($def !== null && !$def->isExpression()) {
            $defaultValue = $this->getDefaultValueString($col);
            $script .= "
            if ( (\$dt != \$this->{$clo}) // normalized values don't match
                || (\$dt->format($fmt) === $defaultValue) // or the entered value matches the default
                 ) {";
        } else {
            switch ($col->getType()) {
                case 'DATE':
                    $format = 'Y-m-d';

                    break;
                case 'TIME':
                    $format = 'H:i:s.u';

                    break;
                default:
                    $format = 'Y-m-d H:i:s.u';
            }
            $script .= "
            if (\$this->{$clo} === null || \$dt === null || \$dt->format(\"$format\") !== \$this->{$clo}->format(\"$format\")) {";
        }

        $script .= "
                \$this->$clo = \$dt === null ? null : clone \$dt;
                \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
            }
        } // if either are not null
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\Column $col
     *
     * @return void
     */
    public function addTemporalMutatorComment(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $orNull = $col->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Sets the value of [$clo] column to a normalized version of the date/time value specified.
     * " . $col->getDescription() . "
     * @param string|integer|\DateTimeInterface{$orNull} \$v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return \$this The current object (for fluent API support)
     */";
    }

    /**
     * Adds a setter for Object columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addObjectMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();
        $cloUnserialized = $clo . '_unserialized';
        $this->addMutatorOpen($script, $col);

        $script .= "
        if (null === \$this->$clo || (rewind(\$this->$clo) !== false && stream_get_contents(\$this->$clo) !== serialize(\$v))) {
            \$this->$cloUnserialized = \$v;
            \$this->$clo = fopen('php://memory', 'r+');
            fwrite(\$this->$clo, serialize(\$v));
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
        rewind(\$this->$clo);
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * Adds a setter for Json columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addJsonMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $this->addJsonMutatorOpen($script, $col);

        $script .= "
        if (is_string(\$v)) {
            // JSON as string needs to be decoded/encoded to get a reliable comparison (spaces, ...)
            \$v = json_decode(\$v, false, 512, JSON_THROW_ON_ERROR);
        }
        \$encodedValue = json_encode(\$v, JSON_THROW_ON_ERROR);
        if (\$encodedValue !== \$this->$clo) {
            \$this->$clo = \$encodedValue;
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * Adds a setter for Array columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addArrayMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();
        $cloUnserialized = $clo . '_unserialized';
        $this->addMutatorOpen($script, $col);

        $script .= "
        if (\$this->$cloUnserialized !== \$v) {
            \$this->$cloUnserialized = \$v;
            \$this->$clo = '| ' . implode(' | ', \$v) . ' |';
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * Adds a push method for an array column.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addAddArrayElement(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();
        $cfc = $col->getPhpName();
        $visibility = $col->getMutatorVisibility();
        $singularPhpName = $col->getPhpSingularName();
        $columnType = ($col->getType() === PropelTypes::PHP_ARRAY) ? 'array' : 'set';
        $script .= "
    /**
     * Adds a value to the [$clo] $columnType column value.
     * @param mixed \$value
     * " . $col->getDescription();
        if ($col->isLazyLoad()) {
            $script .= "
     * @param ConnectionInterface \$con An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return \$this The current object (for fluent API support)
     */
    $visibility function add$singularPhpName(\$value";
        if ($col->isLazyLoad()) {
            $script .= ', ?ConnectionInterface $con = null';
        }

        $script .= ")
    {
        \$currentArray = \$this->get$cfc(";
        if ($col->isLazyLoad()) {
            $script .= '$con';
        }

        $script .= ");
        \$currentArray []= \$value;
        \$this->set$cfc(\$currentArray);

        return \$this;
    }
";
    }

    /**
     * Adds a remove method for an array column.
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addRemoveArrayElement(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();
        $cfc = $col->getPhpName();
        $visibility = $col->getMutatorVisibility();
        $singularPhpName = $col->getPhpSingularName();
        $columnType = ($col->getType() === PropelTypes::PHP_ARRAY) ? 'array' : 'set';
        $script .= "
    /**
     * Removes a value from the [$clo] $columnType column value.
     * @param mixed \$value
     * " . $col->getDescription();
        if ($col->isLazyLoad()) {
            $script .= "
     * @param ConnectionInterface \$con An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return \$this The current object (for fluent API support)
     */
    $visibility function remove$singularPhpName(\$value";
        if ($col->isLazyLoad()) {
            $script .= ', ?ConnectionInterface $con = null';
        }
        // we want to reindex the array, so array_ functions are not the best choice
        $script .= ")
    {
        \$targetArray = [];
        foreach (\$this->get$cfc(";
        if ($col->isLazyLoad()) {
            $script .= '$con';
        }
        $script .= ") as \$element) {
            if (\$element !== \$value) {
                \$targetArray []= \$element;
            }
        }
        \$this->set$cfc(\$targetArray);

        return \$this;
    }
";
    }

    /**
     * Adds a setter for Enum columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addEnumMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();
        $this->addEnumMutatorComment($script, $col);
        $this->addMutatorOpenOpen($script, $col);
        $this->addMutatorOpenBody($script, $col);

        $script .= "
        if (\$v !== null) {
            \$valueSet = " . $this->getTableMapClassName() . '::getValueSet(' . $this->getColumnConstant($col) . ");
            if (!in_array(\$v, \$valueSet)) {
                throw new PropelException(sprintf('Value \"%s\" is not accepted in this enumerated column', \$v));
            }
            \$v = array_search(\$v, \$valueSet);
        }

        if (\$this->$clo !== \$v) {
            \$this->$clo = \$v;
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * Adds the comment for an enum mutator.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addEnumMutatorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $orNull = $column->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Set the value of [$clo] column.
     * " . $column->getDescription() . "
     * @param string{$orNull} \$v new value
     * @return \$this The current object (for fluent API support)
     * @throws \\Propel\\Runtime\\Exception\\PropelException
     */";
    }

    /**
     * Adds a setter for SET column mutator.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addSetMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();
        $this->addSetMutatorComment($script, $col);
        $this->addMutatorOpenOpen($script, $col);
        $this->addMutatorOpenBody($script, $col);
        $cloConverted = $clo . '_converted';

        $this->declareClasses(
            'Propel\Common\Util\SetColumnConverter',
            'Propel\Common\Exception\SetColumnConverterException',
        );

        $script .= "
        if (\$this->$cloConverted === null || count(array_diff(\$this->$cloConverted, \$v)) > 0 || count(array_diff(\$v, \$this->$cloConverted)) > 0) {
            \$valueSet = " . $this->getTableMapClassName() . '::getValueSet(' . $this->getColumnConstant($col) . ");
            try {
                \$v = SetColumnConverter::convertToInt(\$v, \$valueSet);
            } catch (SetColumnConverterException \$e) {
                throw new PropelException(sprintf('Value \"%s\" is not accepted in this set column', \$e->getValue()), \$e->getCode(), \$e);
            }
            if (\$this->$clo !== \$v) {
                \$this->$cloConverted = null;
                \$this->$clo = \$v;
                \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
            }
        }
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * Adds the comment for a SET column mutator.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addSetMutatorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $orNull = $column->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Set the value of [$clo] column.
     * " . $column->getDescription() . "
     * @param array{$orNull} \$v new value
     * @return \$this The current object (for fluent API support)
     * @throws \\Propel\\Runtime\\Exception\\PropelException
     */";
    }

    /**
     * Adds setter method for boolean columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addBooleanMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $this->addBooleanMutatorComment($script, $col);
        $this->addMutatorOpenOpen($script, $col);
        $this->addMutatorOpenBody($script, $col);

        $script .= "
        if (\$v !== null) {
            if (is_string(\$v)) {
                \$v = in_array(strtolower(\$v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                \$v = (boolean) \$v;
            }
        }

        if (\$this->$clo !== \$v) {
            \$this->$clo = \$v;
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
";
        $this->addMutatorClose($script, $col);
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\Column $col
     *
     * @return void
     */
    public function addBooleanMutatorComment(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $orNull = $col->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Sets the value of the [$clo] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * " . $col->getDescription() . "
     * @param bool|integer|string{$orNull} \$v The new value
     * @return \$this The current object (for fluent API support)
     */";
    }

    /**
     * Adds setter method for "normal" columns.
     *
     * @see parent::addColumnMutators()
     *
     * @param string $script The script will be modified in this method.
     * @param \Propel\Generator\Model\Column $col The current column.
     *
     * @return void
     */
    protected function addDefaultMutator(string &$script, Column $col): void
    {
        $clo = $col->getLowercasedName();

        $this->addMutatorOpen($script, $col);

        // Perform type-casting to ensure that we can use type-sensitive
        // checking in mutators.
        if ($col->isPhpPrimitiveType()) {
            $script .= "
        if (\$v !== null) {
            \$v = (" . $col->getPhpType() . ") \$v;
        }
";
        }

        $script .= "
        if (\$this->$clo !== \$v) {
            \$this->$clo = \$v;
            \$this->modifiedColumns[" . $this->getColumnConstant($col) . "] = true;
        }
";
        $this->addMutatorClose($script, $col);
    }
}
