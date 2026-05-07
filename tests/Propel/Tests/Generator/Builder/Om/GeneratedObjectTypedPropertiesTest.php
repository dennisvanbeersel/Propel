<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Generator\Builder\Om;

use PHPUnit\Framework\Attributes\Group;
use Propel\Tests\Bookstore\Book;
use Propel\Tests\Bookstore\BookstoreEmployeeAccount;
use Propel\Tests\TestCase;
use ReflectionProperty;
use TypeError;

/**
 * Regression coverage for the strict-typed AR column properties introduced
 * in Phase B (umbrella spec §3.5; B.2.3-B.2.5).
 *
 * Generated AR base classes now declare column-backed properties with
 * native scalar types (`?int $id = null;`, `?string $title = null;`,
 * `?bool $enabled = null;`, `?float $price = null;`). Direct assignment
 * (e.g. via reflection or trait composition that bypasses the setter)
 * MUST raise TypeError when the value does not match the declared type.
 *
 * These tests drive that contract end-to-end against the regenerated
 * bookstore golden tree.
 */
#[Group('generator')]
class GeneratedObjectTypedPropertiesTest extends TestCase
{
    /**
     * @return void
     */
    public function testTypedIntColumnPropertyRejectsWrongType(): void
    {
        $book = new Book();
        $r = new ReflectionProperty($book, 'id');
        $r->setAccessible(true);

        $this->expectException(TypeError::class);
        $r->setValue($book, 'not-an-int');
    }

    /**
     * @return void
     */
    public function testTypedStringColumnPropertyRejectsWrongType(): void
    {
        $book = new Book();
        $r = new ReflectionProperty($book, 'title');
        $r->setAccessible(true);

        // PHP coerces int <-> string for ?string properties; arrays cannot
        // coerce, so they are the canonical wrong-type sentinel.
        $this->expectException(TypeError::class);
        $r->setValue($book, ['not', 'a', 'string']);
    }

    /**
     * @return void
     */
    public function testTypedFloatColumnPropertyRejectsWrongType(): void
    {
        $book = new Book();
        $r = new ReflectionProperty($book, 'price');
        $r->setAccessible(true);

        $this->expectException(TypeError::class);
        $r->setValue($book, 'not-a-float');
    }

    /**
     * @return void
     */
    public function testTypedBoolColumnPropertyRejectsWrongType(): void
    {
        $account = new BookstoreEmployeeAccount();
        $r = new ReflectionProperty($account, 'enabled');
        $r->setAccessible(true);

        // Strings/ints coerce to bool under PHP juggling; arrays cannot.
        $this->expectException(TypeError::class);
        $r->setValue($account, ['not', 'a', 'bool']);
    }

    /**
     * Sanity check: nullable typed properties must still accept null
     * (the `?` in `?int`, `?string`, `?bool`, `?float`).
     *
     * @return void
     */
    public function testTypedColumnPropertiesAcceptNull(): void
    {
        $book = new Book();
        foreach (['id', 'title', 'price'] as $prop) {
            $r = new ReflectionProperty($book, $prop);
            $r->setAccessible(true);
            $r->setValue($book, null);
            $this->assertNull($r->getValue($book), sprintf('Property %s must accept null', $prop));
        }
    }
}
