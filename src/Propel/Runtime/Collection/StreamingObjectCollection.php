<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Collection;

use Countable;
use Generator;
use IteratorAggregate;
use Propel\Runtime\Exception\LogicException;

/**
 * Phase G.6 (Propel 4.0): {@see IteratorAggregate} + {@see Countable}
 * facade around a {@see Generator} produced by
 * {@see \Propel\Runtime\ActiveQuery\ModelCriteria::findStream()}.
 *
 * Use case: code paths that accept a `Collection` / `iterable` and want
 * the streaming memory profile without materializing the full result.
 * Wrap the Generator once, hand the wrapper around like any iterable.
 *
 * Semantics that differ from {@see Collection}:
 *
 * - **Not array-backed.** No `ArrayAccess`, no `[]` operator, no random
 *   reads. The underlying iterator yields rows in fetch order; positional
 *   indexing would require buffering, defeating the streaming contract.
 * - **Single-pass.** A {@see Generator} cannot be rewound once started.
 *   Iterating twice, or calling `count()` and then iterating, exhausts
 *   the generator — the second pass yields nothing. Callers that need
 *   replay must materialize via `iterator_to_array($generator)` and use
 *   the eager {@see ObjectCollection}.
 * - **Lazy `count()`.** Counting drains the generator (single pass over
 *   the rows). The result is cached so a follow-up `count()` returns
 *   the same number, but no more rows can be iterated after the count.
 *   Calling `count()` before iterating is therefore destructive — guard
 *   accordingly, or pre-count via the eager path.
 *
 * @api Tier 2 SPI; consumed by application code that wants to pass a
 *      streamed result through APIs expecting an iterable.
 *
 * @template-implements \IteratorAggregate<int, \Propel\Runtime\ActiveRecord\ActiveRecordInterface>
 *
 * @psalm-api
 */
final class StreamingObjectCollection implements IteratorAggregate, Countable
{
    /**
     * @var \Generator<int, \Propel\Runtime\ActiveRecord\ActiveRecordInterface>
     */
    private Generator $generator;

    /**
     * Cached row count after `count()` has drained the generator. Null
     * until first count.
     *
     * @var int<0, max>|null
     */
    private ?int $cachedCount = null;

    /**
     * @var bool Set once the generator has been touched (via getIterator() or count()).
     */
    private bool $consumed = false;

    /**
     * @param \Generator<int, \Propel\Runtime\ActiveRecord\ActiveRecordInterface> $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Returns the wrapped Generator. After this returns, the generator is
     * considered "consumed": a second call throws {@see LogicException} —
     * the natural exhaustion semantics of PHP generators give no useful
     * second-pass behavior, and silent empty iteration would mask bugs.
     *
     * @throws \Propel\Runtime\Exception\LogicException When called more than once on the same instance.
     *
     * @return \Generator<int, \Propel\Runtime\ActiveRecord\ActiveRecordInterface>
     */
    #[\Override]
    public function getIterator(): Generator
    {
        if ($this->consumed) {
            throw new LogicException(
                'StreamingObjectCollection is single-pass: the underlying Generator '
                . 'has already been iterated or counted. Materialize via '
                . 'iterator_to_array() and use ObjectCollection if replay is required.',
            );
        }
        $this->consumed = true;

        return $this->generator;
    }

    /**
     * Drains the generator to count its rows. Cached so a subsequent
     * `count()` is O(1), but the generator is exhausted as a side effect
     * — see class docblock.
     *
     * @throws \Propel\Runtime\Exception\LogicException When the underlying generator was already iterated.
     *
     * @return int<0, max>
     */
    #[\Override]
    public function count(): int
    {
        if ($this->cachedCount !== null) {
            return $this->cachedCount;
        }

        if ($this->consumed) {
            throw new LogicException(
                'StreamingObjectCollection::count() called after the Generator '
                . 'was already consumed; no rows remain to count.',
            );
        }
        $this->consumed = true;

        $n = 0;
        foreach ($this->generator as $_) {
            $n++;
        }
        $this->cachedCount = max(0, $n);

        return $this->cachedCount;
    }
}
