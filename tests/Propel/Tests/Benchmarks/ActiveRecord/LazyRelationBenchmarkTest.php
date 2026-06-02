<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Benchmarks\ActiveRecord;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Propel\Generator\Util\QuickBuilder;

/**
 * Phase G.3.4 — performance benchmark for the lazy-relation emission opt-in.
 *
 * Compares hydrate-with-relation latency on two parallel schemas that differ
 * only by `<table useLazyObjects="true">` on the parent table:
 *
 *   - LazyBench\Author / LazyBench\Book (`useLazyObjects="true"`)
 *   - EagerBench\Author / EagerBench\Book (legacy eager init)
 *
 * Asserts the §G rollback criterion: lazy hydration latency must stay within
 * +5% of the eager baseline. If this assertion fails, opt-in remains the only
 * path for 4.0 and the default flip target slips to 4.2 (per plan §G.3.4 +
 * §5.1 risk register #1). Failure is captured in `docs/reviews/G-bench-lazy.md`.
 *
 * Scaling: default N = 1000 authors × 3 books each = ~4k rows. Set
 * `PROPEL_BENCH_N=100000` in the environment to run the full plan-scale
 * 100k benchmark. (The default keeps CI runtime sane; Phase G's numerical
 * gate is the larger run, captured manually in the report.)
 *
 * Excluded from `composer test:agnostic` via the `<exclude>Benchmarks/</exclude>`
 * directive in `tests/agnostic.phpunit.xml`. Run via:
 *
 *     vendor/bin/phpunit -c tests/agnostic.phpunit.xml --testsuite benchmarks
 */
#[Group('benchmark')]
class LazyRelationBenchmarkTest extends TestCase
{
    /**
     * @var int
     */
    private const TRIALS = 5;

    /**
     * @var int
     */
    private const BOOKS_PER_AUTHOR = 3;

    /**
     * @var float
     */
    private const ROLLBACK_THRESHOLD = 1.05;

    /**
     * @var bool
     */
    private static bool $schemasBuilt = false;

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        if (self::$schemasBuilt) {
            return;
        }

        if (!class_exists('LazyBench\\Author')) {
            self::buildSchema('lazy_bench', 'LazyBench', true);
        }
        if (!class_exists('EagerBench\\Author')) {
            self::buildSchema('eager_bench', 'EagerBench', false);
        }

        self::$schemasBuilt = true;
    }

    /**
     * @param string $databaseName
     * @param string $namespace
     * @param bool $useLazy
     *
     * @return void
     */
    private static function buildSchema(string $databaseName, string $namespace, bool $useLazy): void
    {
        $lazyAttr = $useLazy ? ' useLazyObjects="true"' : '';
        $schema = <<<XML
<database name="{$databaseName}" namespace="{$namespace}" defaultIdMethod="native">
    <table name="bench_author" phpName="Author"{$lazyAttr}>
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="name" type="VARCHAR" size="100" required="true"/>
    </table>
    <table name="bench_book" phpName="Book">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="title" type="VARCHAR" size="100" required="true"/>
        <column name="author_id" type="INTEGER"/>
        <foreign-key foreignTable="bench_author">
            <reference local="author_id" foreign="id"/>
        </foreign-key>
    </table>
</database>
XML;

        $builder = new QuickBuilder();
        $builder->setSchema($schema);
        $builder->build();
    }

    /**
     * @return int
     */
    private function getN(): int
    {
        $env = getenv('PROPEL_BENCH_N');
        if ($env !== false && ctype_digit($env) && (int)$env > 0) {
            return (int)$env;
        }

        return 1000;
    }

    /**
     * @param string $namespace
     * @param int $n
     *
     * @return void
     */
    private function seed(string $namespace, int $n): void
    {
        $authorClass = $namespace . '\\Author';
        $bookClass = $namespace . '\\Book';

        for ($i = 0; $i < $n; $i++) {
            /** @var \Propel\Runtime\ActiveRecord\ActiveRecordInterface $author */
            $author = new $authorClass();
            /** @phpstan-ignore-next-line dynamic class with magic setter */
            $author->setName('Author ' . $i);
            /** @phpstan-ignore-next-line dynamic class with magic save */
            $author->save();

            for ($j = 0; $j < self::BOOKS_PER_AUTHOR; $j++) {
                /** @var \Propel\Runtime\ActiveRecord\ActiveRecordInterface $book */
                $book = new $bookClass();
                /** @phpstan-ignore-next-line dynamic class with magic setter */
                $book->setTitle('Book ' . $i . '.' . $j);
                /** @phpstan-ignore-next-line dynamic class with magic setter */
                $book->setAuthor($author);
                /** @phpstan-ignore-next-line dynamic class with magic save */
                $book->save();
            }
        }
    }

    /**
     * @param string $namespace
     *
     * @return float seconds elapsed
     */
    private function hydrateOnce(string $namespace): float
    {
        $queryClass = $namespace . '\\AuthorQuery';
        $joinTarget = $namespace . '\\Author.Book';

        $start = hrtime(true);
        /** @phpstan-ignore-next-line dynamic class with static factory */
        $rows = $queryClass::create()
            ->leftJoinWith($joinTarget)
            ->find();

        // Force iteration so deferred work materializes.
        $sum = 0;
        foreach ($rows as $author) {
            /** @phpstan-ignore-next-line dynamic getter */
            foreach ($author->getBooks() as $book) {
                $sum++;
            }
        }
        $elapsed = (hrtime(true) - $start) / 1e9;

        $this->assertGreaterThan(0, $sum, 'Hydration must produce at least one related book');

        return $elapsed;
    }

    /**
     * @param string $namespace
     *
     * @return float median seconds elapsed across self::TRIALS, after one warmup
     */
    private function measureMedian(string $namespace): float
    {
        // One warmup run to prime opcache / autoloader / sqlite page cache.
        $this->hydrateOnce($namespace);

        $samples = [];
        for ($i = 0; $i < self::TRIALS; $i++) {
            $samples[] = $this->hydrateOnce($namespace);
        }
        sort($samples);

        return $samples[(int)floor(self::TRIALS / 2)];
    }

    /**
     * Asserts the lazy/eager ratio is within the §G rollback criterion.
     *
     * @return void
     */
    public function testLazyHydrationStaysWithinRollbackThreshold(): void
    {
        $n = $this->getN();

        $this->seed('LazyBench', $n);
        $this->seed('EagerBench', $n);

        $eager = $this->measureMedian('EagerBench');
        $lazy = $this->measureMedian('LazyBench');
        $ratio = $lazy / $eager;

        // Capture for human follow-up regardless of pass/fail.
        fwrite(
            STDERR,
            sprintf(
                "\n[lazy-bench] N=%d eager=%.4fs lazy=%.4fs ratio=%.3f threshold=%.2f\n",
                $n,
                $eager,
                $lazy,
                $ratio,
                self::ROLLBACK_THRESHOLD,
            ),
        );

        $this->assertLessThanOrEqual(
            self::ROLLBACK_THRESHOLD,
            $ratio,
            sprintf(
                'Lazy hydration ratio %.3f exceeds rollback threshold %.2f. '
                . 'Per plan §G.3.4: opt-in stays the only path for 4.0; '
                . 'default flip target slips to 4.2. Capture the run output in '
                . 'docs/reviews/G-bench-lazy.md.',
                $ratio,
                self::ROLLBACK_THRESHOLD,
            ),
        );
    }
}
