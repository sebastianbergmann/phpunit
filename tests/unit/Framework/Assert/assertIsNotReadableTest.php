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
use function mkdir;
use function octdec;
use function rmdir;
use function unlink;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertIsNotReadable')]
#[TestDox('assertIsNotReadable()')]
#[Small]
final class assertIsNotReadableTest extends TestCase
{
    private ?string $directory = null;
    private ?string $file      = null;

    protected function tearDown(): void
    {
        if ($this->directory !== null) {
            rmdir($this->directory);
        }

        if ($this->file !== null) {
            unlink($this->file);
        }
    }

    #[DataProviderExternal(assertIsReadableTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $type, string $path): void
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

        $this->assertIsNotReadable($path);
    }

    #[DataProviderExternal(assertIsReadableTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $type, string $path): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsNotReadable($path);
    }
}
