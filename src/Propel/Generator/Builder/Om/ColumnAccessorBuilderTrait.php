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
use Propel\Generator\Platform\MysqlPlatform;

/**
 * Trait containing column accessor generation methods for ObjectBuilder.
 *
 * This trait provides methods for generating getter methods for various column types
 * including temporal, object, JSON, array, boolean, enum, set, and default accessors.
 */
trait ColumnAccessorBuilderTrait
{
    /**
     * Adds a date/time/timestamp getter method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addTemporalAccessor(string &$script, Column $column): void
    {
        $this->addTemporalAccessorComment($script, $column);
        $this->addTemporalAccessorOpen($script, $column);
        $this->addTemporalAccessorBody($script, $column);
        $this->addTemporalAccessorClose($script);
    }

    /**
     * Adds the comment for a temporal accessor.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addTemporalAccessorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $dateTimeClass = $this->getDateTimeClass($column);

        $handleMysqlDate = false;
        $mysqlInvalidDateString = '';
        if ($this->getPlatform() instanceof MysqlPlatform) {
            if (in_array($column->getType(), [PropelTypes::TIMESTAMP, PropelTypes::DATETIME], true)) {
                $handleMysqlDate = true;
                $mysqlInvalidDateString = '0000-00-00 00:00:00';
            } elseif ($column->getType() === PropelTypes::DATE) {
                $handleMysqlDate = true;
                $mysqlInvalidDateString = '0000-00-00';
            }
            // 00:00:00 is a valid time, so no need to check for that.
        }

        $orNull = $column->isNotNull() ? '' : '|null';
        $descriptionReturnValueNull = $column->isNotNull() ? '' : ', NULL if column is NULL';
        $descriptionReturnMysqlInvalidDate = $handleMysqlDate ? ", and 0 if column value is $mysqlInvalidDateString" : '';

        $script .= "
    /**
     * Get the [optionally formatted] temporal [$clo] column value.
     * {$column->getDescription()}
     *
     * @param string|null \$format The date/time format string (date()-style).
     *   If format is NULL, then the raw $dateTimeClass object will be returned.
     *
     * @return string|{$dateTimeClass}{$orNull} Formatted date/time value as string or $dateTimeClass object (if format is NULL){$descriptionReturnValueNull}{$descriptionReturnMysqlInvalidDate}.
     *
     * @throws \Propel\Runtime\Exception\PropelException - if unable to parse/validate the date/time value.
     *
     * @psalm-return (\$format is null ? {$dateTimeClass}{$orNull} : string{$orNull})
     */";
    }

    /**
     * Gets the default format for a temporal column from the configuration
     *
     * @param \Propel\Generator\Model\Column $column
     *
     * @return string|null
     */
    protected function getTemporalTypeDefaultFormat(Column $column): ?string
    {
        $configKey = $this->getTemporalTypeDefaultFormatConfigKey($column);

        return $configKey ? $this->getBuildProperty($configKey) : null;
    }

    /**
     * Knows which key in the configuration holds the default format for a
     * temporal type column.
     *
     * @param \Propel\Generator\Model\Column $column
     *
     * @return string|null
     */
    protected function getTemporalTypeDefaultFormatConfigKey(Column $column): ?string
    {
        return match ($column->getType()) {
            PropelTypes::DATE => 'generator.dateTime.defaultDateFormat',
            PropelTypes::TIME => 'generator.dateTime.defaultTimeFormat',
            PropelTypes::TIMESTAMP, PropelTypes::DATETIME => 'generator.dateTime.defaultTimeStampFormat',
            default => null,
        };
    }

    /**
     * Adds the function declaration for a temporal accessor.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addTemporalAccessorOpen(string &$script, Column $column): void
    {
        $cfc = $column->getPhpName();

        $defaultfmt = $this->getTemporalTypeDefaultFormat($column);
        $visibility = $column->getAccessorVisibility();

        $format = var_export($defaultfmt, true);
        if ($format === 'NULL') {
            $format = 'null';
        }

        $script .= "
    " . $visibility . " function get$cfc(?string \$format = " . $format;
        if ($column->isLazyLoad()) {
            $script .= ', ?ConnectionInterface $con = null';
        }
        $script .= ")
    {";
    }

    /**
     * Gets accessor lazy loaded snippets.
     *
     * @param \Propel\Generator\Model\Column $column
     *
     * @return string
     */
    protected function getAccessorLazyLoadSnippet(Column $column): string
    {
        if ($column->isLazyLoad()) {
            $clo = $column->getLowercasedName();
            $defaultValueString = 'null';
            $def = $column->getDefaultValue();
            if ($def !== null && !$def->isExpression()) {
                $defaultValueString = $this->getDefaultValueString($column);
            }

            return "
        if (!\$this->{$clo}_isLoaded && \$this->{$clo} === {$defaultValueString} && !\$this->isNew()) {
            \$this->load{$column->getPhpName()}(\$con);
        }
";
        }

        return '';
    }

    /**
     * Adds the body of the temporal accessor.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addTemporalAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $dateTimeClass = $this->getDateTimeClass($column);

        $this->declareClasses($dateTimeClass);

        if ($column->isLazyLoad()) {
            $script .= $this->getAccessorLazyLoadSnippet($column);
        }

        $script .= "
        if (\$format === null) {
            return \$this->$clo;
        } else {
            return \$this->$clo instanceof \DateTimeInterface ? \$this->{$clo}->format(\$format) : null;
        }";
    }

    /**
     * Adds the body of the temporal accessor.
     *
     * @param string $script The script will be modified in this method.
     *
     * @return void
     */
    protected function addTemporalAccessorClose(string &$script): void
    {
        $script .= "
    }
";
    }

    /**
     * Adds an object getter method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addObjectAccessor(string &$script, Column $column): void
    {
        $this->addDefaultAccessorComment($script, $column);
        $this->addDefaultAccessorOpen($script, $column);
        $this->addObjectAccessorBody($script, $column);
        $this->addDefaultAccessorClose($script);
    }

    /**
     * Adds the function body for an object accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addObjectAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $cloUnserialized = $clo . '_unserialized';
        if ($column->isLazyLoad()) {
            $script .= $this->getAccessorLazyLoadSnippet($column);
        }

        $script .= "
        if (null === \$this->$cloUnserialized && is_resource(\$this->$clo)) {
            rewind(\$this->$clo);
            if (\$serialisedString = stream_get_contents(\$this->$clo)) {
                \$this->$cloUnserialized = unserialize(\$serialisedString, ['allowed_classes' => true]);
            }
        }

        return \$this->$cloUnserialized;";
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addJsonAccessor(string &$script, Column $column): void
    {
        $this->addJsonAccessorComment($script, $column);
        $this->addJsonAccessorOpen($script, $column);
        $this->addJsonAccessorBody($script, $column);
        $this->addDefaultAccessorClose($script);
    }

    /**
     * Add the comment for a json accessor method (a getter).
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addJsonAccessorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $orNull = $column->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Get the [$clo] column value.
     * " . $column->getDescription() . "
     * @param bool \$asArray Returns the JSON data as array instead of object
     ";
        if ($column->isLazyLoad()) {
            $script .= "
     * @param ConnectionInterface \$con An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return object|array{$orNull}
     */";
    }

    /**
     * Adds the function declaration for a JSON accessor.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addJsonAccessorOpen(string &$script, Column $column): void
    {
        $cfc = $column->getPhpName();
        $visibility = $column->getAccessorVisibility();

        $script .= "
    " . $visibility . " function get$cfc(bool \$asArray = true";
        if ($column->isLazyLoad()) {
            $script .= ', ?ConnectionInterface $con = null';
        }

        $script .= ")
    {";
    }

    /**
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addJsonAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $script .= "
        return \$this->$clo !== null ? json_decode(\$this->$clo, \$asArray, 512, JSON_THROW_ON_ERROR) : null;";
    }

    /**
     * Adds an array getter method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addArrayAccessor(string &$script, Column $column): void
    {
        $this->addDefaultAccessorComment($script, $column);
        $this->addDefaultAccessorOpen($script, $column);
        $this->addArrayAccessorBody($script, $column);
        $this->addDefaultAccessorClose($script);
    }

    /**
     * Adds the function body for an array accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addArrayAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $cloUnserialized = $clo . '_unserialized';
        if ($column->isLazyLoad()) {
            $script .= $this->getAccessorLazyLoadSnippet($column);
        }

        $script .= "
        if (null === \$this->$cloUnserialized) {
            \$this->$cloUnserialized = [];
        }
        if (!\$this->$cloUnserialized && null !== \$this->$clo) {
            \$$cloUnserialized = substr(\$this->$clo, 2, -2);
            \$this->$cloUnserialized = '' !== \$$cloUnserialized ? explode(' | ', \$$cloUnserialized) : [];
        }

        return \$this->$cloUnserialized;";
    }

    /**
     * Adds a boolean isser method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addBooleanAccessor(string &$script, Column $column): void
    {
        $name = self::getBooleanAccessorName($column);
        if (in_array($name, ClassTools::getPropelReservedMethods(), true)) {
            //TODO: Issue a warning telling the user to use default accessors
            return; // Skip boolean accessors for reserved names
        }
        $this->addDefaultAccessorComment($script, $column);
        $this->addBooleanAccessorOpen($script, $column);
        $this->addBooleanAccessorBody($script, $column);
        $this->addDefaultAccessorClose($script);
    }

    /**
     * Returns the name to be used as boolean accessor name
     *
     * @param \Propel\Generator\Model\Column $column
     *
     * @return string
     */
    protected static function getBooleanAccessorName(Column $column): string
    {
        $name = $column->getCamelCaseName();
        if (!preg_match('/^(?:is|has)(?=[A-Z])/', $name)) {
            $name = 'is' . ucfirst($name);
        }

        return $name;
    }

    /**
     * Adds the function declaration for a boolean accessor.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addBooleanAccessorOpen(string &$script, Column $column): void
    {
        $name = self::getBooleanAccessorName($column);
        $visibility = $column->getAccessorVisibility();

        $script .= "
    " . $visibility . " function $name(";
        if ($column->isLazyLoad()) {
            $script .= '?ConnectionInterface $con = null';
        }

        $script .= ")
    {";
    }

    /**
     * Adds the function body for a boolean accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addBooleanAccessorBody(string &$script, Column $column): void
    {
        $cfc = $column->getPhpName();

        $script .= "
        return \$this->get$cfc(";

        if ($column->isLazyLoad()) {
            $script .= '$con';
        }

        $script .= ');';
    }

    /**
     * Adds an enum getter method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addEnumAccessor(string &$script, Column $column): void
    {
        $this->addEnumAccessorComment($script, $column);
        $this->addDefaultAccessorOpen($script, $column);
        $this->addEnumAccessorBody($script, $column);
        $this->addDefaultAccessorClose($script);
    }

    /**
     * Add the comment for an enum accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addEnumAccessorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $enumClassName = $this->getEnumClassName($column);

        $script .= "
    /**
     * Get the [$clo] column value.
     * " . $column->getDescription();
        if ($column->isLazyLoad()) {
            $script .= "
     * @param ConnectionInterface \$con An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return $enumClassName|null
     * @throws \\Propel\\Runtime\\Exception\\PropelException
     */";
    }

    /**
     * Adds the function body for an enum accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addEnumAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $enumClassName = $this->getEnumClassName($column);

        if ($column->isLazyLoad()) {
            $script .= $this->getAccessorLazyLoadSnippet($column);
        }

        $script .= "
        if (null === \$this->$clo) {
            return null;
        }
        \$valueSet = " . $this->getTableMapClassName() . '::getValueSet(' . $this->getColumnConstant($column) . ");
        if (!isset(\$valueSet[\$this->$clo])) {
            throw new PropelException('Unknown stored enum key: ' . \$this->$clo);
        }

        return $enumClassName::from(\$valueSet[\$this->$clo]);";
    }

    /**
     * Adds a SET column getter method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addSetAccessor(string &$script, Column $column): void
    {
        $this->addSetAccessorComment($script, $column);
        $this->addDefaultAccessorOpen($script, $column);
        $this->addSetAccessorBody($script, $column);
        $this->addDefaultAccessorClose($script);
    }

    /**
     * Add the comment for a SET column accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addSetAccessorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $script .= "
    /**
     * Get the [$clo] column value.
     * " . $column->getDescription();
        if ($column->isLazyLoad()) {
            $script .= "
     * @param ConnectionInterface \$con An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return array|null
     * @throws \\Propel\\Runtime\\Exception\\PropelException
     */";
    }

    /**
     * Adds the function body for a SET column accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addSetAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        $cloConverted = $clo . '_converted';
        if ($column->isLazyLoad()) {
            $script .= $this->getAccessorLazyLoadSnippet($column);
        }
        $this->declareClasses(
            'Propel\Common\Util\SetColumnConverter',
            'Propel\Common\Exception\SetColumnConverterException',
        );

        $script .= "
        if (null === \$this->$cloConverted) {
            \$this->$cloConverted = [];
        }
        if (!\$this->$cloConverted && null !== \$this->$clo) {
            \$valueSet = " . $this->getTableMapClassName() . '::getValueSet(' . $this->getColumnConstant($column) . ");
            try {
                \$this->$cloConverted = SetColumnConverter::convertIntToArray(\$this->$clo, \$valueSet);
            } catch (SetColumnConverterException \$e) {
                throw new PropelException('Unknown stored set key: ' . \$e->getValue(), \$e->getCode(), \$e);
            }
        }

        return \$this->$cloConverted;";
    }

    /**
     * Adds a normal (non-temporal) getter method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addDefaultAccessor(string &$script, Column $column): void
    {
        $this->addDefaultAccessorComment($script, $column);
        $this->addDefaultAccessorOpen($script, $column);
        $this->addDefaultAccessorBody($script, $column);
        $this->addDefaultAccessorClose($script);
    }

    /**
     * Add the comment for a default accessor method (a getter).
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addDefaultAccessorComment(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();

        $orNull = $column->isNotNull() ? '' : '|null';

        $script .= "
    /**
     * Get the [$clo] column value.
     * " . $column->getDescription();
        if ($column->isLazyLoad()) {
            $script .= "
     * @param ConnectionInterface \$con An optional ConnectionInterface connection to use for fetching this lazy-loaded column.";
        }
        $script .= "
     * @return " . ($column->getTypeHint() ?: ($column->getPhpType() ?: 'mixed')) . $orNull . "
     */";
    }

    /**
     * Adds the function declaration for a default accessor.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function addDefaultAccessorOpen(string &$script, Column $column): void
    {
        $cfc = $column->getPhpName();
        $visibility = $column->getAccessorVisibility();

        $script .= "
    " . $visibility . " function get$cfc(";
        if ($column->isLazyLoad()) {
            $script .= '?ConnectionInterface $con = null';
        }

        $script .= ")
    {";
    }

    /**
     * Adds the function body for a default accessor method.
     *
     * @param string $script
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    protected function addDefaultAccessorBody(string &$script, Column $column): void
    {
        $clo = $column->getLowercasedName();
        if ($column->isLazyLoad()) {
            $script .= $this->getAccessorLazyLoadSnippet($column);
        }

        $script .= "
        return \$this->$clo;";
    }

    /**
     * Adds the function close for a default accessor method.
     *
     * @param string $script The script will be modified in this method.
     *
     * @return void
     */
    protected function addDefaultAccessorClose(string &$script): void
    {
        $script .= "
    }
";
    }
}
