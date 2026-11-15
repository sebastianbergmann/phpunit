<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\OutcomeOfTestRun;

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber as FinishedSubscriberInterface;

final class FinishedSubscriber implements FinishedSubscriberInterface
{
    public function notify(Finished $event): void
    {
        $outcome = $event->outcome();

        print sprintf(
            'status: %s' . PHP_EOL .
            'shell exit code: %d' . PHP_EOL .
            'tests run: %d' . PHP_EOL .
            'failed tests: %d' . PHP_EOL .
            'warnings triggered by tests: %d' . PHP_EOL .
            'warnings triggered by the test runner: %d' . PHP_EOL,
            $outcome->status()->value,
            $outcome->shellExitCode(),
            $outcome->numberOfTestsRun(),
            $outcome->numberOfFailedTests(),
            $outcome->issuesTriggeredByTests()->warnings(),
            $outcome->issuesTriggeredByTestRunner()->warnings(),
        );
    }
}
