<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\AbstractEventTestCase;

/**
 * @covers \PHPUnit\Event\TestSuite\Sorted
 */
final class SortedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo         = self::createTelemetryInfo();
        $executionOrder        = 9001;
        $executionOrderDefects = 5;
        $resolveDependencies   = true;

        $event = new Sorted(
            $telemetryInfo,
            $executionOrder,
            $executionOrderDefects,
            $resolveDependencies
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($executionOrder, $event->executionOrder());
        $this->assertSame($executionOrderDefects, $event->executionOrderDefects());
        $this->assertSame($resolveDependencies, $event->resolveDependencies());
    }
}
