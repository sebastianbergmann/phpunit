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
use function mkdir;
use function octdec;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertDirectoryIsNotWritable')]
#[TestDox('assertDirectoryIsNotWritable()')]
#[Small]
final class assertDirectoryIsNotWritableTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid(__CLASS__ . '_', true);
    }

    protected function tearDown(): void
    {
        @rmdir($this->directory);
    }

    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        mkdir($this->directory, octdec('0'));

        $this->assertDirectoryIsNotWritable($this->directory);
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsNotWritable(__DIR__);
    }
}
