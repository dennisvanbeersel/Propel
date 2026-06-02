<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Om;

use Propel\Generator\Builder\Util\CodeEmitter;
use Propel\Generator\Exception\InvalidArgumentException;

/**
 * Phase G.3 (Propel 4.0, opt-in): emits the lazy form of a
 * one-to-many relation initializer for tables marked
 * `<table useLazyObjects="true">`.
 *
 * The legacy form (in {@see ReferrerBuilderTrait::addRefFKInit}) eagerly
 * instantiates `new $collectionClassName` and immediately calls
 * `setModel('...')`. Even when the relation is never read, the empty
 * collection is constructed and configured.
 *
 * The lazy form uses PHP 8.4 `ReflectionClass::newLazyGhost()` to defer
 * construction-and-model-wiring until the collection is first touched.
 * The wrapper hands every property read / method call off to the closure
 * passed to `newLazyGhost`, which in turn calls the sister
 * `doInit<RelCol>()` method to populate the (now real) instance.
 *
 * Risk note (per plan §G risk register #1): lazy-ghosts + property hooks
 * (G.5) haven't been combined extensively in production ORMs. G.3 ships
 * opt-in only; the §G rollback criterion (≤+5% latency on 100k-row
 * with() query — measured in G.3.4) gates the 4.1 default flip.
 *
 * @internal Tier 3 internal helper. Public surface committed via the
 * golden bookstore tree, not via tracked-classes.txt — emission
 * stability is enforced through the diff of generated entities, not
 * through this class's own method shapes.
 *
 * @psalm-api
 */
final class LazyRelationBuilder
{
    /**
     * Emit the `init<RelCol>()` + `doInit<RelCol>()` pair.
     *
     * The two methods together replace the body produced by
     * {@see ReferrerBuilderTrait::addRefFKInit} when the parent table
     * opts in via `useLazyObjects="true"`. Output is indented at
     * `$baseIndent` levels (4 spaces per level) so it slots into the
     * surrounding class body without further reformatting by the
     * caller.
     *
     * @param string $relCol Plural PhpName of the relation, e.g. `"Books"`.
     * @param string $collName Underlying property name, e.g. `"collBooks"`.
     * @param string $collectionClassNameExpr PHP expression that resolves to the FQCN of the collection class at runtime, e.g. `"BookTableMap::getTableMap()->getCollectionClassName()"`.
     * @param string $modelFqcn FQCN of the model class held by the collection, e.g. `"Propel\\Tests\\Bookstore\\Book"`.
     * @param int $baseIndent Indent levels (4 spaces each) at which the emitted methods should sit. Method bodies nest below `$baseIndent`.
     *
     * @throws \Propel\Generator\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function emit(
        string $relCol,
        string $collName,
        string $collectionClassNameExpr,
        string $modelFqcn,
        int $baseIndent = 1
    ): string {
        $this->validatePhpIdentifier($relCol, 'relCol');
        $this->validatePhpIdentifier($collName, 'collName');
        if ($collectionClassNameExpr === '') {
            throw new InvalidArgumentException('collectionClassNameExpr must not be empty');
        }
        if ($modelFqcn === '') {
            throw new InvalidArgumentException('modelFqcn must not be empty');
        }

        $emitter = new CodeEmitter($baseIndent);

        $initBody = $emitter->methodBody(
            'init' . $relCol,
            'public',
            [['name' => 'overrideExisting', 'type' => 'bool', 'default' => 'true']],
            'void',
            "Initializes the {$collName} collection (lazy form, Phase G.3).\n\n"
                . "Hands a PHP 8.4 ReflectionClass::newLazyGhost stub to {$collName};\n"
                . "the actual model wiring is deferred to doInit{$relCol}() and only\n"
                . "fires when the collection is first read.\n\n"
                . "@param bool \$overrideExisting If true, re-init even if non-null.\n\n"
                . '@return void',
        );

        $emitter->line('if ($this->' . $collName . ' !== null && !$overrideExisting) {');
        $emitter->indent();
        $emitter->line('return;');
        $emitter->dedent();
        $emitter->line('}');
        $emitter->blank();
        $emitter->line('$collectionClassName = ' . $collectionClassNameExpr . ';');
        $emitter->blank();
        $emitter->line('$this->' . $collName . ' = (new \ReflectionClass($collectionClassName))->newLazyGhost(');
        $emitter->indent();
        $emitter->line('fn (\Propel\Runtime\Collection\Collection $coll) => $this->doInit' . $relCol . '($coll),');
        $emitter->dedent();
        $emitter->line(');');

        unset($initBody);
        $emitter->blank();

        $doInitBody = $emitter->methodBody(
            'doInit' . $relCol,
            'private',
            [['name' => 'coll', 'type' => '\Propel\Runtime\Collection\Collection']],
            'void',
            "Sister hydrator for the lazy init{$relCol}() ghost (Phase G.3).\n\n"
                . "Invoked by the newLazyGhost initializer when the collection is\n"
                . "first read. Mirrors the eager init's setModel() side effect.\n\n"
                . "@param \\Propel\\Runtime\\Collection\\Collection \$coll Lazy stub being hydrated.\n\n"
                . '@return void',
        );

        $emitter->line('$coll->setModel(' . CodeEmitter::phpString($modelFqcn) . ');');

        unset($doInitBody);

        return $emitter->toString();
    }

    /**
     * @param string $value
     * @param string $context
     *
     * @throws \Propel\Generator\Exception\InvalidArgumentException
     *
     * @return void
     */
    private function validatePhpIdentifier(string $value, string $context): void
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be a valid PHP identifier; got: %s', $context, $value));
        }
    }
}
