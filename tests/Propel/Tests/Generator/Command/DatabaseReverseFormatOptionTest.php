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
 * Phase D (umbrella §6.4 carry-forward) / Phase G.2.7 (Propel 4.0): the
 * --reverse-format option defaults to the information-schema strategy. The
 * legacy SHOW CREATE TABLE branch (REVERSE_FORMAT_LEGACY_SHOW_CREATE) was
 * removed in 4.0 along with NestedSet.
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
    public function testInformationSchemaConstantStable(): void
    {
        // Tier 2 stability: the public constant is part of the consumer
        // contract for tooling that wraps the command.
        $this->assertSame('information-schema', DatabaseReverseCommand::REVERSE_FORMAT_INFORMATION_SCHEMA);
    }
}
