<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\ChaosTests;

use PHPUnit\Framework\TestCase;

/**
 * Confirms the ChaosTests directory is wired into the test runner. Real
 * failure-injection tests land in Phase E / J per umbrella spec §4.12.
 */
class SmokeTest extends TestCase
{
    public function testChaosDirIsOnTestSuitePath(): void
    {
        $this->assertSame(__NAMESPACE__, 'Propel\\Tests\\ChaosTests');
    }
}
