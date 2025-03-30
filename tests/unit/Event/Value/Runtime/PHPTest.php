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

use const PHP_EXTRA_VERSION;
use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_RELEASE_VERSION;
use const PHP_SAPI;
use const PHP_VERSION;
use const PHP_VERSION_ID;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(PHP::class)]
#[Small]
final class PHPTest extends TestCase
{
    public function testHasVersion(): void
    {
        $this->assertSame(PHP_VERSION, (new PHP)->version());
    }

    public function testHasVersionId(): void
    {
        $this->assertSame(PHP_VERSION_ID, (new PHP)->versionId());
    }

    public function testHasMajorVersion(): void
    {
        $this->assertSame(PHP_MAJOR_VERSION, (new PHP)->majorVersion());
    }

    public function testHasMinorVersion(): void
    {
        $this->assertSame(PHP_MINOR_VERSION, (new PHP)->minorVersion());
    }

    public function testHasReleaseVersion(): void
    {
        $this->assertSame(PHP_RELEASE_VERSION, (new PHP)->releaseVersion());
    }

    public function testHasExtraVersion(): void
    {
        $this->assertSame(PHP_EXTRA_VERSION, (new PHP)->extraVersion());
    }

    public function testHasSapi(): void
    {
        $this->assertSame(PHP_SAPI, (new PHP)->sapi());
    }

    public function testHasExtensions(): void
    {
        $this->assertNotEmpty((new PHP)->extensions());
        $this->assertIsList((new PHP)->extensions());
        $this->assertContainsOnlyString((new PHP)->extensions());
    }
}
