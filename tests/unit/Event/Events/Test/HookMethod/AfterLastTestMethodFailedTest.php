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

#[CoversClass(AfterLastTestMethodFailed::class)]
#[Small]
final class AfterLastTestMethodFailedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testClassName = 'Test';
        $calledMethod  = $this->calledMethod();
        $throwable     = Code\ThrowableBuilder::from(new Exception('message'));

        $event = new AfterLastTestMethodFailed(
            $telemetryInfo,
            $testClassName,
            $calledMethod,
            $throwable,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new AfterLastTestMethodFailed(
            $this->telemetryInfo(),
            'test class name',
            $this->calledMethod(),
            Code\ThrowableBuilder::from(new Exception('message')),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
After Last Test Method Failed (HookClass::hookMethod)
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
