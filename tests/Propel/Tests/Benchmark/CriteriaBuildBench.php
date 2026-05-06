<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Benchmark;

use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Measures Criteria construction + filter chaining throughput on synthetic
 * data. No DB required. Phase F's Criteria split refactor and Phase G's
 * lazy-object collections compare against this baseline.
 */
class CriteriaBuildBench extends AbstractBench
{
    public function name(): string
    {
        return 'criteria_build_filter_chain_1k';
    }

    public function run(): void
    {
        for ($i = 0; $i < 1_000; $i++) {
            $c = new Criteria();
            $c->add('book.id', $i);
            $c->add('book.title', 'Title ' . $i, Criteria::LIKE);
            $c->addAscendingOrderByColumn('book.id');
        }
    }
}
