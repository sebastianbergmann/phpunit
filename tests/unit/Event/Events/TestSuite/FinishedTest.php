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
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Finished::class)]
final class FinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();

        $info = new TestSuiteWithName(
            'foo',
            9001,
            [],
            [],
            [],
            'bar',
            TestCollection::fromArray([]),
            [],
        );

        $result = new Result(
            5,
            new FailureCollection,
            new FailureCollection,
            new FailureCollection,
            new FailureCollection,
            new FailureCollection,
            new FailureCollection,
            [],
            []
        );

        $event = new Finished(
            $telemetryInfo,
            $info,
            $result,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($info, $event->testSuite());
    }
}
