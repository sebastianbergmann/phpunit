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

use const PHP_OS_FAMILY;
use function chmod;
use function octdec;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertFileIsWritable')]
#[TestDox('assertFileIsWritable()')]
#[Small]
final class assertFileIsWritableTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), __CLASS__);
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertFileIsWritable(__FILE__);
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        chmod($this->file, octdec('0'));

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsWritable($this->file);
    }
}
