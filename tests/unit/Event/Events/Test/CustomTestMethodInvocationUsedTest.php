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
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(CustomTestMethodInvocationUsed::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class CustomTestMethodInvocationUsedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo              = $this->telemetryInfo();
        $test                       = $this->testValueObject();
        $customTestMethodInvocation = $this->calledMethod();

        $event = new CustomTestMethodInvocationUsed(
            $telemetryInfo,
            $test,
            $customTestMethodInvocation,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame($customTestMethodInvocation, $event->customTestMethodInvocation());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new CustomTestMethodInvocationUsed(
            $this->telemetryInfo(),
            $this->testValueObject(),
            $this->calledMethod(),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            'Custom Test Method Invocation Used (ExampleTest::invokeTestMethod)',
            $event->asString(),
        );
    }

    private function calledMethod(): ClassMethod
    {
        return new ClassMethod('ExampleTest', 'invokeTestMethod');
    }
}
