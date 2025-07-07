<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnorePHPUnitWarnings;
use PHPUnit\Framework\TestCase;

final class PhpunitWarningIgnoredTest extends TestCase
{
    public static function dataProvider(): iterable
    {
        yield [true];
    }

    #[IgnorePHPUnitWarnings]
    public function testPhpunitWarning(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitWarning(
            $this->valueObjectForEvents(),
            'warning message',
        );

        $this->assertTrue(true);
    }

    #[IgnorePHPUnitWarnings('warning message')]
    public function testPhpunitWarningWithExactMessage(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitWarning(
            $this->valueObjectForEvents(),
            'warning message',
        );

        $this->assertTrue(true);
    }

    #[IgnorePHPUnitWarnings('warn(.*)mess(.*)')]
    public function testPhpunitWarningWithRegex(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitWarning(
            $this->valueObjectForEvents(),
            'warning message',
        );

        $this->assertTrue(true);
    }

    #[IgnorePHPUnitWarnings('warn(.*)mess(.*)')]
    public function testPhpunitWarningWithWrongPattern(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitWarning(
            $this->valueObjectForEvents(),
            'another message',
        );

        $this->assertTrue(true);
    }

    #[DataProvider('dataProvider')]
    #[IgnorePHPUnitWarnings]
    public function testTooManyArgumentsInDataProvider(): void
    {
        $this->assertTrue(true);
    }
}
