<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Formatter;

use Generator;
use Propel\Runtime\ActiveQuery\BaseModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Telemetry\NoOpTelemetry;
use Propel\Runtime\Telemetry\TelemetryInterface;

/**
 * Phase G.6 (Propel 4.0): Generator-based streaming sibling to
 * {@see ObjectFormatter}.
 *
 * `format()` yields hydrated entities one at a time as rows arrive from the
 * statement, never materializing the full collection. Memory profile stays
 * O(1) in the number of rows (modulo the underlying DataFetcher buffering).
 *
 * Trade-offs vs the eager {@see ObjectFormatter}:
 *
 * - One-to-many `with()` is rejected (`LogicException`). The eager
 *   formatter dedupes parents by serialized PK across all rows, which is
 *   incompatible with streaming — a parent cannot be yielded until every
 *   joined child row has been seen, so the "hold everything" cost is the
 *   same as eager hydration.
 * - `findOne()` callers should keep using `ObjectFormatter`; streaming has
 *   no advantage on a single row.
 * - When the strong-ref instance pool is active, every streamed entity is
 *   retained in `InstancePoolTrait::$instances` even after the consumer
 *   discards it — combine with the WeakMap pool variant (G.7) to truly
 *   keep memory bounded across a 100k-row stream. Documented in
 *   `findStream()` PHPDoc on `ModelCriteria`.
 *
 * Each yielded row records hydration latency through the
 * {@see TelemetryInterface}. Default is {@see NoOpTelemetry}; pass a
 * configured instance via the constructor or `setTelemetry()` to feed the
 * Phase I OTel/Prometheus adapters.
 *
 * Extends {@see AbstractFormatter} directly (not {@see ObjectFormatter})
 * so the Generator return type doesn't conflict with ObjectFormatter's
 * `Collection|array` `format()` contract. Row hydration logic is shared
 * via {@see ObjectRowHydratorTrait}.
 *
 * @psalm-api Public Tier 2 SPI; consumed by {@see \Propel\Runtime\ActiveQuery\ModelCriteria::findStream()} (Phase G.6.2).
 */
class StreamingObjectFormatter extends AbstractFormatter
{
    use ObjectRowHydratorTrait;

    /**
     * Mirror of {@see ObjectFormatter::$objects} so the trait's PK-dedup
     * branch resolves at compile time. Always empty for streaming —
     * streaming-with-one-to-many is rejected up-front, and per-PK
     * identity is irrelevant when each row is yielded standalone.
     *
     * @var array<string, \Propel\Runtime\ActiveRecord\ActiveRecordInterface>
     */
    protected array $objects = [];

    /**
     * @var \Propel\Runtime\Telemetry\TelemetryInterface
     */
    private TelemetryInterface $telemetry;

    /**
     * Optional telemetry sink may be passed at construction time. When
     * omitted, a {@see NoOpTelemetry} instance is used so the formatter
     * stays drop-in compatible with the eager {@see ObjectFormatter}
     * surface.
     *
     * @param \Propel\Runtime\ActiveQuery\BaseModelCriteria|null $criteria
     * @param \Propel\Runtime\DataFetcher\DataFetcherInterface|null $dataFetcher
     * @param \Propel\Runtime\Telemetry\TelemetryInterface|null $telemetry
     */
    public function __construct(
        ?BaseModelCriteria $criteria = null,
        ?DataFetcherInterface $dataFetcher = null,
        ?TelemetryInterface $telemetry = null
    ) {
        parent::__construct($criteria, $dataFetcher);
        $this->telemetry = $telemetry ?? new NoOpTelemetry();
    }

    /**
     * Override the telemetry sink post-construction. Used by callers that
     * resolve the adapter from a service container after the formatter is
     * already built (e.g. ModelCriteria::findStream()).
     *
     * @psalm-api
     *
     * @param \Propel\Runtime\Telemetry\TelemetryInterface $telemetry
     *
     * @return void
     */
    public function setTelemetry(TelemetryInterface $telemetry): void
    {
        $this->telemetry = $telemetry;
    }

    /**
     * Stream-formats the underlying DataFetcher: yields each hydrated
     * entity as the row arrives, never materializing a full collection.
     * Memory stays O(1) in row count (modulo DataFetcher buffering).
     *
     * One-to-many `with()` joins are rejected — see class docblock.
     *
     * @param \Propel\Runtime\DataFetcher\DataFetcherInterface|null $dataFetcher
     *
     * @throws \Propel\Runtime\Exception\LogicException When `with()` joins a one-to-many relation.
     *
     * @return \Generator<int, \Propel\Runtime\ActiveRecord\ActiveRecordInterface>
     */
    #[\Override]
    public function format(?DataFetcherInterface $dataFetcher = null): Generator
    {
        $this->checkInit();
        if ($dataFetcher) {
            $this->setDataFetcher($dataFetcher);
        } else {
            $dataFetcher = $this->getDataFetcher();
        }

        $class = (string)$this->getClass();

        try {
            if ($this->isWithOneToMany()) {
                throw new LogicException(
                    'StreamingObjectFormatter does not support one-to-many with(): '
                    . 'streaming yields rows as they arrive, but parent dedup requires '
                    . 'materializing all rows. Drop the with() on the collection-side '
                    . 'relation, or use the eager ObjectFormatter via find().',
                );
            }

            $index = 0;
            foreach ($dataFetcher as $row) {
                $start = hrtime(true);
                $object = $this->getAllObjectsFromRow($row);
                $this->telemetry->recordHydrationDuration(
                    $class,
                    (hrtime(true) - $start) / 1000.0,
                );

                yield $index++ => $object;
            }
        } finally {
            $dataFetcher->close();
        }
    }

    /**
     * Single-row counterpart — drains the fetcher until the last row,
     * mirroring {@see ObjectFormatter::formatOne}. Streaming offers no
     * win on a single result, so the implementation is non-streaming.
     *
     * @param \Propel\Runtime\DataFetcher\DataFetcherInterface|null $dataFetcher
     *
     * @return \Propel\Runtime\ActiveRecord\ActiveRecordInterface|null
     */
    #[\Override]
    public function formatOne(?DataFetcherInterface $dataFetcher = null): ?ActiveRecordInterface
    {
        $this->checkInit();
        if ($dataFetcher) {
            $this->setDataFetcher($dataFetcher);
        } else {
            $dataFetcher = $this->getDataFetcher();
        }

        $result = null;

        try {
            foreach ($dataFetcher as $row) {
                $result = $this->getAllObjectsFromRow($row);
            }
        } finally {
            $dataFetcher->close();
        }

        return $result;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function isObjectFormatter(): bool
    {
        return true;
    }
}
