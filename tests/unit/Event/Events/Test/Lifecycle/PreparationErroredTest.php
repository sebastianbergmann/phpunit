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

use const PHP_EOL;
use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(PreparationErrored::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class PreparationErroredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $throwable     = ThrowableBuilder::from(new Exception('message'));

        $event = new PreparationErrored(
            $telemetryInfo,
            $test,
            $throwable,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new PreparationErrored(
            $this->telemetryInfo(),
            $this->testValueObject(),
            ThrowableBuilder::from(new Exception('message')),
        );

        $this->assertSame('Test Preparation Errored (FooTest::testBar)' . PHP_EOL . 'message', $event->asString());
    }
}
