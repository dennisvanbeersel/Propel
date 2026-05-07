<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Util;

use DateTimeZone;
use ErrorException;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Util\PropelDateTime;

/**
 * Regression: PropelDateTime::__wakeup() called `new DateTimeZone($this->tzString)`
 * unconditionally, throwing from __wakeup() on a corrupt or moved
 * timezone string. unserialize() callers expecting a "valid PropelDateTime
 * or null" semantics get an unhandled exception mid-stream. Now: fall back
 * to UTC with a user-warning rather than throwing.
 */
class PropelDateTimeWakeupTest extends TestCase
{
    public function testWakeupFallsBackToUtcOnInvalidTimezone(): void
    {
        $obj = new PropelDateTime('2026-01-01 12:00:00', new DateTimeZone('UTC'));
        $serialized = serialize($obj);

        // Corrupt the stored timezone string in the __serialize() payload.
        $serialized = str_replace(
            's:8:"tzString";s:3:"UTC"',
            's:8:"tzString";s:18:"BogusContinent/Foo"',
            $serialized,
        );

        // Capture the warning instead of letting failOnWarning kill the test.
        $previous = error_reporting(E_ALL);
        $warnings = [];
        set_error_handler(static function (int $err, string $msg) use (&$warnings): bool {
            if ($err === E_USER_WARNING) {
                $warnings[] = $msg;

                return true;
            }

            return false;
        });
        try {
            $restored = unserialize($serialized);
        } finally {
            restore_error_handler();
            error_reporting($previous);
        }

        $this->assertInstanceOf(PropelDateTime::class, $restored);
        $this->assertSame('UTC', $restored->getTimeZone()->getName());
        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('BogusContinent/Foo', $warnings[0]);
    }

    public function testWakeupPreservesValidTimezone(): void
    {
        $obj = new PropelDateTime('2026-01-01 12:00:00', new DateTimeZone('Europe/Berlin'));
        $serialized = serialize($obj);

        // Install a throwing handler so any unexpected E_USER_WARNING surfaces
        // as an exception (the buggy code path), then restore properly.
        set_error_handler(static fn (int $err, string $msg): never => throw new ErrorException($msg, 0, $err));
        try {
            $restored = unserialize($serialized);
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(PropelDateTime::class, $restored);
        $this->assertSame('Europe/Berlin', $restored->getTimeZone()->getName());
    }
}
