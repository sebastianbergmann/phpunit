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
use PHPUnit\Event\Code;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(BeforeTestMethodCalled::class)]
#[Small]
final class BeforeTestMethodCalledTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testClassName = 'Test';
        $calledMethod  = $this->calledMethod();

        $event = new BeforeTestMethodCalled(
            $telemetryInfo,
            $testClassName,
            $calledMethod,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new BeforeTestMethodCalled(
            $this->telemetryInfo(),
            'Test',
            $this->calledMethod(),
        );

        $this->assertSame('Before Test Method Called (HookClass::hookMethod)', $event->asString());
    }

    private function calledMethod(): Code\ClassMethod
    {
        return new Code\ClassMethod('HookClass', 'hookMethod');
    }
}
