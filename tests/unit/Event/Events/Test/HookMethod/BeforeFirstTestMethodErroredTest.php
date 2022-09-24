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

use function array_values;
use function explode;
use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BeforeFirstTestMethodErrored::class)]
final class BeforeFirstTestMethodErroredTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));
        $throwable = Throwable::from(new Exception('message'));

        $event = new BeforeFirstTestMethodErrored(
            $telemetryInfo,
            $testClassName,
            $calledMethod,
            $throwable
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
        $this->assertSame('Before First Test Method Errored (PHPUnit\Event\Test\BeforeFirstTestMethodErroredTest::testConstructorSetsValues)
message', $event->asString());
    }
}
