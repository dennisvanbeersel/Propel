<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Generator\Model;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Propel\Generator\Model\PropelTypes;

/**
 * Phase G.2.6 (Propel 4.0): the legacy mapping types — BU_DATE, BU_TIMESTAMP,
 * BOOLEAN_EMU, OBJECT, PHP_ARRAY — are removed from active use. Resolving
 * any of them via PropelTypes::getPhpNative / getPDOType / getPdoTypeString
 * throws an InvalidArgumentException pointing at the modern replacement.
 *
 * The XSD continues to accept these as valid values per umbrella §3.7
 * (additivity promise — schemas remain parseable forever); only the
 * generator pipeline refuses to emit code for them.
 */
class DeprecatedPropelTypesTest extends TestCase
{
    public function testBuDateThrowsPointingAtTimestamp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PropelType "BU_DATE" was removed in Propel 4.0; use TIMESTAMP instead.');

        PropelTypes::getPhpNative(PropelTypes::BU_DATE);
    }

    public function testBuTimestampThrowsPointingAtTimestamp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PropelType "BU_TIMESTAMP" was removed in Propel 4.0; use TIMESTAMP instead.');

        PropelTypes::getPhpNative(PropelTypes::BU_TIMESTAMP);
    }

    public function testBooleanEmuThrowsPointingAtBoolean(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PropelType "BOOLEAN_EMU" was removed in Propel 4.0; use BOOLEAN instead.');

        PropelTypes::getPhpNative(PropelTypes::BOOLEAN_EMU);
    }

    public function testObjectThrowsPointingAtJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PropelType "OBJECT" was removed in Propel 4.0; use JSON or app-layer storage instead.');

        PropelTypes::getPhpNative(PropelTypes::OBJECT);
    }

    public function testPhpArrayThrowsPointingAtJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PropelType "ARRAY" was removed in Propel 4.0; use JSON instead.');

        PropelTypes::getPhpNative(PropelTypes::PHP_ARRAY);
    }

    public function testGetPDOTypeAlsoThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PropelTypes::getPDOType(PropelTypes::BU_DATE);
    }

    public function testGetPdoTypeStringAlsoThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PropelTypes::getPdoTypeString(PropelTypes::BOOLEAN_EMU);
    }
}
