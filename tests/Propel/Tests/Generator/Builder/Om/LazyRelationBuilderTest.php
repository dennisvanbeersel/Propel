<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Builder\Om;

use Propel\Generator\Builder\Om\LazyRelationBuilder;
use Propel\Generator\Exception\InvalidArgumentException;
use Propel\Tests\TestCase;

/**
 * Phase G.3.2 — LazyRelationBuilder emitter unit tests.
 *
 * Verifies that the emitter produces a `init<RelCol>()` body backed by
 * PHP 8.4 ReflectionClass::newLazyGhost plus a sister `doInit<RelCol>()`
 * hydrator. Pure string-shape contract — integration into ObjectBuilder
 * lands in G.3.3 with golden-tree regen.
 */
class LazyRelationBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testEmitsLazyInitMethodWithNewLazyGhost(): void
    {
        $emitter = new LazyRelationBuilder();
        $code = $emitter->emit(
            'Books',
            'collBooks',
            'BookTableMap::getTableMap()->getCollectionClassName()',
            'Propel\\Tests\\Bookstore\\Book',
        );

        $this->assertStringContainsString('public function initBooks(bool $overrideExisting = true): void', $code);
        $this->assertStringContainsString('newLazyGhost(', $code);
        $this->assertStringContainsString(
            'fn (\Propel\Runtime\Collection\Collection $coll) => $this->doInitBooks($coll)',
            $code,
        );
        $this->assertStringContainsString(
            '$collectionClassName = BookTableMap::getTableMap()->getCollectionClassName();',
            $code,
        );
        $this->assertStringContainsString('$this->collBooks = (new \ReflectionClass($collectionClassName))->newLazyGhost(', $code);
    }

    /**
     * @return void
     */
    public function testEmitsDoInitHydratorWithSetModelLiteral(): void
    {
        $emitter = new LazyRelationBuilder();
        $code = $emitter->emit(
            'Books',
            'collBooks',
            'BookTableMap::getTableMap()->getCollectionClassName()',
            'Propel\\Tests\\Bookstore\\Book',
        );

        $this->assertStringContainsString('private function doInitBooks(\Propel\Runtime\Collection\Collection $coll): void', $code);
        $this->assertStringContainsString("\$coll->setModel('Propel\\\\Tests\\\\Bookstore\\\\Book');", $code);
    }

    /**
     * @return void
     */
    public function testEmittedOverrideGuardMatchesEagerSemantics(): void
    {
        $emitter = new LazyRelationBuilder();
        $code = $emitter->emit('Reviews', 'collReviews', 'ReviewTableMap::TABLE_NAME', 'Acme\\Review');

        // Eager init early-returns when collection exists and override flag is false;
        // lazy form must preserve that contract so callsites depending on the guard
        // (e.g. ReferrerBuilderTrait::addRefFKAdd) continue to behave identically.
        $this->assertStringContainsString('if ($this->collReviews !== null && !$overrideExisting) {', $code);
        $this->assertStringContainsString('return;', $code);
    }

    /**
     * @return void
     */
    public function testEmittedCodeIsValidPhp(): void
    {
        $emitter = new LazyRelationBuilder();
        $code = $emitter->emit('Books', 'collBooks', 'BookTableMap::TABLE_NAME', 'Acme\\Book');

        // Wrap the emitted methods in a class shell so php -l can syntax-check them.
        $tmp = tempnam(sys_get_temp_dir(), 'lazyrel-');
        $this->assertNotFalse($tmp);
        try {
            file_put_contents($tmp, "<?php\nclass Stub {\n" . $code . "\n}\n");
            $output = [];
            $rc = 1;
            exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $output, $rc);
            $this->assertSame(0, $rc, 'Emitted code failed php -l syntax check: ' . implode("\n", $output));
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * @return void
     */
    public function testRejectsInvalidRelColIdentifier(): void
    {
        $emitter = new LazyRelationBuilder();
        $this->expectException(InvalidArgumentException::class);
        $emitter->emit('1Bad', 'collX', 'X::TABLE_NAME', 'X');
    }

    /**
     * @return void
     */
    public function testRejectsInvalidCollNameIdentifier(): void
    {
        $emitter = new LazyRelationBuilder();
        $this->expectException(InvalidArgumentException::class);
        $emitter->emit('Books', 'coll-Books', 'X::TABLE_NAME', 'X');
    }

    /**
     * @return void
     */
    public function testRejectsEmptyCollectionExpression(): void
    {
        $emitter = new LazyRelationBuilder();
        $this->expectException(InvalidArgumentException::class);
        $emitter->emit('Books', 'collBooks', '', 'X');
    }

    /**
     * @return void
     */
    public function testRejectsEmptyModelFqcn(): void
    {
        $emitter = new LazyRelationBuilder();
        $this->expectException(InvalidArgumentException::class);
        $emitter->emit('Books', 'collBooks', 'X::TABLE_NAME', '');
    }

    /**
     * @return void
     */
    public function testEscapesSingleQuotesInModelFqcn(): void
    {
        $emitter = new LazyRelationBuilder();
        $code = $emitter->emit('Books', 'collBooks', 'X::TABLE_NAME', "Acme\\O'Brien\\Book");

        $this->assertStringContainsString("\$coll->setModel('Acme\\\\O\\'Brien\\\\Book');", $code);
    }

    /**
     * @return void
     */
    public function testBaseIndentControlsLeadingWhitespace(): void
    {
        $emitter = new LazyRelationBuilder();
        $oneIndent = $emitter->emit('Books', 'collBooks', 'X::TABLE_NAME', 'Acme\\Book', 1);
        $twoIndent = $emitter->emit('Books', 'collBooks', 'X::TABLE_NAME', 'Acme\\Book', 2);

        // baseIndent = 1 → "    public ..."; baseIndent = 2 → "        public ..."
        $this->assertStringContainsString("\n    public function initBooks", "\n" . $oneIndent);
        $this->assertStringContainsString("\n        public function initBooks", "\n" . $twoIndent);
    }
}
