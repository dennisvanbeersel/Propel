<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Model;

/**
 * Enum for column default value types.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Propel)
 * @author Hugo Hamon <webmaster@apprendre-php.com> (Propel)
 */
enum DefaultValueType: string
{
    /**
     * A literal value (string, number, etc.).
     */
    case VALUE = 'value';

    /**
     * A database expression (e.g., CURRENT_TIMESTAMP, NOW()).
     */
    case EXPRESSION = 'expr';
}
