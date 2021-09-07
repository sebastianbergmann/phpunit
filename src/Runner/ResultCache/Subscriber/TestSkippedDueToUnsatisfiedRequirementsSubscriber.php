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

use PHPUnit\Event\Test\SkippedDueToUnsatisfiedRequirements;
use PHPUnit\Event\Test\SkippedDueToUnsatisfiedRequirementsSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSkippedDueToUnsatisfiedRequirementsSubscriber extends Subscriber implements SkippedDueToUnsatisfiedRequirementsSubscriber
{
    public function notify(SkippedDueToUnsatisfiedRequirements $event): void
    {
        $this->handler()->testSkippedDueToUnsatisfiedRequirements($event);
    }
}
