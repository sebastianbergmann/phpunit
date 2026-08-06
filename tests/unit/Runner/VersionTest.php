<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function explode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Version::class)]
#[Small]
final class VersionTest extends TestCase
{
    public function testMajorVersionNumberIsTheFirstPartOfTheSeries(): void
    {
        $this->assertSame(
            (int) explode('.', Version::series())[0],
            Version::majorVersionNumber(),
        );
    }

    public function testMajorVersionNumberIsPositive(): void
    {
        $this->assertGreaterThan(0, Version::majorVersionNumber());
    }
}
