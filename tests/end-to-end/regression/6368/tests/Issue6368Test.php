<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6368;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

final class Issue6368Test extends TestCase
{
    #[DoesNotPerformAssertions]
    public function testOne(): void
    {
        EventFacade::emitter()->testRunnerTriggeredPhpunitWarning('message');
        EventFacade::emitter()->testTriggeredPhpunitWarning($this->valueObjectForEvents(), 'message');
    }
}
