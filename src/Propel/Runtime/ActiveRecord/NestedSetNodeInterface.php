<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveRecord;

/**
 * Static-analysis shape for ActiveRecord objects produced with the NestedSet
 * behavior. Generated NestedSet AR classes are not required to formally
 * implement this interface (legacy code generation pre-dates it); the
 * interface exists so that NestedSetRecursiveIterator can be precisely typed
 * without scattering per-callsite ignores.
 *
 * Methods retrieveNextSibling and retrieveFirstChild are supported variants
 * documented via method_exists guards in the iterator, not declared here.
 */
interface NestedSetNodeInterface
{
    /**
     * @return list<self>
     */
    public function getAncestors(): array;

    /**
     * @return bool
     */
    public function hasNextSibling(): bool;

    /**
     * @return self|null
     */
    public function getNextSibling();

    /**
     * @return bool
     */
    public function hasChildren(): bool;

    /**
     * @return self|null
     */
    public function getFirstChild();
}
