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

#[CoversClass(AfterTestMethodErrored::class)]
#[Small]
final class AfterTestMethodErroredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $calledMethod  = $this->calledMethod();
        $throwable     = Code\ThrowableBuilder::from(new Exception('message'));

        $event = new AfterTestMethodErrored(
            $telemetryInfo,
            $test,
            $calledMethod,
            $throwable,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame('FooTest', $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new AfterTestMethodErrored(
            $this->telemetryInfo(),
            $this->testValueObject(),
            $this->calledMethod(),
            Code\ThrowableBuilder::from(new Exception('message')),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
After Test Method Errored (HookClass::hookMethod)
message
EOT,
            $event->asString(),
        );
    }

    private function calledMethod(): Code\ClassMethod
    {
        return new Code\ClassMethod('HookClass', 'hookMethod');
    }
}
