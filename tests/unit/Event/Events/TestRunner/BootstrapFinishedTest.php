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

#[CoversClass(BootstrapFinished::class)]
#[Small]
final class BootstrapFinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $filename      = 'bootstrap.php';

        $event = new BootstrapFinished(
            $telemetryInfo,
            $filename,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($filename, $event->filename());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new BootstrapFinished(
            $this->telemetryInfo(),
            'bootstrap.php',
        );

        $this->assertSame('Bootstrap Finished (bootstrap.php)', $event->asString());
    }
}
