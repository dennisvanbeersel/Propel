<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Formatter;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Formatter\AbstractFormatter;
use ReflectionClass;
use ReflectionMethod;
use stdClass;

/**
 * Regression: AbstractFormatterWithHydration::hydratePropelObjectCollection
 * and OnDemandFormatter::current both instantiated `new ReflectionClass($class)`
 * inside the per-row hydration loop. For STI hydration of N rows × M with()
 * relations this allocated O(N×M) ReflectionClass objects on the hot path.
 *
 * Fix: AbstractFormatter::getReflectionClass() returns a per-class cached
 * instance (static array). This test verifies the cache returns identical
 * instances for repeated calls with the same class name.
 */
class AbstractFormatterReflectionCacheTest extends TestCase
{
    public function testGetReflectionClassReturnsCachedInstance(): void
    {
        // getReflectionClass is protected static; reach it via reflection.
        $method = new ReflectionMethod(AbstractFormatter::class, 'getReflectionClass');

        $first = $method->invoke(null, stdClass::class);
        $second = $method->invoke(null, stdClass::class);

        $this->assertInstanceOf(ReflectionClass::class, $first);
        $this->assertSame($first, $second, 'cache must return identical ReflectionClass instances for the same class');
    }

    public function testGetReflectionClassDistinguishesByClassName(): void
    {
        $method = new ReflectionMethod(AbstractFormatter::class, 'getReflectionClass');

        $stdClass = $method->invoke(null, stdClass::class);
        $exception = $method->invoke(null, \RuntimeException::class);

        $this->assertNotSame($stdClass, $exception, 'cache must produce distinct instances for distinct class names');
        $this->assertSame(stdClass::class, $stdClass->getName());
        $this->assertSame(\RuntimeException::class, $exception->getName());
    }
}
