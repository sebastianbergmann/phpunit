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

#[CoversClass(CodeCoverageDataCollectionStopped::class)]
#[Small]
final class CodeCoverageDataCollectionStoppedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();

        $event = new CodeCoverageDataCollectionStopped($telemetryInfo);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new CodeCoverageDataCollectionStopped($this->telemetryInfo());

        $this->assertSame('Test Runner Stopped Code Coverage Data Collection', $event->asString());
    }
}
