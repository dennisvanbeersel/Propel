<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\ActiveQuery;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Bridge\PhpUnit\ExpectUserDeprecationMessageTrait;

/**
 * Per umbrella §6.1: the eight Java-Hashtable methods are deprecated as of 3.0
 * and slated for removal in 4.0. They remain functional during the runway —
 * only a deprecation notice fires when called.
 */
#[Group('legacy')]
class JavaHashtableDeprecationTest extends TestCase
{
    use ExpectUserDeprecationMessageTrait;

    public function testPutEmitsDeprecationAndStillWorks(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::put() is a Java-Hashtable rump and is deprecated. Use Criteria::add() / addAnd() / addOr() instead. Removal targeted for 4.0.',
        );

        $crit = new Criteria();
        $crit->put('book.title', 'foo');
        self::assertTrue($crit->getMap() !== []);
    }

    public function testGetEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::get() is a Java-Hashtable rump and is deprecated. Use Criteria::getValue() (its existing Tier 1 alias) directly. Removal targeted for 4.0.',
        );

        $crit = new Criteria();
        $crit->add('book.title', 'foo');
        self::assertSame('foo', $crit->get('book.title'));
    }

    public function testKeysEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::keys() is a Java-Hashtable rump and is deprecated. Use array_keys($criteria->getMap()) or iterate via WhereTree::getCriterions(). Removal targeted for 4.0.',
        );

        $crit = new Criteria();
        $crit->add('book.title', 'foo');
        self::assertSame(['book.title'], $crit->keys());
    }

    public function testContainsKeyEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::containsKey() is a Java-Hashtable rump and is deprecated. Use Criteria::hasWhereClause() or check WhereTree::has() directly. Removal targeted for 4.0.',
        );

        $crit = new Criteria();
        $crit->add('book.title', 'foo');
        self::assertTrue($crit->containsKey('book.title'));
    }

    public function testKeyContainsValueEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::keyContainsValue() is a Java-Hashtable rump and is deprecated. Use $criteria->hasWhereClause($col) && $criteria->getCriterion($col)->getValue() !== null instead. Removal targeted for 4.0.',
        );

        $crit = new Criteria();
        $crit->add('book.title', 'foo');
        self::assertTrue($crit->keyContainsValue('book.title'));
    }

    public function testPutAllEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::putAll() is a Java-Hashtable rump and is deprecated. Iterate and call Criteria::add() per entry. Removal targeted for 4.0.',
        );

        $crit = new Criteria();
        $crit->putAll(['book.title' => 'foo']);
        self::assertTrue($crit->getMap() !== []);
    }

    public function testSizeEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::size() is a Java-Hashtable rump and is deprecated. Use count($criteria->getMap()) for where-clause count, or count($criteria->getJoins()) etc. for the relevant subset. Removal targeted for 4.0.',
        );

        $crit = new Criteria();
        $crit->add('book.title', 'foo');
        self::assertSame(1, $crit->size());
    }

    public function testEqualsEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::equals() is a Java-Hashtable rump and is deprecated. Object identity (===) covers most cases; use spl_object_id() or your own domain comparison for value equality. Removal targeted for 4.0.',
        );

        $a = new Criteria();
        $b = new Criteria();
        self::assertTrue($a->equals($b));
    }
}
