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

use function get_current_user;
use function gethostname;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Runtime::class)]
#[UsesClass(OperatingSystem::class)]
#[UsesClass(PHP::class)]
#[UsesClass(PHPUnit::class)]
#[Small]
final class RuntimeTest extends TestCase
{
    public function testHasOperatingSystem(): void
    {
        $operatingSystem = new OperatingSystem;

        $this->assertSame($operatingSystem->operatingSystem(), (new Runtime)->operatingSystem()->operatingSystem());
    }

    public function test_has_PHP(): void
    {
        $php = new PHP;

        $this->assertSame($php->version(), (new Runtime)->php()->version());
    }

    public function test_has_PHPUnit(): void
    {
        $phpunit = new PHPUnit;

        $this->assertSame($phpunit->versionId(), (new Runtime)->phpunit()->versionId());
    }

    public function testHasHostName(): void
    {
        $this->assertSame(gethostname(), (new Runtime)->hostName());
    }

    public function testHasUserName(): void
    {
        $this->assertSame(get_current_user(), (new Runtime)->userName());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertStringMatchesFormat(
            'PHPUnit %s using PHP %s (%s) on %s',
            (new Runtime)->asString(),
        );
    }
}
