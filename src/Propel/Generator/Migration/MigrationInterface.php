<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Migration;

use Propel\Generator\Manager\MigrationManager;

/**
 * Shape of a generated migration class.
 *
 * Migration classes produced by "propel migration:diff" are not required to
 * formally implement this interface (legacy templates pre-date it), but every
 * generated migration provides the methods listed here. The interface exists
 * primarily for static analysis: it lets MigrationManager::getMigrationObject()
 * return type and the migration command call sites be precisely typed without
 * a per-callsite ignore annotation.
 *
 * The migration template populates these methods. Custom migration classes
 * may freely add additional pre/post hooks with the same naming pattern;
 * MigrationManager only relies on the contract defined here.
 */
interface MigrationInterface
{
    /**
     * Returns the SQL to execute for the up direction, keyed by datasource name.
     *
     * @return array<string, string>
     */
    public function getUpSQL(): array;

    /**
     * Returns the SQL to execute for the down direction, keyed by datasource name.
     *
     * @return array<string, string>
     */
    public function getDownSQL(): array;

    /**
     * @param \Propel\Generator\Manager\MigrationManager $manager
     *
     * @return false|null|void Return false to abort the migration.
     */
    public function preUp(MigrationManager $manager);

    /**
     * @param \Propel\Generator\Manager\MigrationManager $manager
     *
     * @return null|void
     */
    public function postUp(MigrationManager $manager);

    /**
     * @param \Propel\Generator\Manager\MigrationManager $manager
     *
     * @return false|null|void Return false to abort the migration.
     */
    public function preDown(MigrationManager $manager);

    /**
     * @param \Propel\Generator\Manager\MigrationManager $manager
     *
     * @return null|void
     */
    public function postDown(MigrationManager $manager);
}
