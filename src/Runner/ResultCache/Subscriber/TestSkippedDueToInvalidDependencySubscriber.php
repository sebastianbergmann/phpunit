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

use PHPUnit\Event\Test\SkippedDueToInvalidDependency;
use PHPUnit\Event\Test\SkippedDueToInvalidDependencySubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSkippedDueToInvalidDependencySubscriber extends Subscriber implements SkippedDueToInvalidDependencySubscriber
{
    public function notify(SkippedDueToInvalidDependency $event): void
    {
        $this->handler()->testSkippedDueToInvalidDependency($event);
    }
}
