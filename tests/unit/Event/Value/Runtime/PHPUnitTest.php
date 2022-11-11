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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

#[CoversClass(PHPUnit::class)]
#[Small]
final class PHPUnitTest extends TestCase
{
    public function testHasVersionId(): void
    {
        $this->assertSame(Version::id(), (new PHPUnit)->versionId());
    }

    public function testHasReleaseSeries(): void
    {
        $this->assertSame(Version::series(), (new PHPUnit)->releaseSeries());
    }
}
