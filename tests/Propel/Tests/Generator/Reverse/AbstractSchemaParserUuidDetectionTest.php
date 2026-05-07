<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Reverse;

use Propel\Generator\Reverse\AbstractSchemaParser;
use Propel\Tests\TestCase;

/**
 * Phase D (umbrella §6.4 carry-forward): UUID detection heuristic on reverse
 * for both PG (native uuid) and MySQL (BINARY(16) + name/comment hint).
 */
class AbstractSchemaParserUuidDetectionTest extends TestCase
{
    /**
     * @return \Propel\Generator\Reverse\AbstractSchemaParser
     */
    private function parser(): AbstractSchemaParser
    {
        return new class extends AbstractSchemaParser {
            #[\Override]
            public function parse(\Propel\Generator\Model\Database $database, array $additionalTables = []): int
            {
                return 0;
            }

            #[\Override]
            protected function getTypeMapping(): array
            {
                return [];
            }

            public function detect(array $row): bool
            {
                return $this->detectUuidColumn($row);
            }
        };
    }

    /**
     * @return void
     */
    public function testPgsqlNativeUuidDetected(): void
    {
        $this->assertTrue($this->parser()->detect(['data_type' => 'uuid']));
        $this->assertTrue($this->parser()->detect(['data_type' => 'UUID']));
    }

    /**
     * @return void
     */
    public function testMysqlBinary16WithUuidNameDetected(): void
    {
        $row = [
            'data_type' => 'binary',
            'column_type' => 'binary(16)',
            'column_name' => 'user_uuid',
        ];
        $this->assertTrue($this->parser()->detect($row));
    }

    /**
     * @return void
     */
    public function testMysqlBinary16WithUuidCommentDetected(): void
    {
        $row = [
            'data_type' => 'binary',
            'column_type' => 'binary(16)',
            'column_name' => 'identifier',
            'column_comment' => 'Stores a UUID v7',
        ];
        $this->assertTrue($this->parser()->detect($row));
    }

    /**
     * @return void
     */
    public function testMysqlBinary16WithoutHintNotDetected(): void
    {
        $row = [
            'data_type' => 'binary',
            'column_type' => 'binary(16)',
            'column_name' => 'sha_digest',
        ];
        $this->assertFalse($this->parser()->detect($row));
    }

    /**
     * @return void
     */
    public function testBinaryOtherSizeNotDetected(): void
    {
        $row = [
            'data_type' => 'binary',
            'column_type' => 'binary(32)',
            'column_name' => 'user_uuid',
        ];
        $this->assertFalse($this->parser()->detect($row));
    }

    /**
     * @return void
     */
    public function testNonBinaryColumnNotDetected(): void
    {
        $row = [
            'data_type' => 'varchar',
            'column_type' => 'varchar(36)',
            'column_name' => 'user_uuid',
        ];
        $this->assertFalse($this->parser()->detect($row));
    }
}
