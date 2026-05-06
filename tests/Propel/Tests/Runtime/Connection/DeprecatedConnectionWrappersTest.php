<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Connection;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\DebugPDO;
use Propel\Runtime\Connection\PropelPDO;
use Symfony\Bridge\PhpUnit\ExpectUserDeprecationMessageTrait;

/**
 * Per umbrella spec section 6.2: DebugPDO and PropelPDO are aliases of
 * ConnectionWrapper retained only for BC. Constructing them must emit a
 * deprecation pointing at ConnectionWrapper as the supported class.
 */
#[Group('legacy')]
class DeprecatedConnectionWrappersTest extends TestCase
{
    use ExpectUserDeprecationMessageTrait;

    public function testDebugPDOConstructorEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Class "Propel\\Runtime\\Connection\\DebugPDO" is deprecated, use "Propel\\Runtime\\Connection\\ConnectionWrapper" directly.',
        );

        $inner = $this->createMock(ConnectionInterface::class);
        new DebugPDO($inner);
    }

    public function testPropelPDOConstructorEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Class "Propel\\Runtime\\Connection\\PropelPDO" is deprecated, use "Propel\\Runtime\\Connection\\ConnectionWrapper" directly.',
        );

        $inner = $this->createMock(ConnectionInterface::class);
        new PropelPDO($inner);
    }
}
