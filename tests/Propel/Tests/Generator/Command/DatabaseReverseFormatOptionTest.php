<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Command;

use Propel\Generator\Command\DatabaseReverseCommand;
use Propel\Tests\TestCase;

/**
 * Phase D (umbrella §6.4 carry-forward): the new --reverse-format option
 * exposes the legacy SHOW CREATE TABLE path under a deprecation flag, and
 * defaults to the information-schema strategy.
 */
class DatabaseReverseFormatOptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testReverseFormatOptionIsDeclared(): void
    {
        $command = new DatabaseReverseCommand();
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasOption('reverse-format'));
        $this->assertSame(
            DatabaseReverseCommand::REVERSE_FORMAT_INFORMATION_SCHEMA,
            $definition->getOption('reverse-format')->getDefault(),
        );
    }

    /**
     * @return void
     */
    public function testReverseFormatConstantsAreStable(): void
    {
        // Tier 2 stability: the public constants are part of the consumer
        // contract for tooling that wraps the command.
        $this->assertSame('information-schema', DatabaseReverseCommand::REVERSE_FORMAT_INFORMATION_SCHEMA);
        $this->assertSame('legacy-show-create', DatabaseReverseCommand::REVERSE_FORMAT_LEGACY_SHOW_CREATE);
    }
}
