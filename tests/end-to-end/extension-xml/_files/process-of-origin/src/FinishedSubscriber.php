<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\ProcessOfOrigin;

use const PHP_EOL;
use function getmypid;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber as FinishedSubscriberInterface;

final class FinishedSubscriber implements FinishedSubscriberInterface
{
    public function notify(Finished $event): void
    {
        if ($event->telemetryInfo()->processId() === getmypid()) {
            print $event->test()->name() . ' finished in this process' . PHP_EOL;

            return;
        }

        print $event->test()->name() . ' finished in another process' . PHP_EOL;
    }
}
