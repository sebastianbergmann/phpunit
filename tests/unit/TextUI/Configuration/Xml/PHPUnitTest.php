<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(PHPUnit::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class PHPUnitTest extends TestCase
{
    public function testMayNotHaveCacheDirectory(): void
    {
        $phpunit = $this->phpunit();

        $this->assertFalse($phpunit->hasCacheDirectory());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cache directory is not configured');

        $phpunit->cacheDirectory();
    }

    public function testMayNotHaveBootstrapScript(): void
    {
        $phpunit = $this->phpunit();

        $this->assertFalse($phpunit->hasBootstrap());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Bootstrap script is not configured');

        $phpunit->bootstrap();
    }

    public function testMayNotHaveExtensionsDirectory(): void
    {
        $phpunit = $this->phpunit();

        $this->assertFalse($phpunit->hasExtensionsDirectory());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Extensions directory is not configured');

        $phpunit->extensionsDirectory();
    }

    public function testMayNotHaveDefaultTestSuite(): void
    {
        $phpunit = $this->phpunit();

        $this->assertFalse($phpunit->hasDefaultTestSuite());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Default test suite is not configured');

        $phpunit->defaultTestSuite();
    }

    private function phpunit(): PHPUnit
    {
        return DefaultConfiguration::create()->phpunit();
    }
}
