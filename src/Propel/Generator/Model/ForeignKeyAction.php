<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Model;

/**
 * Enum for foreign key referential actions (ON DELETE / ON UPDATE behaviors).
 *
 * @author Hans Lellelid <hans@xmpl.org> (Propel)
 * @author Hugo Hamon <webmaster@apprendre-php.com> (Propel)
 */
enum ForeignKeyAction: string
{
    /**
     * No 'ON [ DELETE | UPDATE ]' behavior specified.
     */
    case NONE = '';

    /**
     * NO ACTION - Produce an error indicating that the deletion or update
     * would create a foreign key constraint violation.
     */
    case NO_ACTION = 'NO ACTION';

    /**
     * CASCADE - Delete or update the row from the parent table and
     * automatically delete or update the matching rows in the child table.
     */
    case CASCADE = 'CASCADE';

    /**
     * RESTRICT - Rejects the delete or update operation for the parent table.
     */
    case RESTRICT = 'RESTRICT';

    /**
     * SET DEFAULT - Set the foreign key column(s) to their default values.
     */
    case SET_DEFAULT = 'SET DEFAULT';

    /**
     * SET NULL - Set the foreign key column(s) to NULL.
     */
    case SET_NULL = 'SET NULL';
}
