<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterDirectory::class)]
#[Small]
final class FilterDirectoryTest extends TestCase
{
    public function testHasPath(): void
    {
        $path = 'path';

        $this->assertSame($path, (new FilterDirectory($path, 'prefix', 'suffix'))->path());
    }

    public function testHasPrefix(): void
    {
        $prefix = 'prefix';

        $this->assertSame($prefix, (new FilterDirectory('path', $prefix, 'suffix'))->prefix());
    }

    public function testHasSuffix(): void
    {
        $suffix = 'suffix';

        $this->assertSame($suffix, (new FilterDirectory('path', 'prefix', $suffix))->suffix());
    }
}
