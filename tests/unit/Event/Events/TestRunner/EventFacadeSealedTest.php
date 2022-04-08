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

#[CoversClass(EventFacadeSealed::class)]
final class EventFacadeSealedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();

        $event = new EventFacadeSealed($telemetryInfo);

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
    }
}
