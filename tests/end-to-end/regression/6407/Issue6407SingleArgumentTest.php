<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6407;

use PHPUnit\Framework\TestCase;

require __DIR__ . '/src/single/Dispatcher.php';

require __DIR__ . '/src/single/Event.php';

require __DIR__ . '/src/single/AnEvent.php';

require __DIR__ . '/src/single/AnotherEvent.php';

require __DIR__ . '/src/single/Service.php';

final class Issue6407SingleArgumentTest extends TestCase
{
    public function testWithOrderedParametersList(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);

        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutiveParameterSets(
                $this->isInstanceOf(AnEvent::class),
                $this->isInstanceOf(AnotherEvent::class),
            );

        $service = new Service($dispatcher);

        $service->doSomething();
    }

    public function testWithUnOrderedParametersList(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);

        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withParameterSetsInAnyOrder(
                $this->isInstanceOf(AnotherEvent::class),
                $this->isInstanceOf(AnEvent::class),
            );

        $service = new Service($dispatcher);

        $service->doSomething();
    }
}
