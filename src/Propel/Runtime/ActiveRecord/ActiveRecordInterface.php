<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveRecord;

/**
 * This ActiveRecord interface helps to find Propel Object.
 *
 * The interface itself only declares isPrimaryKeyNull() as an abstract
 * method; the @method tags below describe the runtime contract every
 * generated AR class additionally provides. They are deliberately PHPDoc
 * (not abstract methods) so that adding a new tag here does not break
 * binary compatibility for downstream code that already implements the
 * interface (per umbrella spec section 3.1, no abstract additions in 3.x).
 *
 * @author jaugustin
 *
 * @method array toArray(string $keyType = \Propel\Runtime\Map\TableMap::TYPE_FIELDNAME, bool $includeLazyLoadColumns = true, array $alreadyDumpedObjects = [], bool $includeForeignObjects = false)
 * @method void fromArray(array $arr, string $keyType = \Propel\Runtime\Map\TableMap::TYPE_PHPNAME)
 * @method int save(?\Propel\Runtime\Connection\ConnectionInterface $con = null)
 * @method void delete(?\Propel\Runtime\Connection\ConnectionInterface $con = null)
 * @method void clear()
 * @method void setNew(bool $b)
 * @method void setDeleted(bool $b)
 * @method bool isNew()
 * @method bool isDeleted()
 * @method bool isModified()
 * @method void setModified(bool $b)
 * @method mixed getPrimaryKey()
 * @method void setPrimaryKey(mixed $key)
 */
interface ActiveRecordInterface
{
    /**
     * Returns true if the primary key for this object is null.
     *
     * @return bool
     */
    public function isPrimaryKeyNull(): bool;
}
