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

#[CoversClass(ChildProcessFinished::class)]
#[Small]
final class ChildProcessFinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $stdout        = 'output';
        $stderr        = 'error';

        $event = new ChildProcessFinished(
            $telemetryInfo,
            $stdout,
            $stderr,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($stdout, $event->stdout());
        $this->assertSame($stderr, $event->stderr());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new ChildProcessFinished(
            $this->telemetryInfo(),
            'output',
            'error',
        );

        $this->assertSame('Child Process Finished', $event->asString());
    }
}
