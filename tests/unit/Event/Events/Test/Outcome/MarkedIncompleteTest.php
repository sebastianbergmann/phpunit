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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(MarkedIncomplete::class)]
#[Small]
final class MarkedIncompleteTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $throwable     = $this->throwable();

        $event = new MarkedIncomplete(
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
        $event = new MarkedIncomplete(
            $this->telemetryInfo(),
            $this->testValueObject(),
            $this->throwable(),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Marked Incomplete (FooTest::testBar)
incomplete
EOT,
            $event->asString(),
        );
    }

    private function throwable(): Code\Throwable
    {
        return Code\ThrowableBuilder::from(new Exception('incomplete'));
    }
}
