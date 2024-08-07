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

#[CoversClass(AfterLastTestMethodFinished::class)]
#[Small]
final class AfterLastTestMethodFinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testClassName = 'Test';
        $calledMethods = $this->calledMethods();

        $event = new AfterLastTestMethodFinished(
            $telemetryInfo,
            $testClassName,
            ...$calledMethods,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new AfterLastTestMethodFinished(
            $this->telemetryInfo(),
            self::class,
            ...$this->calledMethods(),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
After Last Test Method Finished:
- HookClass::hookMethod
- AnotherHookClass::anotherHookMethod
EOT
            ,
            $event->asString(),
        );
    }

    /**
     * @return list<Code\ClassMethod>
     */
    private function calledMethods(): array
    {
        return [
            new Code\ClassMethod('HookClass', 'hookMethod'),
            new Code\ClassMethod('AnotherHookClass', 'anotherHookMethod'),
        ];
    }
}
