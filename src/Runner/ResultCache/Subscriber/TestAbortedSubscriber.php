<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ResultCache;

use PHPUnit\Event\Test\Aborted;
use PHPUnit\Event\Test\AbortedSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestAbortedSubscriber extends Subscriber implements AbortedSubscriber
{
    public function notify(Aborted $event): void
    {
        $this->handler()->testAborted($event);
    }
}
