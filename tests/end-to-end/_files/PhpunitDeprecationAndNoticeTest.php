<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestCase;

final class PhpunitDeprecationAndNoticeTest extends TestCase
{
    public function testTriggersPhpunitDeprecation(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitDeprecation(
            $this->valueObjectForEvents(),
            'deprecation message',
        );

        $this->assertTrue(true);
    }

    public function testTriggersPhpunitNotice(): void
    {
        EventFacade::emitter()->testTriggeredPhpunitNotice(
            $this->valueObjectForEvents(),
            'notice message',
        );

        $this->assertTrue(true);
    }

    public function testTriggersNothing(): void
    {
        $this->assertTrue(true);
    }
}
