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
use PHPUnit\TextUI\Configuration;

/**
 * @covers \PHPUnit\Event\TestRunner\ConfigurationCombined
 */
final class ConfigurationCombinedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = self::createTelemetryInfo();
        $configuration = $this->createStub(Configuration::class);

        $event = new ConfigurationCombined($telemetryInfo, $configuration);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($configuration, $event->configuration());
    }
}
