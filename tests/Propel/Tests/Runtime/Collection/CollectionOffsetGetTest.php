<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Collection;

use ErrorException;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Collection\Collection;

/**
 * Regression: Collection::offsetGet was declared `public function &offsetGet`
 * (return-by-reference) but returned a literal `null` for missing keys.
 * PHP 8 emits a notice on `return null;` from a by-reference function. With
 * Phase A's failOnNotice="true" this would surface as a test failure.
 */
class CollectionOffsetGetTest extends TestCase
{
    public function testOffsetGetMissingKeyReturnsNullWithoutByRefNotice(): void
    {
        $previousLevel = error_reporting(E_ALL);
        set_error_handler(static function (int $err, string $msg): never {
            throw new ErrorException($msg, 0, $err);
        });
        try {
            $coll = new Collection();
            $value = $coll['missing'];
            $this->assertNull($value);
        } finally {
            restore_error_handler();
            error_reporting($previousLevel);
        }
    }

    public function testOffsetGetExistingKeyReturnsValue(): void
    {
        $coll = new Collection();
        $coll[0] = 'first';
        $this->assertSame('first', $coll[0]);
    }
}
