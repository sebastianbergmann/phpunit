<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ProgressPrinter;

use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\PassedWithWarningSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestPassedWithWarningSubscriber extends Subscriber implements PassedWithWarningSubscriber
{
    public function notify(PassedWithWarning $event): void
    {
        $this->printer()->testPassedWithWarning();
    }
}
