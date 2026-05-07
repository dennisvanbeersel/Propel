<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Generator\Manager;

use PDOException;
use PHPUnit\Framework\TestCase;
use Propel\Generator\Manager\MigrationManager;
use ReflectionMethod;

/**
 * Regression: MigrationManager::getAllDatabaseVersions previously caught
 * EVERY PDOException and silently created the migration table. A typo'd
 * migration table name, network failure, schema drift, or permission
 * issue all produced "everything is fine, just empty migrations" — masking
 * the real problem.
 *
 * Fix: only auto-create on a "table not found" error (SQLSTATE 42S02 / 42P01,
 * or message contains driver-specific 'no such table' / 'does not exist').
 * Other errors propagate as RuntimeException with context.
 *
 * This test exercises the isTableNotFoundError heuristic directly via
 * reflection — the full getAllDatabaseVersions path requires a live
 * connection, which is excluded from the agnostic suite.
 */
class MigrationManagerTableNotFoundTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function provideErrorCases(): array
    {
        return [
            'mysql sqlstate'        => ['42S02', 'Base table or view not found', true],
            'postgresql sqlstate'   => ['42P01', 'relation "propel_migration" does not exist', true],
            'sqlite-style message'  => ['HY000', 'no such table: propel_migration', true],
            'mysql-style message'   => ['42000', "Table 'foo' doesn't exist",        true],
            'permission denied'     => ['42501', 'permission denied for table propel_migration', false],
            'network error'         => ['HY000', 'MySQL server has gone away',        false],
            'schema column drift'   => ['42S22', 'Unknown column "version" in field list', false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideErrorCases')]
    public function testIsTableNotFoundError(string $sqlState, string $message, bool $expected): void
    {
        $manager = new MigrationManager();
        $method = new ReflectionMethod($manager, 'isTableNotFoundError');

        // PDOException::$code defaults to int 0; SQLSTATE is the string code
        // we want to inject. Cleanest path: subclass and set ->code manually.
        $exception = new class ($message, $sqlState) extends PDOException {
            public function __construct(string $message, string $sqlState)
            {
                parent::__construct($message);
                $this->code = $sqlState;
            }
        };

        $this->assertSame($expected, $method->invoke($manager, $exception));
    }
}
