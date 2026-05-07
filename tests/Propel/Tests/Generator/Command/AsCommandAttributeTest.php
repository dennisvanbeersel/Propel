<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Command;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Asserts every concrete command class in src/Propel/Generator/Command/ has
 * an #[AsCommand] attribute and that the (name, aliases) tuple matches the
 * snapshot at tests/snapshots/cli-commands.txt — preventing accidental rename
 * during the Phase H attribute sweep and afterwards.
 */
#[Group('cli')]
#[Group('phase-h')]
class AsCommandAttributeTest extends TestCase
{
    /**
     * @var string
     */
    private const string SNAPSHOT_PATH = __DIR__ . '/../../../../snapshots/cli-commands.txt';

    /**
     * @var string
     */
    private const string COMMAND_DIR = __DIR__ . '/../../../../../src/Propel/Generator/Command';

    /**
     * @return array<string, array{0: string, 1: string, 2: string}> Map command-name => [class, name, aliases]
     */
    public static function snapshotProvider(): array
    {
        $cases = [];
        $lines = file(self::SNAPSHOT_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $cases;
        }
        $classByName = self::resolveCommandClassesByName();
        foreach ($lines as $line) {
            [$name, $aliases] = explode('|', $line, 2);
            if (!isset($classByName[$name])) {
                continue;
            }
            $cases[$name] = [$classByName[$name], $name, $aliases];
        }

        return $cases;
    }

    /**
     * Walk every PHP file under src/Propel/Generator/Command/ that defines a
     * concrete command class. Map class FQN to the command name declared
     * via #[AsCommand] (preferred) or via setName() in configure() (legacy).
     *
     * @return array<string, string> name => fully-qualified class name
     */
    private static function resolveCommandClassesByName(): array
    {
        $map = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::COMMAND_DIR, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string)file_get_contents($file->getPathname());
            if (!preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch)) {
                continue;
            }
            if (!preg_match('/^(?:abstract\s+|final\s+)?class\s+(\w+)/m', $contents, $classMatch)) {
                continue;
            }
            $fqn = $nsMatch[1] . '\\' . $classMatch[1];
            if (!class_exists($fqn)) {
                continue;
            }
            $reflection = new ReflectionClass($fqn);
            if ($reflection->isAbstract()) {
                continue;
            }
            // Prefer #[AsCommand] attribute name
            $attrs = $reflection->getAttributes(AsCommand::class);
            if ($attrs !== []) {
                /** @var AsCommand $attr */
                $attr = $attrs[0]->newInstance();
                $primary = explode('|', $attr->name)[0];
                $map[$primary] = $fqn;

                continue;
            }
            // Fallback: parse the legacy ->setName('foo:bar') call out of the source
            if (preg_match("/->setName\\('([^']+)'\\)/", $contents, $nameMatch)) {
                $map[$nameMatch[1]] = $fqn;
            }
        }

        return $map;
    }

    #[DataProvider('snapshotProvider')]
    public function testCommandHasAsCommandAttribute(string $class, string $expectedName, string $expectedAliases): void
    {
        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getAttributes(AsCommand::class);
        $this->assertNotEmpty(
            $attributes,
            sprintf(
                'Command class %s is missing the #[AsCommand] attribute. Add: #[AsCommand(name: %s, ...)]',
                $class,
                var_export($expectedName, true),
            ),
        );
        /** @var AsCommand $attr */
        $attr = $attributes[0]->newInstance();
        // Symfony's AsCommand encodes aliases into the name as "primary|alias1|alias2".
        $parts = explode('|', $attr->name);
        $actualName = array_shift($parts);
        $actualAliases = $parts;
        $this->assertSame(
            $expectedName,
            $actualName,
            sprintf(
                '#[AsCommand] name on %s does not match snapshot at tests/snapshots/cli-commands.txt.',
                $class,
            ),
        );
        $expectedAliasesArray = $expectedAliases === '' ? [] : explode(',', $expectedAliases);
        $this->assertSame(
            $expectedAliasesArray,
            $actualAliases,
            sprintf(
                '#[AsCommand] aliases on %s do not match snapshot.',
                $class,
            ),
        );
    }
}
