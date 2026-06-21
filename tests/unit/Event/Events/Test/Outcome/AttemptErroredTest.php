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

use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(AttemptErrored::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class AttemptErroredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $throwable     = $this->throwable();
        $duration      = Duration::fromSecondsAndNanoseconds(1, 0);

        $event = new AttemptErrored(
            $telemetryInfo,
            $test,
            $throwable,
            $duration,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
        $this->assertSame($duration, $event->duration());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new AttemptErrored(
            $this->telemetryInfo(),
            $this->testValueObject(),
            $this->throwable(),
            Duration::fromSecondsAndNanoseconds(1, 0),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Attempt Errored (FooTest::testBar)
error
EOT,
            $event->asString(),
        );
    }

    private function throwable(): Code\Throwable
    {
        return Code\ThrowableBuilder::from(new Exception('error'));
    }
}
