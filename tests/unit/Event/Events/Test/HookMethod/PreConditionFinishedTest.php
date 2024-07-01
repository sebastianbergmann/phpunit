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

#[CoversClass(PreConditionFinished::class)]
#[Small]
final class PreConditionFinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testClassName = 'Test';
        $calledMethods = $this->calledMethods();

        $event = new PreConditionFinished(
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
        $event = new PreConditionFinished(
            $this->telemetryInfo(),
            'Test',
            ...$this->calledMethods(),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Pre Condition Method Finished:
- HookClass::hookMethod
- AnotherHookClass::anotherHookMethod
EOT,
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
