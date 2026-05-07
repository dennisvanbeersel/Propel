<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Om;

use Propel\Generator\Exception\EngineException;
use Propel\Generator\Model\Column;

/**
 * Generates a PHP 8.1 backed-string enum class for an ENUM column.
 *
 * For column `status` of table `Book` with values ['draft', 'published', 'archived']
 * this builder produces:
 *
 *     enum BookStatus: string
 *     {
 *         case DRAFT = 'draft';
 *         case PUBLISHED = 'published';
 *         case ARCHIVED = 'archived';
 *     }
 *
 * The enum class name is `<TablePhpName><ColumnPhpName>` and lives in the same
 * user namespace as the stub model (peer of ExtensionObjectBuilder).
 */
class EnumBuilder extends AbstractOMBuilder
{
    /**
     * The ENUM column this builder targets. Set via {@see setColumn()} before {@see build()}.
     */
    private ?Column $column = null;

    /**
     * @param \Propel\Generator\Model\Column $column
     *
     * @return void
     */
    public function setColumn(Column $column): void
    {
        $this->column = $column;
    }

    /**
     * @throws \Propel\Generator\Exception\EngineException
     *
     * @return \Propel\Generator\Model\Column
     */
    public function getColumn(): Column
    {
        if ($this->column === null) {
            throw new EngineException('EnumBuilder: column has not been set.');
        }

        return $this->column;
    }

    /**
     * Returns the unqualified class name of the generated enum,
     * e.g. `BookStatus` for column `status` on table `Book`.
     *
     * @return string
     */
    #[\Override]
    public function getUnprefixedClassName(): string
    {
        return self::buildEnumClassName($this->getTable()->getPhpName(), $this->getColumn()->getPhpName());
    }

    /**
     * Builds the unqualified PHP enum class name from a table and column phpName.
     *
     * @param string $tablePhpName
     * @param string $columnPhpName
     *
     * @return string
     */
    public static function buildEnumClassName(string $tablePhpName, string $columnPhpName): string
    {
        return $tablePhpName . $columnPhpName;
    }

    /**
     * The enum lives alongside the user stub (no `\Base` suffix).
     *
     * @return string|null
     */
    #[\Override]
    public function getNamespace(): ?string
    {
        return $this->getTable()->getNamespace();
    }

    /**
     * Builds the PHP source for the enum class.
     *
     * Overrides {@see AbstractOMBuilder::build()} so the generated file uses
     * the `enum` keyword instead of `class` and skips namespace-import bookkeeping
     * (the enum body never references external types).
     *
     * @throws \Propel\Generator\Exception\EngineException
     *
     * @return string
     */
    #[\Override]
    public function build(): string
    {
        $column = $this->getColumn();
        $valueSet = $column->getValueSet();

        if ($valueSet === []) {
            throw new EngineException(sprintf(
                'EnumBuilder: cannot generate an enum for column "%s" — value set is empty.',
                $column->getFullyQualifiedName(),
            ));
        }

        $cases = self::buildEnumCases($valueSet);

        $script = "<?php\n\ndeclare(strict_types=1);\n\n";

        $namespaceStatement = $this->getNamespaceStatement();
        if ($namespaceStatement !== null) {
            $script .= $namespaceStatement;
        }

        $className = $this->getUnqualifiedClassName();
        $columnName = $column->getName();
        $tableName = $this->getTable()->getName();
        $script .= "/**\n";
        $script .= ' * Auto-generated backed enum for column `' . $columnName . "`\n";
        $script .= ' * of table `' . $tableName . "`.\n";
        $script .= " */\n";
        $script .= 'enum ' . $className . ": string\n{\n";
        foreach ($cases as $caseName => $caseValue) {
            $script .= '    case ' . $caseName . " = '" . self::escapeSingleQuoted($caseValue) . "';\n";
        }
        $script .= "}\n";

        // Normalize line endings + drop trailing whitespace, mirroring AbstractOMBuilder::clean().
        $script = str_replace("\r\n", "\n", $script);
        $script = (string)preg_replace('/[ \t]*$/m', '', $script);

        return $script;
    }

    /**
     * Builds the case-name => value map from a raw value set.
     *
     * Case names are derived as a screaming-snake-case form of the value with
     * non-identifier characters stripped. Numeric or otherwise unsafe values
     * fall back to a deterministic `CASE_<index>` name.
     *
     * @param array<int, string> $valueSet
     *
     * @return array<string, string>
     */
    public static function buildEnumCases(array $valueSet): array
    {
        $cases = [];
        $usedNames = [];

        foreach (array_values($valueSet) as $index => $value) {
            $caseName = self::sanitizeCaseName((string)$value, $index);
            if (isset($usedNames[$caseName])) {
                $caseName = 'CASE_' . $index;
            }
            $usedNames[$caseName] = true;
            $cases[$caseName] = (string)$value;
        }

        return $cases;
    }

    /**
     * Sanitizes an enum value into a valid PHP identifier (uppercase snake-case).
     *
     * @param string $value
     * @param int $index Zero-based position in the value set, used for the safe fallback.
     *
     * @return string
     */
    private static function sanitizeCaseName(string $value, int $index): string
    {
        // Replace non-alphanumeric/underscore runs with a single underscore.
        $name = (string)preg_replace('/[^A-Za-z0-9_]+/', '_', $value);
        // Convert lower→UPPER and add underscores between camel boundaries (fooBar → FOO_BAR).
        $name = (string)preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);
        $name = strtoupper($name);
        $name = trim($name, '_');

        // PHP identifiers cannot start with a digit and cannot be empty —
        // fall back to a deterministic index-based name.
        if ($name === '' || preg_match('/^[0-9]/', $name) === 1) {
            return 'CASE_' . $index;
        }

        return $name;
    }

    /**
     * @param string $raw
     *
     * @return string
     */
    private static function escapeSingleQuoted(string $raw): string
    {
        return strtr($raw, ['\\' => '\\\\', "'" => "\\'"]);
    }

    /**
     * Not used — {@see build()} is fully overridden.
     *
     * @param string $script
     *
     * @return void
     */
    #[\Override]
    protected function addClassOpen(string &$script): void
    {
    }

    /**
     * Not used — {@see build()} is fully overridden.
     *
     * @param string $script
     *
     * @return void
     */
    #[\Override]
    protected function addClassBody(string &$script): void
    {
    }

    /**
     * Not used — {@see build()} is fully overridden.
     *
     * @param string $script
     *
     * @return void
     */
    #[\Override]
    protected function addClassClose(string &$script): void
    {
    }
}
