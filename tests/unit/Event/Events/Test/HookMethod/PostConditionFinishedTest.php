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

#[CoversClass(PostConditionFinished::class)]
#[Small]
final class PostConditionFinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $test          = $this->testValueObject();
        $calledMethods = $this->calledMethods();

        $event = new PostConditionFinished(
            $telemetryInfo,
            $test,
            ...$calledMethods,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($test, $event->test());
        $this->assertSame('FooTest', $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new PostConditionFinished(
            $this->telemetryInfo(),
            $this->testValueObject(),
            ...$this->calledMethods(),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
Post Condition Method Finished:
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
