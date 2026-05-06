<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Builder\Om;

use Propel\Tests\Bookstore\Book2;
use Propel\Tests\Bookstore\Book2Style;
use Propel\Tests\TestCase;

/**
 * End-to-end integration test for backed-enum support in generated objects/queries.
 *
 * Uses the regenerated bookstore Book2 fixture (table `book2`, ENUM column `style`
 * with valueSet `novel, essay, poetry`).
 */
class EnumBackedEnumIntegrationTest extends TestCase
{
    /**
     * @return void
     */
    public function testGeneratedEnumExistsAndIsBackedString(): void
    {
        $this->assertTrue(enum_exists(Book2Style::class));
        $this->assertSame('novel', Book2Style::NOVEL->value);
        $this->assertSame('essay', Book2Style::ESSAY->value);
        $this->assertSame('poetry', Book2Style::POETRY->value);
    }

    /**
     * @return void
     */
    public function testSetterAcceptsEnumInstance(): void
    {
        $book = new Book2();
        $book->setStyle(Book2Style::NOVEL);

        $this->assertSame(Book2Style::NOVEL, $book->getStyle());
        $this->assertSame('novel', $book->getStyle()->value);
    }

    /**
     * @return void
     */
    public function testSetterAcceptsBareStringForBackwardCompatibility(): void
    {
        $book = new Book2();
        $book->setStyle('essay');

        $this->assertSame(Book2Style::ESSAY, $book->getStyle());
    }

    /**
     * @return void
     */
    public function testGetterReturnsNullWhenColumnIsNull(): void
    {
        $book = new Book2();
        $this->assertNull($book->getStyle());
    }

    /**
     * Round-trips an enum through setter→getter to verify the generated mutator
     * preserves the value when written via either the enum instance or a
     * cross-table BackedEnum (covering the Versionable mirror-table case).
     *
     * @return void
     */
    public function testSetterAcceptsAnyBackedEnumViaUnionType(): void
    {
        $book = new Book2();
        $alien = MutatorTestProbeEnum::POETRY;
        $book->setStyle($alien);

        $this->assertSame(Book2Style::POETRY, $book->getStyle());
    }
}

/**
 * Test-local backed enum used to cover the BC pathway where a cross-table
 * BackedEnum (e.g. a mirror version table's enum) is passed into the setter.
 *
 * @internal
 */
enum MutatorTestProbeEnum: string
{
    case POETRY = 'poetry';
}
