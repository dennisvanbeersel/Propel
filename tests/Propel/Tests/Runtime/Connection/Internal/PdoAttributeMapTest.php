<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Internal;

use PDO;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Internal\PdoAttributeMap;
use Propel\Runtime\Exception\InvalidArgumentException;

/**
 * Unit tests for {@see PdoAttributeMap}.
 *
 * Verifies that string PDO attribute names resolve to the right int constants
 * without runtime `defined()`/`constant()` calls (umbrella spec §6.2 risk #3).
 */
class PdoAttributeMapTest extends TestCase
{
    /**
     * @return void
     */
    public function testResolvesIntPassThrough(): void
    {
        $this->assertSame(PDO::ATTR_ERRMODE, PdoAttributeMap::resolve(PDO::ATTR_ERRMODE));
    }

    /**
     * @return void
     */
    public function testResolvesBareName(): void
    {
        $this->assertSame(PDO::ATTR_ERRMODE, PdoAttributeMap::resolve('ATTR_ERRMODE'));
        $this->assertSame(PDO::ATTR_CASE, PdoAttributeMap::resolve('ATTR_CASE'));
    }

    /**
     * @return void
     */
    public function testResolvesPdoQualifiedName(): void
    {
        $this->assertSame(PDO::ATTR_ERRMODE, PdoAttributeMap::resolve('PDO::ATTR_ERRMODE'));
    }

    /**
     * @return void
     */
    public function testResolvesLeadingBackslashName(): void
    {
        $this->assertSame(PDO::ATTR_ERRMODE, PdoAttributeMap::resolve('\\PDO::ATTR_ERRMODE'));
    }

    /**
     * @return void
     */
    public function testThrowsOnUnknownName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid PDO option/attribute name specified: `ATTR_NONSENSE`');

        PdoAttributeMap::resolve('ATTR_NONSENSE');
    }

    /**
     * @return void
     */
    public function testMapContainsKnownConstants(): void
    {
        $map = PdoAttributeMap::map();

        $this->assertArrayHasKey('ATTR_ERRMODE', $map);
        $this->assertArrayHasKey('ATTR_CASE', $map);
        $this->assertSame(PDO::ATTR_ERRMODE, $map['ATTR_ERRMODE']);
    }
}
