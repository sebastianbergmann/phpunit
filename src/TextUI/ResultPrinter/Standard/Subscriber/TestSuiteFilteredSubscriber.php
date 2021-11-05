<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ResultPrinter\Standard;

use PHPUnit\Event\TestSuite\Filtered;
use PHPUnit\Event\TestSuite\FilteredSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteFilteredSubscriber extends Subscriber implements FilteredSubscriber
{
    public function notify(Filtered $event): void
    {
        $this->printer()->testSuiteFiltered($event);
    }
}
