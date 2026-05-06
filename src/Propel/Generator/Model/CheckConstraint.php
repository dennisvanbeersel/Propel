<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Model;

use Propel\Generator\Exception\EngineException;

/**
 * Models a SQL CHECK constraint.
 *
 * Phase C (umbrella §6.4): adds first-class CHECK constraint support to the
 * schema model. Mirrors the {@see Index} / {@see Unique} shape — a CHECK
 * constraint owns a name (auto-generated when nameless), an SQL expression
 * (emitted verbatim to the database), and an enforcement flag.
 *
 * Column-scoped <check> elements in the XML schema are sugar; the constraint
 * is always stored on the parent {@see Table} as a flat list. See
 * {@see Table::getCheckConstraints()} and {@see Table::addCheckConstraint()}.
 *
 * Tier 2 SPI (per umbrella §3.4): the public method shape is committed for
 * the 3.x line. Snapshot lives at tests/snapshots/CheckConstraint.signatures.json.
 *
 * @author Phase C, 2026-05-06
 */
class CheckConstraint extends MappingModel
{
    private ?string $name = null;

    private string $expression = '';

    private bool $enforced = true;

    private bool $autoNaming = false;

    /**
     * The Table instance.
     */
    private ?Table $table = null;

    /**
     * @param string|null $name optional constraint name; auto-generated when omitted
     * @param string $expression the SQL expression emitted verbatim
     * @param bool $enforced whether the constraint is enforced (default true)
     */
    public function __construct(?string $name = null, string $expression = '', bool $enforced = true)
    {
        $this->setName($name);
        $this->expression = $expression;
        $this->enforced = $enforced;
    }

    /**
     * @inheritDoc
     *
     * @throws \Propel\Generator\Exception\EngineException when expression is empty
     */
    #[\Override]
    protected function setupObject(): void
    {
        $this->setName($this->getAttribute('name'));

        $expression = (string)$this->getAttribute('expression');
        if ($expression === '') {
            throw new EngineException('CHECK constraint requires a non-empty expression attribute.');
        }
        $this->expression = $expression;

        $enforced = $this->getAttribute('enforced');
        $this->enforced = $enforced === null ? true : $this->booleanValue($enforced);
    }

    /**
     * Sets the constraint name. Passing null re-enables auto-naming.
     *
     * @param string|null $name
     *
     * @return void
     */
    public function setName(?string $name): void
    {
        $this->autoNaming = $name === null || $name === '';
        $this->name = $this->autoNaming ? null : $name;
    }

    /**
     * Returns the constraint name, computing an auto-name when none was set.
     *
     * @return string
     */
    public function getName(): string
    {
        if ($this->autoNaming || $this->name === null || $this->name === '') {
            $this->doNaming();
        }

        return (string)$this->name;
    }

    /**
     * @return void
     */
    private function doNaming(): void
    {
        $tablePart = $this->table !== null ? $this->table->getCommonName() : 'anon';
        $hash = substr(md5($this->expression), 0, 8);
        $this->name = sprintf('ck_%s_%s', $tablePart, $hash);
        $this->autoNaming = true;
    }

    /**
     * Sets the SQL expression. The expression is emitted verbatim to the
     * database; no parsing or escaping is performed by the model.
     *
     * @param string $expression
     *
     * @return void
     */
    public function setExpression(string $expression): void
    {
        $this->expression = $expression;
    }

    /**
     * Returns the raw SQL expression.
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Sets the enforcement flag.
     *
     * Maps to NOT ENFORCED on MySQL/MariaDB and NOT VALID on PostgreSQL when
     * false; SQLite is frozen and rejects CHECK declarations entirely (see
     * Phase C Group C.3 for platform DDL emission).
     *
     * @param bool $enforced
     *
     * @return void
     */
    public function setEnforced(bool $enforced): void
    {
        $this->enforced = $enforced;
    }

    /**
     * @return bool
     */
    public function isEnforced(): bool
    {
        return $this->enforced;
    }

    /**
     * @return \Propel\Generator\Model\Table|null
     */
    public function getTable(): ?Table
    {
        return $this->table;
    }

    /**
     * @param \Propel\Generator\Model\Table $table
     *
     * @return void
     */
    public function setTable(Table $table): void
    {
        $this->table = $table;
    }

    /**
     * Structural equality used by Diff comparators (Phase C Group C.5).
     *
     * Two CHECK constraints are equivalent when their expressions and
     * enforcement flags match. The auto-generated name is derived from the
     * expression and is therefore not compared independently.
     *
     * @param self $other
     *
     * @return bool
     */
    public function isEquivalent(self $other): bool
    {
        return $this->expression === $other->expression
            && $this->enforced === $other->enforced
            && $this->getName() === $other->getName();
    }
}
