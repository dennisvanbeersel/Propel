<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Manager;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Propel\Generator\Manager\MigrationCheckSummer;
use RuntimeException;

#[Group('phase-h')]
#[Group('migration')]
class MigrationCheckSummerTest extends TestCase
{
    /**
     * @var string|null
     */
    private ?string $tempFile = null;

    protected function tearDown(): void
    {
        if ($this->tempFile !== null && is_file($this->tempFile)) {
            unlink($this->tempFile);
        }
        $this->tempFile = null;
    }

    /**
     * @return string
     */
    private function createTempMigration(string $body): string
    {
        $path = (string)tempnam(sys_get_temp_dir(), 'propel-migration-');
        // tempnam creates a file without `.php` — append the suffix so
        // php_strip_whitespace() recognizes it as PHP.
        $phpPath = $path . '.php';
        rename($path, $phpPath);
        file_put_contents($phpPath, $body);
        $this->tempFile = $phpPath;

        return $phpPath;
    }

    public function testComputeReturnsSha256HexLength(): void
    {
        $path = $this->createTempMigration("<?php\n// hello\nclass A {}\n");
        $hex = (new MigrationCheckSummer())->compute($path);
        $this->assertSame(64, strlen($hex));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $hex);
    }

    public function testComputeIsDeterministic(): void
    {
        $path = $this->createTempMigration("<?php\nclass A {}\n");
        $summer = new MigrationCheckSummer();
        $this->assertSame($summer->compute($path), $summer->compute($path));
    }

    public function testCommentOnlyEditDoesNotChangeChecksum(): void
    {
        $summer = new MigrationCheckSummer();
        // php_strip_whitespace() removes comments + collapses leading
        // whitespace; the executable code structure is preserved verbatim.
        // Equivalent code with different surrounding comments must match.
        $path = $this->createTempMigration("<?php\n// initial comment\nclass A {}\n");
        $hexA = $summer->compute($path);
        file_put_contents($path, "<?php\n// brand new comment\n/* multi-\n   line */\nclass A {}\n");
        $hexB = $summer->compute($path);
        $this->assertSame($hexA, $hexB, 'php_strip_whitespace() should erase comment-only edits.');
    }

    public function testRealCodeEditChangesChecksum(): void
    {
        $summer = new MigrationCheckSummer();
        $path = $this->createTempMigration("<?php\nclass A {}\n");
        $hexA = $summer->compute($path);
        file_put_contents($path, "<?php\nclass A { public int \$x = 0; }\n");
        $hexB = $summer->compute($path);
        $this->assertNotSame($hexA, $hexB, 'Real code edits MUST change the digest.');
    }

    public function testVerifyReturnsTrueOnEmptyExpected(): void
    {
        $path = $this->createTempMigration("<?php\nclass A {}\n");
        $this->assertTrue((new MigrationCheckSummer())->verify('', $path));
    }

    public function testVerifyReturnsTrueOnWrongLengthExpected(): void
    {
        $path = $this->createTempMigration("<?php\nclass A {}\n");
        $this->assertTrue((new MigrationCheckSummer())->verify('abc', $path));
    }

    public function testVerifyReturnsTrueOnMatchingHex(): void
    {
        $path = $this->createTempMigration("<?php\nclass A {}\n");
        $summer = new MigrationCheckSummer();
        $hex = $summer->compute($path);
        $this->assertTrue($summer->verify($hex, $path));
    }

    public function testVerifyReturnsFalseOnMismatch(): void
    {
        $path = $this->createTempMigration("<?php\nclass A {}\n");
        $bogus = str_repeat('0', 64);
        $this->assertFalse((new MigrationCheckSummer())->verify($bogus, $path));
    }

    public function testComputeThrowsOnMissingFile(): void
    {
        $this->expectException(RuntimeException::class);
        (new MigrationCheckSummer())->compute('/no/such/file.php');
    }
}
