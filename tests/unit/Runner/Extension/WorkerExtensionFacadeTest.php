<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(WorkerExtensionFacade::class)]
#[Small]
final class WorkerExtensionFacadeTest extends TestCase
{
    public function testCollectsTheSubscribersThatAreRegisteredWithItInTheOrderOfRegistration(): void
    {
        $first  = $this->preparationStartedSubscriber();
        $second = $this->executionFinishedSubscriber();

        $facade = new WorkerExtensionFacade;

        $facade->registerSubscriber($first);
        $facade->registerSubscriber($second);

        $this->assertSame([$first, $second], $facade->subscribers());
    }

    public function testCollectsSeveralSubscribersRegisteredAtOnce(): void
    {
        $first  = $this->preparationStartedSubscriber();
        $second = $this->executionFinishedSubscriber();

        $facade = new WorkerExtensionFacade;

        $facade->registerSubscribers($first, $second);

        $this->assertSame([$first, $second], $facade->subscribers());
    }

    public function testHasNoSubscribersUntilOneIsRegistered(): void
    {
        $this->assertSame([], (new WorkerExtensionFacade)->subscribers());
    }

    private function preparationStartedSubscriber(): PreparationStartedSubscriber
    {
        return new class implements PreparationStartedSubscriber
        {
            public function notify(PreparationStarted $event): void
            {
            }
        };
    }

    private function executionFinishedSubscriber(): ExecutionFinishedSubscriber
    {
        return new class implements ExecutionFinishedSubscriber
        {
            public function notify(ExecutionFinished $event): void
            {
            }
        };
    }
}
