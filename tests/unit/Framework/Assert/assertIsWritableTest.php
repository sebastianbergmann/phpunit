<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use const DIRECTORY_SEPARATOR;
use const PHP_OS_FAMILY;
use function chmod;
use function mkdir;
use function octdec;
use function rmdir;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertIsWritable')]
#[TestDox('assertIsWritable()')]
#[Small]
final class assertIsWritableTest extends TestCase
{
    private ?string $directory = null;
    private ?string $file      = null;

    /**
     * @return non-empty-list<array{0: 'directory'|'file', 1: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            ['directory', __DIR__],
            ['file', __FILE__],
        ];
    }

    /**
     * @return non-empty-list<array{0: 'directory'|'file', 1: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            ['directory', sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid(__CLASS__ . '_', true)],
            ['file', tempnam(sys_get_temp_dir(), __CLASS__)],
        ];
    }

    protected function tearDown(): void
    {
        if ($this->directory !== null) {
            rmdir($this->directory);
        }

        if ($this->file !== null) {
            unlink($this->file);
        }
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $type, string $path): void
    {
        $this->assertIsWritable($path);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $type, string $path): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        if ($type === 'directory') {
            mkdir($path, octdec('0'));

            $this->directory = $path;
        } else {
            chmod($path, octdec('0'));

            $this->file = $path;
        }

        $this->expectException(AssertionFailedError::class);

        $this->assertIsWritable($path);
    }
}
