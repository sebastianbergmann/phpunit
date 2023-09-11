<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelativePathCalculator::class)]
#[Small]
/**
 * @see Copied from https://github.com/phpstan/phpstan-src/blob/1.10.33/src/File/ParentDirectoryRelativePathHelper.php
 */
final class RelativePathCalculatorTest extends TestCase
{
    public static function dataGetRelativePath(): array
    {
        return [
            [
                '/usr/var/www',
                '/usr/var/www/test.php',
                'test.php',
            ],
            [
                '/usr/var/www/foo/bar/baz',
                '/usr/var/www/test.php',
                '../../../test.php',
            ],
            [
                '/',
                '/usr/var/www/test.php',
                '/usr/var/www/test.php',
            ],
            [
                '/usr/var/www',
                '/usr/var/www/src/test.php',
                'src/test.php',
            ],
            [
                '/usr/var/www/',
                '/usr/var/www/src/test.php',
                'src/test.php',
            ],
            [
                '/usr/var/www',
                '/usr/var/test.php',
                '../test.php',
            ],
            [
                '/usr/var/www/',
                '/usr/var/test.php',
                '../test.php',
            ],
            [
                '/usr/var/www/',
                '/usr/var/web/test.php',
                '../web/test.php',
            ],
            [
                '/usr/var/www/',
                '/usr/var/web/foo/test.php',
                '../web/foo/test.php',
            ],
            [
                '/',
                '/test.php',
                '/test.php',
            ],
            [
                '/var/www',
                '/usr/test.php',
                '/usr/test.php',
            ],
            [
                'C:\\var',
                'C:\\var\\test.php',
                'test.php',
            ],
            [
                'C:\\var',
                'C:\\var\\src\\test.php',
                'src/test.php',
            ],
            [
                'C:\\var',
                'C:\\test.php',
                '../test.php',
            ],
            [
                'C:\\var\\',
                'C:\\usr\\test.php',
                '../usr/test.php',
            ],
            [
                'C:\\',
                'C:\\test.php',
                'test.php',
            ],
            [
                'C:\\',
                'C:\\src\\test.php',
                'src/test.php',
            ],
            [
                'C:\\var',
                'D:\\var\\src\\test.php',
                'D:\\var\\src\\test.php',
            ],
            [
                '/usr/var/www',
                'file:///usr/var/www/test.php',
                'test.php',
            ],
        ];
    }

    #[DataProvider('dataGetRelativePath')]
    public function testGetRelativePath(string $baselineDirectory, string $filename, string $expectedRelativePath): void
    {
        $calculator = new RelativePathCalculator($baselineDirectory);

        $this->assertSame(
            $expectedRelativePath,
            $calculator->calculate($filename),
        );
    }
}
