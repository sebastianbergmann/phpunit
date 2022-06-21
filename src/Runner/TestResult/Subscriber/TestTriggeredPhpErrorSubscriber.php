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

use PHPUnit\Event\Test\PhpErrorTriggered;
use PHPUnit\Event\Test\PhpErrorTriggeredSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestTriggeredPhpErrorSubscriber extends Subscriber implements PhpErrorTriggeredSubscriber
{
    public function notify(PhpErrorTriggered $event): void
    {
        $this->collector()->testTriggeredPhpError($event);
    }
}
