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

use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestCase;

final class PhpunitNoticeTest extends TestCase
{
    public function testOne(): void
    {
        Facade::emitter()->testTriggeredPhpunitNotice(
            $this->valueObjectForEvents(),
            'message',
        );

        Facade::emitter()->testRunnerTriggeredPhpunitNotice('message');

        $this->assertTrue(true);
    }
}
