<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\Logging;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\XmlConfiguration\Exception;

#[CoversClass(Logging::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class LoggingTest extends TestCase
{
    public function testMayNotHaveJunitLogger(): void
    {
        $logging = $this->logging();

        $this->assertFalse($logging->hasJunit());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Logger "JUnit XML" is not configured');

        $logging->junit();
    }

    public function testMayNotHaveOtrLogger(): void
    {
        $logging = $this->logging();

        $this->assertFalse($logging->hasOtr());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Logger "Open Test Reporting XML" is not configured');

        $logging->otr();
    }

    public function testMayNotHaveTeamCityLogger(): void
    {
        $logging = $this->logging();

        $this->assertFalse($logging->hasTeamCity());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Logger "Team City" is not configured');

        $logging->teamCity();
    }

    public function testMayNotHaveTestDoxHtmlLogger(): void
    {
        $logging = $this->logging();

        $this->assertFalse($logging->hasTestDoxHtml());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Logger "TestDox HTML" is not configured');

        $logging->testDoxHtml();
    }

    public function testMayNotHaveTestDoxTextLogger(): void
    {
        $logging = $this->logging();

        $this->assertFalse($logging->hasTestDoxText());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Logger "TestDox Text" is not configured');

        $logging->testDoxText();
    }

    private function logging(): Logging
    {
        return new Logging(null, null, null, null, null);
    }
}
