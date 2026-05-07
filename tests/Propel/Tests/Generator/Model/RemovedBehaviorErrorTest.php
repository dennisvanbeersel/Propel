<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Generator\Model;

use PHPUnit\Framework\TestCase;
use Propel\Generator\Exception\BuildException;
use Propel\Generator\Model\Database;

/**
 * Per umbrella spec §6.1: schemas referencing the killed Validate or
 * QueryCache behaviors must produce a clear migration-guide pointer
 * rather than a generic class-not-found error.
 */
class RemovedBehaviorErrorTest extends TestCase
{
    public function testValidateBehaviorEmitsMigrationGuidePointer(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageMatches('/Validate behavior was removed in Propel 3\.0.+MIGRATION-FROM-PRE-AI/s');

        (new Database())->addBehavior(['name' => 'validate']);
    }

    public function testQueryCacheBehaviorEmitsMigrationGuidePointer(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageMatches('/QueryCache behavior was removed in Propel 3\.0.+MIGRATION-FROM-PRE-AI/s');

        (new Database())->addBehavior(['name' => 'query_cache']);
    }
}
