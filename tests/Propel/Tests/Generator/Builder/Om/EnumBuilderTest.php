<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Builder\Om;

use Propel\Generator\Builder\Om\EnumBuilder;
use Propel\Generator\Config\QuickGeneratorConfig;
use Propel\Generator\Exception\EngineException;
use Propel\Generator\Model\Column;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Table;
use Propel\Generator\Platform\SqlitePlatform;
use Propel\Tests\TestCase;

/**
 * Unit tests for EnumBuilder.
 *
 * Verifies that the builder emits valid PHP backed-enum syntax for various
 * value sets, including edge cases (numeric values, special characters,
 * duplicate sanitised names, empty value sets).
 */
class EnumBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testGeneratesValidBackedEnumSource(): void
    {
        $script = $this->buildEnum('Book', 'status', ['draft', 'published', 'archived']);

        $this->assertStringContainsString("<?php\n\ndeclare(strict_types=1);", $script);
        $this->assertStringContainsString('namespace App\\Model;', $script);
        $this->assertStringContainsString('enum BookStatus: string', $script);
        $this->assertStringContainsString("case DRAFT = 'draft';", $script);
        $this->assertStringContainsString("case PUBLISHED = 'published';", $script);
        $this->assertStringContainsString("case ARCHIVED = 'archived';", $script);
    }

    /**
     * @return void
     */
    public function testGeneratedSourceParsesAndDefinesEnum(): void
    {
        $script = $this->buildEnum('Book', 'status', ['draft', 'published', 'archived']);
        $tmp = tempnam(sys_get_temp_dir(), 'enum-builder-');
        file_put_contents($tmp, $script);

        try {
            require $tmp;
            $this->assertTrue(enum_exists('App\\Model\\BookStatus'));
            $this->assertSame('draft', constant('App\\Model\\BookStatus::DRAFT')->value);
            $this->assertSame('published', constant('App\\Model\\BookStatus::PUBLISHED')->value);
        } finally {
            unlink($tmp);
        }
    }

    /**
     * Numeric values must yield valid PHP identifiers (PHP enum cases cannot start with a digit).
     *
     * @return void
     */
    public function testNumericValuesAreSanitisedToValidIdentifiers(): void
    {
        $cases = EnumBuilder::buildEnumCases(['1', '2', '3']);
        foreach (array_keys($cases) as $caseName) {
            $this->assertMatchesRegularExpression(
                '/^[A-Za-z_][A-Za-z0-9_]*$/',
                $caseName,
                "Case name '$caseName' is not a valid PHP identifier",
            );
        }
        $this->assertSame(['CASE_0' => '1', 'CASE_1' => '2', 'CASE_2' => '3'], $cases);
    }

    /**
     * @return void
     */
    public function testSpecialCharactersAreReplacedWithUnderscores(): void
    {
        $cases = EnumBuilder::buildEnumCases(['hello world', 'foo-bar', 'baz/qux']);
        $this->assertSame(
            [
                'HELLO_WORLD' => 'hello world',
                'FOO_BAR' => 'foo-bar',
                'BAZ_QUX' => 'baz/qux',
            ],
            $cases,
        );
    }

    /**
     * Duplicate sanitised names must be disambiguated via the deterministic CASE_<index> fallback.
     *
     * @return void
     */
    public function testDuplicateSanitisedNamesUseSafeFallback(): void
    {
        $cases = EnumBuilder::buildEnumCases(['foo bar', 'foo-bar', 'foo/bar']);
        $names = array_keys($cases);

        $this->assertCount(3, array_unique($names), 'Duplicate sanitised names must collapse to unique identifiers.');
        $this->assertContains('FOO_BAR', $names);
        $this->assertContains('CASE_1', $names);
        $this->assertContains('CASE_2', $names);
    }

    /**
     * @return void
     */
    public function testCamelCaseValuesAreSplitIntoUpperSnake(): void
    {
        $cases = EnumBuilder::buildEnumCases(['fooBar', 'BazQux']);
        $this->assertSame(['FOO_BAR' => 'fooBar', 'BAZ_QUX' => 'BazQux'], $cases);
    }

    /**
     * @return void
     */
    public function testQuotesInValuesAreEscaped(): void
    {
        $script = $this->buildEnum('Book', 'status', ["it's", 'said: "hi"']);
        $this->assertStringContainsString("'it\\'s'", $script);
        $this->assertStringContainsString('said: "hi"', $script);
    }

    /**
     * @return void
     */
    public function testEmptyValueSetThrows(): void
    {
        $this->expectException(EngineException::class);
        $this->buildEnum('Book', 'status', []);
    }

    /**
     * @param string $tablePhpName
     * @param string $columnName
     * @param array<int, string> $valueSet
     *
     * @return string Generated PHP source.
     */
    private function buildEnum(string $tablePhpName, string $columnName, array $valueSet): string
    {
        $database = new Database('App');
        $database->setNamespace('App\\Model');
        $database->setPlatform(new SqlitePlatform());

        $table = new Table('book');
        $table->setPhpName($tablePhpName);
        $database->addTable($table);

        $column = new Column($columnName);
        $column->setType(PropelTypes::ENUM);
        $column->setValueSet($valueSet);
        $table->addColumn($column);

        $builder = new EnumBuilder($table);
        $builder->setGeneratorConfig(new QuickGeneratorConfig());
        $builder->setColumn($column);

        return $builder->build();
    }
}
