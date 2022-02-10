<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestDouble;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\TestFixture;

#[CoversClass(MockObjectCreatedForTrait::class)]
final class MockObjectCreatedForTraitTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $traitName     = TestFixture\MockObject\ExampleTrait::class;

        $event = new MockObjectCreatedForTrait(
            $telemetryInfo,
            $traitName
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($traitName, $event->traitName());
    }
}
