<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\TestRunner\TimeLimitExceeded;
use PHPUnit\Event\TestRunner\TimeLimitExceededSubscriber;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestRunnerTimeLimitExceededSubscriber extends Subscriber implements TimeLimitExceededSubscriber
{
    public function notify(TimeLimitExceeded $event): void
    {
        $this->collector()->testRunnerTimeLimitExceeded($event);
    }
}
