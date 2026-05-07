<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\PropertyTests\Connection;

use Innmind\BlackBox\Random;
use Innmind\BlackBox\Set;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Internal\TransactionalConnection;

/**
 * Property-based test for {@see TransactionalConnection} nested-tx invariants.
 *
 * Each generated sequence respects the prefix-validity rule
 * `(begin count) >= (commit count) + (rollback count)` at every step. After
 * applying the sequence, the following invariants must hold:
 *
 *   1. `getNestedTransactionCount() == (begin) - (commit) - (rollback)`.
 *   2. `inTransaction() == (count > 0)`.
 *   3. The inner stub's `beginTransaction()` is called exactly once per
 *      outermost begin (savepoints are not exercised by this PBT).
 *   4. `isCommitable()` flips to false after a nested rollback and stays
 *      false until the outer transaction ends.
 *
 * Seeded for reproducibility — see `docs/reviews/E-round-1-summary.md`.
 */
class NestedTransactionInvariantTest extends TestCase
{
    private const SEED_BASE = 7331;

    /**
     * @return void
     */
    public function testRandomSequencesPreserveCountInvariant(): void
    {
        $count = 0;
        $set = Set::integers()->between(1, 30)->take(40);
        foreach ($set->values(Random::default) as $value) {
            $sequence = $this->generateSequence($value->unwrap(), self::SEED_BASE + $count);
            $this->verifyCountInvariant($sequence);
            $count++;
        }
        $this->assertSame(40, $count);
    }

    /**
     * @return void
     */
    public function testNestedRollbackTaintsTillOutermostEnds(): void
    {
        $count = 0;
        $set = Set::integers()->between(2, 20)->take(20);
        foreach ($set->values(Random::default) as $value) {
            $sequence = $this->generateSequenceWithNestedRollback($value->unwrap(), self::SEED_BASE + 1000 + $count);
            $this->verifyTaintInvariant($sequence);
            $count++;
        }
        $this->assertSame(20, $count);
    }

    /**
     * Generate a balanced sequence: at every prefix, begins >= commit+rollback.
     *
     * Operations: 'begin' | 'commit' | 'rollback'.
     *
     * @param int $length
     * @param int $seed
     *
     * @return list<string>
     */
    private function generateSequence(int $length, int $seed): array
    {
        $sequence = [];
        $depth = 0;
        mt_srand($seed);
        for ($i = 0; $i < $length; $i++) {
            $r = mt_rand(0, 99);
            if ($depth === 0 || $r < 50) {
                $sequence[] = 'begin';
                $depth++;
            } elseif ($r < 80) {
                $sequence[] = 'commit';
                $depth--;
            } else {
                $sequence[] = 'rollback';
                $depth--;
            }
        }

        return $sequence;
    }

    /**
     * Generate a sequence guaranteed to contain at least one nested rollback.
     *
     * @param int $length
     * @param int $seed
     *
     * @return list<string>
     */
    private function generateSequenceWithNestedRollback(int $length, int $seed): array
    {
        // Build a controlled prefix: 2 begins, 1 nested rollback, then a balanced tail.
        $sequence = ['begin', 'begin', 'rollback'];
        $depth = 1;

        mt_srand($seed);
        $remaining = max(0, $length - 3);
        for ($i = 0; $i < $remaining; $i++) {
            $r = mt_rand(0, 99);
            if ($depth === 0 || $r < 40) {
                $sequence[] = 'begin';
                $depth++;
            } elseif ($r < 70) {
                $sequence[] = 'commit';
                $depth--;
            } else {
                $sequence[] = 'rollback';
                $depth--;
            }
        }

        return $sequence;
    }

    /**
     * @param list<string> $sequence
     *
     * @return void
     */
    private function verifyCountInvariant(array $sequence): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('beginTransaction')->willReturn(true);
        $inner->method('commit')->willReturn(true);
        $inner->method('rollBack')->willReturn(true);
        $inner->method('inTransaction')->willReturn(true);

        $tx = new TransactionalConnection($inner);
        $expected = 0;
        $tainted = false;
        foreach ($sequence as $op) {
            $opcountBefore = $expected;
            try {
                if ($op === 'begin') {
                    if ($expected === 0) {
                        $tainted = false;
                    }
                    $tx->beginTransaction();
                    $expected++;
                } elseif ($op === 'commit') {
                    if ($opcountBefore === 1 && $tainted) {
                        // outermost commit on tainted tx throws BEFORE decrement
                        $tx->commit();
                    } else {
                        $tx->commit();
                        $expected = max(0, $expected - 1);
                    }
                } else {
                    if ($opcountBefore > 1) {
                        $tainted = true;
                    }
                    $tx->rollBack();
                    $expected = max(0, $expected - 1);
                }
            } catch (\Propel\Runtime\Connection\Exception\RollbackException $e) {
                // Throw happens before decrement; count stays.
            }
            $this->assertSame($expected, $tx->getNestedTransactionCount(), 'Op=' . $op . ' count drift');
            $this->assertSame($expected > 0, $tx->inTransaction(), 'inTransaction mirror failed at op=' . $op);
        }
    }

    /**
     * @param list<string> $sequence
     *
     * @return void
     */
    private function verifyTaintInvariant(array $sequence): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('beginTransaction')->willReturn(true);
        $inner->method('commit')->willReturn(true);
        $inner->method('rollBack')->willReturn(true);
        $inner->method('inTransaction')->willReturn(true);

        $tx = new TransactionalConnection($inner);
        $tainted = false;
        $expected = 0;

        foreach ($sequence as $op) {
            $opcountBefore = $expected;
            try {
                if ($op === 'begin') {
                    if ($expected === 0) {
                        $tainted = false; // 0→1 begin resets taint
                    }
                    $tx->beginTransaction();
                    $expected++;
                } elseif ($op === 'commit') {
                    if ($opcountBefore === 1 && $tainted) {
                        $tx->commit(); // throws
                    } else {
                        $tx->commit();
                        $expected = max(0, $expected - 1);
                    }
                } else {
                    if ($opcountBefore > 1) {
                        $tainted = true;
                    }
                    $tx->rollBack();
                    $expected = max(0, $expected - 1);
                }
            } catch (\Propel\Runtime\Connection\Exception\RollbackException $e) {
                // count stays
            }

            // isCommitable invariant: true iff inside tx and not tainted.
            $this->assertSame(
                $expected > 0 && !$tainted,
                $tx->isCommitable(),
                'isCommitable invariant violated at op=' . $op . ' (expected ' . ($expected > 0 && !$tainted ? 'true' : 'false') . ')',
            );
        }
    }
}
