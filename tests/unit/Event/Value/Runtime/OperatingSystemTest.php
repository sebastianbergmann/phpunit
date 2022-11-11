<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Runtime;

use const PHP_OS;
use const PHP_OS_FAMILY;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(OperatingSystem::class)]
#[Small]
final class OperatingSystemTest extends TestCase
{
    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame(PHP_OS, (new OperatingSystem)->operatingSystem());
    }

    public function testHasOperatingSystemFamily(): void
    {
        $this->assertSame(PHP_OS_FAMILY, (new OperatingSystem)->operatingSystemFamily());
    }
}
