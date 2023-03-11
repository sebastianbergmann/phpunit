<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Application;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Finished::class)]
#[Small]
final class FinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $shellExitCode = 0;

        $event = new Finished($telemetryInfo, $shellExitCode);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($shellExitCode, $event->shellExitCode());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Finished($this->telemetryInfo(), 0);

        $this->assertSame('PHPUnit Finished (Shell Exit Code: 0)', $event->asString());
    }
}
