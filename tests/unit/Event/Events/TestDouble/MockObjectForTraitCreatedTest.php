<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(MockObjectForTraitCreated::class)]
#[Small]
final class MockObjectForTraitCreatedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $traitName     = 'TraitName';

        $event = new MockObjectForTraitCreated(
            $telemetryInfo,
            $traitName,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($traitName, $event->traitName());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new MockObjectForTraitCreated(
            $this->telemetryInfo(),
            'TraitName',
        );

        $this->assertSame('Mock Object Created (TraitName)', $event->asString());
    }
}
