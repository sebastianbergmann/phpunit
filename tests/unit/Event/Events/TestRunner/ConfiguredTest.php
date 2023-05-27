<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(Configured::class)]
#[Small]
final class ConfiguredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $configuration = $this->configuration();

        $event = new Configured(
            $telemetryInfo,
            $configuration,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($configuration, $event->configuration());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Configured(
            $this->telemetryInfo(),
            $this->configuration(),
        );

        $this->assertSame('Test Runner Configured', $event->asString());
    }

    private function configuration(): Configuration
    {
        return (new Merger)->merge(
            (new Builder)->fromParameters([]),
            DefaultConfiguration::create(),
        );
    }
}
