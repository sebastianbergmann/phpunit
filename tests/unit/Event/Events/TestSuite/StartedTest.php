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
use PHPUnit\Event\Code\TestCollection;

/**
 * @covers \PHPUnit\Event\TestSuite\Started
 */
final class StartedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();

        $info = new TestSuite(
            9001,
            'foo',
            [],
            [],
            [],
            'bar',
            TestCollection::fromArray([]),
            []
        );

        $event = new Started(
            $telemetryInfo,
            $info
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($info, $event->testSuiteInfo());
    }
}
