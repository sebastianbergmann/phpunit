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
use PHPUnit\Event\Runtime\Runtime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Started::class)]
#[Small]
final class StartedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $runtime       = new Runtime;

        $event = new Started(
            $telemetryInfo,
            $runtime,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($runtime, $event->runtime());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Started(
            $this->telemetryInfo(),
            new Runtime,
        );

        $this->assertStringMatchesFormat('PHPUnit Started (PHPUnit %s using PHP %s)', $event->asString());
    }
}
