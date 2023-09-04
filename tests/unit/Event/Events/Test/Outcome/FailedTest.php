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
use PHPUnit\Event\Code\ComparisonFailure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Failed::class)]
#[Small]
final class FailedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo     = $this->telemetryInfo();
        $test              = $this->testValueObject();
        $throwable         = $this->throwable();
        $comparisonFailure = $this->comparisonFailure();

        $event = new Failed(
            $telemetryInfo,
            $test,
            $throwable,
            $comparisonFailure,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
        $this->assertSame($comparisonFailure, $event->comparisonFailure());
        $this->assertTrue($event->hasComparisonFailure());
    }

    public function testThrowsExceptionOnAccessToUnspecifiedComparisonFailure(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $throwable     = $this->throwable();

        $event = new Failed(
            $telemetryInfo,
            $test,
            $throwable,
            null,
        );

        $this->assertFalse($event->hasComparisonFailure());

        $this->expectException(NoComparisonFailureException::class);

        $event->comparisonFailure();
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Failed(
            $this->telemetryInfo(),
            $this->testValueObject(),
            $this->throwable(),
            null,
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Test Failed (FooTest::testBar)
failure
EOT,
            $event->asString(),
        );
    }

    private function throwable(): Code\Throwable
    {
        return Code\ThrowableBuilder::from(new Exception('failure'));
    }

    private function comparisonFailure(): ComparisonFailure
    {
        return new ComparisonFailure('expected', 'actual', 'diff');
    }
}
