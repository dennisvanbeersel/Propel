<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Generator\Model;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Propel\Generator\Model\PropelTypes;
use Symfony\Bridge\PhpUnit\ExpectUserDeprecationMessageTrait;

/**
 * Per umbrella spec §3.7: BU_DATE, BU_TIMESTAMP, BOOLEAN_EMU, OBJECT and
 * PHP_ARRAY column types are scheduled for removal in 4.0. Resolving them
 * via PropelTypes must emit a deprecation pointing to the replacement.
 */
#[Group('legacy')]
class DeprecatedPropelTypesTest extends TestCase
{
    use ExpectUserDeprecationMessageTrait;

    public function testBuDateIsDeprecatedInFavourOfTimestamp(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: PropelType "BU_DATE" is deprecated and will be removed in 4.0; use TIMESTAMP instead.',
        );

        PropelTypes::getPhpNative(PropelTypes::BU_DATE);
    }

    public function testBuTimestampIsDeprecatedInFavourOfTimestamp(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: PropelType "BU_TIMESTAMP" is deprecated and will be removed in 4.0; use TIMESTAMP instead.',
        );

        PropelTypes::getPhpNative(PropelTypes::BU_TIMESTAMP);
    }

    public function testBooleanEmuIsDeprecatedInFavourOfBoolean(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: PropelType "BOOLEAN_EMU" is deprecated and will be removed in 4.0; use BOOLEAN instead.',
        );

        PropelTypes::getPhpNative(PropelTypes::BOOLEAN_EMU);
    }

    public function testObjectIsDeprecatedInFavourOfJson(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: PropelType "OBJECT" is deprecated and will be removed in 4.0; use JSON or app-layer storage instead.',
        );

        PropelTypes::getPhpNative(PropelTypes::OBJECT);
    }

    public function testPhpArrayIsDeprecatedInFavourOfJson(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: PropelType "ARRAY" is deprecated and will be removed in 4.0; use JSON instead.',
        );

        PropelTypes::getPhpNative(PropelTypes::PHP_ARRAY);
    }
}
