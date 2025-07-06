<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DoNotFailOn;

use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestCase;

final class IssueTest extends TestCase
{
    public function testThatTriggersPhpunitDeprecation(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitDeprecation(
            $this->valueObjectForEvents(),
            'message',
        );

        $this->assertTrue(true);
    }

    public function testThatTriggersPhpunitWarning(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitWarning(
            $this->valueObjectForEvents(),
            'message',
        );

        $this->assertTrue(true);
    }

    public function testThatTriggersDeprecation(): void
    {
        trigger_error('message', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testThatIsSkipped(): void
    {
        $this->markTestSkipped('message');
    }

    public function testThatIsIncomplete(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testThatTriggersNotice(): void
    {
        trigger_error('message', E_USER_NOTICE);

        $this->assertTrue(true);
    }

    public function testThatIsRisky(): void
    {
    }

    public function testThatTriggersWarning(): void
    {
        trigger_error('message', E_USER_WARNING);

        $this->assertTrue(true);
    }
}
