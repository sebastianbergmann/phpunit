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

#[CoversClass(ConsideredRisky::class)]
#[Small]
final class ConsideredRiskyTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();

        $message = 'message';

        $event = new ConsideredRisky(
            $telemetryInfo,
            $test,
            $message,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new ConsideredRisky(
            $this->telemetryInfo(),
            $this->testValueObject(),
            'message',
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Considered Risky (FooTest::testBar)
message
EOT,
            $event->asString(),
        );
    }
}
