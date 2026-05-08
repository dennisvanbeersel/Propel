<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Formatter;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\LogicException;

/**
 * Object formatter for Propel query
 * format() returns a ObjectCollection of Propel model objects
 *
 * @author Francois Zaninotto
 */
class ObjectFormatter extends AbstractFormatter
{
    use ObjectRowHydratorTrait;

    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @param \Propel\Runtime\DataFetcher\DataFetcherInterface|null $dataFetcher
     *
     * @throws \Propel\Runtime\Exception\LogicException
     *
     * @return \Propel\Runtime\Collection\Collection|array
     */
    #[\Override]
    public function format(?DataFetcherInterface $dataFetcher = null)
    {
        $this->checkInit();
        if ($dataFetcher) {
            $this->setDataFetcher($dataFetcher);
        } else {
            $dataFetcher = $this->getDataFetcher();
        }

        $collection = $this->getCollection();

        if ($this->isWithOneToMany()) {
            if ($this->hasLimit) {
                throw new LogicException('Cannot use limit() in conjunction with with() on a one-to-many relationship. Please remove the with() call, or the limit() call.');
            }
            foreach ($dataFetcher as $row) {
                $object = $this->getAllObjectsFromRow($row);
                $pk = $object->getPrimaryKey();
                $serializedPk = serialize($pk);

                if (!isset($this->objects[$serializedPk])) {
                    $this->objects[$serializedPk] = $object;
                    $collection[] = $object;
                }
            }
        } else {
            // only many-to-one relationships
            foreach ($dataFetcher as $row) {
                $collection[] = $this->getAllObjectsFromRow($row);
            }
        }
        $dataFetcher->close();

        return $collection;
    }

    /**
     * @return string|null
     */
    #[\Override]
    public function getCollectionClassName(): ?string
    {
        return $this->getTableMap()->getCollectionClassName();
    }

    /**
     * @param \Propel\Runtime\DataFetcher\DataFetcherInterface|null $dataFetcher
     *
     * @throws \Propel\Runtime\Exception\LogicException
     *
     * @return \Propel\Runtime\ActiveRecord\ActiveRecordInterface|null
     */
    #[\Override]
    public function formatOne(?DataFetcherInterface $dataFetcher = null): ?ActiveRecordInterface
    {
        $this->checkInit();
        $result = null;

        if ($this->isWithOneToMany() && $this->hasLimit) {
            throw new LogicException('Cannot use limit() in conjunction with with() on a one-to-many relationship. Please remove the with() call, or the limit() call.');
        }

        if ($dataFetcher) {
            $this->setDataFetcher($dataFetcher);
        } else {
            $dataFetcher = $this->getDataFetcher();
        }

        foreach ($dataFetcher as $row) {
            $result = $this->getAllObjectsFromRow($row);
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

    // getAllObjectsFromRow() is now provided by ObjectRowHydratorTrait —
    // shared with StreamingObjectFormatter (Phase G.6.1).
}
